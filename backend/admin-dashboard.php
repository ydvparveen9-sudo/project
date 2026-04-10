<?php
/*
// Core helpers load: auth, rooms, support, attendance aur leave modules.
FILE OVERVIEW:
- backend\admin-dashboard.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Access guard: sirf admin user hi dashboard open kar sake.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/rooms_store.php';
require_once __DIR__ . '/messages_store.php';
require_once __DIR__ . '/attendance_store.php';
require_once __DIR__ . '/leave_requests_store.php';
// Booking data bootstrap: JSON file se initial bookings list load hoti hai.

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: admin-login.php');
    exit;
}

$bookingsFile = __DIR__ . '/../data/bookings.json';
$bookings = [];

if (file_exists($bookingsFile)) {
    $raw = file_get_contents($bookingsFile);
    $decoded = json_decode((string)$raw, true);
    if (is_array($decoded)) {
        $bookings = $decoded;
    }
}

// Dashboard state setup: sab module data aur feedback messages initialize kiye jaate hain.
$messages = load_messages();
$messageErrors = [];
$messageSuccess = '';
$rooms = load_rooms();
$roomErrors = [];
$roomSuccess = '';
$bookingErrors = [];
$bookingSuccess = '';
$leaveRequests = load_leave_requests();
$leaveErrors = [];
$leaveSuccess = '';
$attendanceData = load_recent_attendance();
$attendanceRows = $attendanceData['rows'] ?? [];
$attendanceError = (string)($attendanceData['error'] ?? '');
$availableRooms = count(array_filter($rooms, static function (array $room): bool {
    return !empty($room['is_available']);
}));
$openTickets = count(array_filter($messages, static function (array $message): bool {
    return (string)($message['status'] ?? 'Open') !== 'Resolved';
}));
$todayAttendance = count(array_filter($attendanceRows, static function (array $attendance): bool {
    return (string)($attendance['attendance_date'] ?? '') === date('d M Y');
}));
$pendingLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
    return (string)($request['status'] ?? 'Pending') === 'Pending';
}));
$approvedLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
    return (string)($request['status'] ?? '') === 'Approved';
}));
$rejectedLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
    return (string)($request['status'] ?? '') === 'Rejected';
}));

usort($leaveRequests, static function (array $a, array $b): int {
    $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
    $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
    return $bTime <=> $aTime;
});
$isAjaxRequest = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && stripos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
);

// Helper: admin approval ke time same student ki approved leaves overlap to nahi karti.
function admin_leave_ranges_overlap(string $fromA, string $toA, string $fromB, string $toB): bool
{
    return !($toA < $fromB || $toB < $fromA);
}

// POST controller: form action ke hisaab se CRUD/update operations execute hote hain.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));
    $deleteActions = ['delete_booking', 'delete_room', 'delete_leave_request', 'delete_message'];
    $isDeleteAction = in_array($action, $deleteActions, true);

    // Booking delete flow: booking remove + linked bed vacate + file persist.
    if ($action === 'delete_booking') {
        $bookingId = trim((string)($_POST['booking_id'] ?? ''));

        if ($bookingId === '') {
            $bookingErrors[] = 'Booking ID is required.';
        }

        if (!$bookingErrors) {
            $bookingFound = false;
            $removedBooking = null;
            foreach ($bookings as $index => $booking) {
                if ((string)($booking['booking_id'] ?? '') === $bookingId) {
                    $removedBooking = $booking;
                    unset($bookings[$index]);
                    $bookingFound = true;
                    break;
                }
            }

            if (!$bookingFound) {
                $bookingErrors[] = 'Booking not found.';
            } else {
                if (is_array($removedBooking)) {
                    $roomName = (string)($removedBooking['room_type'] ?? '');
                    $bedId = (string)($removedBooking['bed_id'] ?? '');
                    if ($roomName !== '' && $bedId !== '') {
                        vacate_room_bed($rooms, $roomName, $bedId);
                        save_rooms($rooms);
                    }
                }

                $saved = file_put_contents(
                    $bookingsFile,
                    json_encode(array_values($bookings), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    LOCK_EX
                );

                if ($saved === false) {
                    $bookingErrors[] = 'Failed to delete booking.';
                } else {
                    $bookingSuccess = 'Booking deleted successfully.';
                }
            }
        }
    }

    // Leave request update flow: status/admin note update karke save.
    if ($action === 'update_leave_request') {
        $requestId = trim((string)($_POST['request_id'] ?? ''));
        $status = trim((string)($_POST['leave_status'] ?? 'Pending'));
        $adminNote = trim((string)($_POST['admin_note'] ?? ''));
        $allowLeaveOverride = (string)($_POST['allow_leave_override'] ?? '0') === '1';
        $validStatus = ['Pending', 'Approved', 'Rejected'];

        if ($requestId === '') {
            $leaveErrors[] = 'Leave request ID is required.';
        }

        if (!in_array($status, $validStatus, true)) {
            $leaveErrors[] = 'Invalid leave status.';
        }

        $leaveIndex = find_leave_request_index($leaveRequests, $requestId);
        if ($leaveIndex < 0) {
            $leaveErrors[] = 'Leave request not found.';
        }

        // Governance rule: reject karte waqt admin reason mandatory hona chahiye.
        if ($status === 'Rejected' && $adminNote === '') {
            $leaveErrors[] = 'Admin note is required when rejecting a leave request.';
        }

        // Approval rule: overlapping approved leaves prevent karo.
        if (!$leaveErrors && $status === 'Approved' && $leaveIndex >= 0) {
            $currentRequest = $leaveRequests[$leaveIndex];
            $currentEmail = strtolower((string)($currentRequest['student_email'] ?? ''));
            $currentFrom = (string)($currentRequest['leave_from'] ?? '');
            $currentTo = (string)($currentRequest['leave_to'] ?? '');
            $currentId = (string)($currentRequest['request_id'] ?? '');

            if ($currentEmail === '' || $currentFrom === '' || $currentTo === '') {
                $leaveErrors[] = 'Leave request has invalid date/email data.';
            } else {
                foreach ($leaveRequests as $otherRequest) {
                    $otherId = (string)($otherRequest['request_id'] ?? '');
                    $otherEmail = strtolower((string)($otherRequest['student_email'] ?? ''));
                    $otherStatus = (string)($otherRequest['status'] ?? 'Pending');
                    $otherFrom = (string)($otherRequest['leave_from'] ?? '');
                    $otherTo = (string)($otherRequest['leave_to'] ?? '');

                    if ($otherId === $currentId || $otherEmail !== $currentEmail || $otherStatus !== 'Approved') {
                        continue;
                    }

                    if ($otherFrom === '' || $otherTo === '') {
                        continue;
                    }

                    if (admin_leave_ranges_overlap($currentFrom, $currentTo, $otherFrom, $otherTo) && !$allowLeaveOverride) {
                        $leaveErrors[] = 'Cannot approve: overlapping approved leave already exists for this student. Use override with reason if policy allows.';
                        break;
                    }
                }
            }
        }

        // Override governance: jab admin override use kare to reason note required ho.
        if (!$leaveErrors && $status === 'Approved' && $allowLeaveOverride && $adminNote === '') {
            $leaveErrors[] = 'Admin note is required when override is used for leave approval.';
        }

        if (!$leaveErrors) {
            $leaveRequests[$leaveIndex]['status'] = $status;
            $leaveRequests[$leaveIndex]['admin_note'] = $adminNote;
            $leaveRequests[$leaveIndex]['override_used'] = ($status === 'Approved' && $allowLeaveOverride);
            $leaveRequests[$leaveIndex]['override_note'] = ($status === 'Approved' && $allowLeaveOverride) ? $adminNote : '';
            $leaveRequests[$leaveIndex]['updated_at'] = date('c');

            if (save_leave_requests($leaveRequests)) {
                $leaveSuccess = 'Leave request updated successfully.';
            } else {
                $leaveErrors[] = 'Failed to update leave request.';
            }
        }
    }

    // Leave request delete flow: selected request remove karke save.
    if ($action === 'delete_leave_request') {
        $requestId = trim((string)($_POST['request_id'] ?? ''));

        if ($requestId === '') {
            $leaveErrors[] = 'Leave request ID is required.';
        }

        $leaveIndex = find_leave_request_index($leaveRequests, $requestId);
        if ($leaveIndex < 0) {
            $leaveErrors[] = 'Leave request not found.';
        }

        if (!$leaveErrors) {
            unset($leaveRequests[$leaveIndex]);

            if (save_leave_requests($leaveRequests)) {
                $leaveSuccess = 'Leave request deleted successfully.';
            } else {
                $leaveErrors[] = 'Failed to delete leave request.';
            }
        }
    }

    // Room add flow: validation + unique name check + save.
    if ($action === 'add_room') {
        $name = trim((string)($_POST['room_name'] ?? ''));
        $roomType = trim((string)($_POST['room_type'] ?? ''));
        $price = (int)($_POST['price_per_term'] ?? 0);
        $isAvailable = (string)($_POST['is_available'] ?? '1') === '1';
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $totalBeds = (int)($_POST['total_beds'] ?? 0);

        if ($name === '') {
            $roomErrors[] = 'Room name is required.';
        }

        if (!in_array($roomType, ['Single', 'Double'], true)) {
            $roomErrors[] = 'Room type must be Single or Double.';
        }

        if ($price <= 0) {
            $roomErrors[] = 'Price must be greater than zero.';
        }

        if ($totalBeds <= 0) {
            $roomErrors[] = 'Total beds must be at least 1.';
        }

        foreach ($rooms as $room) {
            if (strtolower((string)($room['name'] ?? '')) === strtolower($name)) {
                $roomErrors[] = 'Room with this name already exists.';
                break;
            }
        }

        if (!$roomErrors) {
            $rooms[] = [
                'id' => next_room_id($rooms),
                'name' => $name,
                'room_type' => $roomType,
                'price_per_term' => $price,
                'is_available' => $isAvailable,
                'total_beds' => $totalBeds,
                'image_url' => $imageUrl !== '' ? $imageUrl : 'https://www.lpu.in/lpu-assets/images/residence/residential.jpg'
            ];

            if (save_rooms($rooms)) {
                $roomSuccess = 'New room added successfully.';
            } else {
                $roomErrors[] = 'Failed to save room data.';
            }
        }
    }

    // Room update flow: price/availability update.
    if ($action === 'update_room') {
        $roomId = trim((string)($_POST['room_id'] ?? ''));
        $price = (int)($_POST['price_per_term'] ?? 0);
        $isAvailable = (string)($_POST['is_available'] ?? '1') === '1';

        $idx = find_room_index_by_id($rooms, $roomId);
        if ($idx < 0) {
            $roomErrors[] = 'Room not found.';
        }

        if ($price <= 0) {
            $roomErrors[] = 'Price must be greater than zero.';
        }

        if (!$roomErrors) {
            $rooms[$idx]['price_per_term'] = $price;
            $rooms[$idx]['is_available'] = $isAvailable;

            if (save_rooms($rooms)) {
                $roomSuccess = 'Room availability and price updated.';
            } else {
                $roomErrors[] = 'Failed to update room data.';
            }
        }
    }

    // Room delete flow: occupied bed check ke baad hi deletion allow.
    if ($action === 'delete_room') {
        $roomId = trim((string)($_POST['room_id'] ?? ''));
        $idx = find_room_index_by_id($rooms, $roomId);

        if ($idx < 0) {
            $roomErrors[] = 'Room not found.';
        }

        if (!$roomErrors) {
            $occupiedFound = false;
            foreach ((array)($rooms[$idx]['beds'] ?? []) as $bed) {
                if ((string)($bed['status'] ?? 'available') === 'occupied') {
                    $occupiedFound = true;
                    break;
                }
            }

            if ($occupiedFound) {
                $roomErrors[] = 'Cannot delete room with occupied beds.';
            }
        }

        if (!$roomErrors) {
            unset($rooms[$idx]);

            if (save_rooms($rooms)) {
                $roomSuccess = 'Room deleted successfully.';
            } else {
                $roomErrors[] = 'Failed to delete room.';
            }
        }
    }

    // Support ticket update flow: admin reply + status update.
    if ($action === 'update_message') {
        $ticketId = trim((string)($_POST['ticket_id'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'Open'));
        $adminReply = trim((string)($_POST['admin_reply'] ?? ''));
        $validStatus = ['Open', 'In Progress', 'Resolved'];

        if ($ticketId === '') {
            $messageErrors[] = 'Ticket ID is required.';
        }

        if (!in_array($status, $validStatus, true)) {
            $messageErrors[] = 'Invalid status selected.';
        }

        if ($adminReply === '') {
            $messageErrors[] = 'Reply/solution cannot be empty.';
        }

        $messageIndex = find_message_index($messages, $ticketId);
        if ($messageIndex < 0) {
            $messageErrors[] = 'Message ticket not found.';
        }

        if (!$messageErrors) {
            $messages[$messageIndex]['admin_reply'] = $adminReply;
            $messages[$messageIndex]['status'] = $status;
            $messages[$messageIndex]['updated_at'] = date('c');

            if (save_messages($messages)) {
                $messageSuccess = 'Message reply updated successfully.';
            } else {
                $messageErrors[] = 'Failed to update message reply.';
            }
        }
    }

    // Support ticket delete flow.
    if ($action === 'delete_message') {
        $ticketId = trim((string)($_POST['ticket_id'] ?? ''));

        if ($ticketId === '') {
            $messageErrors[] = 'Ticket ID is required.';
        }

        $messageIndex = find_message_index($messages, $ticketId);
        if ($messageIndex < 0) {
            $messageErrors[] = 'Message ticket not found.';
        }

        if (!$messageErrors) {
            unset($messages[$messageIndex]);

            if (save_messages($messages)) {
                $messageSuccess = 'Message deleted successfully.';
            } else {
                $messageErrors[] = 'Failed to delete message.';
            }
        }
    }

    // Action ke baad fresh state reload: UI me latest data/stats dikhane ke liye.
    $messages = load_messages();
    $rooms = load_rooms();
    if (file_exists($bookingsFile)) {
        $raw = file_get_contents($bookingsFile);
        $decoded = json_decode((string)$raw, true);
        $bookings = is_array($decoded) ? $decoded : [];
    } else {
        $bookings = [];
    }
    $leaveRequests = load_leave_requests();
    $pendingLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
        return (string)($request['status'] ?? 'Pending') === 'Pending';
    }));
    $approvedLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
        return (string)($request['status'] ?? '') === 'Approved';
    }));
    $rejectedLeaves = count(array_filter($leaveRequests, static function (array $request): bool {
        return (string)($request['status'] ?? '') === 'Rejected';
    }));
    usort($leaveRequests, static function (array $a, array $b): int {
        $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
        $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
        return $bTime <=> $aTime;
    });

    if ($isAjaxRequest && $isDeleteAction) {
        $success = false;
        $message = 'Delete action failed.';
        $deletedId = '';

        if ($action === 'delete_booking') {
            $deletedId = trim((string)($_POST['booking_id'] ?? ''));
            $success = empty($bookingErrors) && $bookingSuccess !== '';
            $message = $success ? $bookingSuccess : ((string)($bookingErrors[0] ?? 'Failed to delete booking.'));
        } elseif ($action === 'delete_room') {
            $deletedId = trim((string)($_POST['room_id'] ?? ''));
            $success = empty($roomErrors) && $roomSuccess !== '';
            $message = $success ? $roomSuccess : ((string)($roomErrors[0] ?? 'Failed to delete room.'));
        } elseif ($action === 'delete_leave_request') {
            $deletedId = trim((string)($_POST['request_id'] ?? ''));
            $success = empty($leaveErrors) && $leaveSuccess !== '';
            $message = $success ? $leaveSuccess : ((string)($leaveErrors[0] ?? 'Failed to delete leave request.'));
        } elseif ($action === 'delete_message') {
            $deletedId = trim((string)($_POST['ticket_id'] ?? ''));
            $success = empty($messageErrors) && $messageSuccess !== '';
            $message = $success ? $messageSuccess : ((string)($messageErrors[0] ?? 'Failed to delete message.'));
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'action' => $action,
            'deleted_id' => $deletedId
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Logged-in user context + tickets ko latest first order me sort.
$current = current_user();
usort($messages, static function (array $a, array $b): int {
    $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
    $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
    return $bTime <=> $aTime;
});

?>
<!DOCTYPE html>
<!-- Dashboard UI starts: niche admin operations ka full interface render hota hai. -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Theme tokens: colors, typography aur reusable visual variables. */
        :root {
            --bg-a: #f6eee6;
            --bg-b: #e6f3ee;
            --bg-c: #f9f6ee;
            --card: #ffffff;
            --ink: #142229;
            --muted: #5f6e75;
            --brand: #0f8a74;
            --brand-dark: #0a6153;
            --brand-soft: #d6f2ea;
            --accent: #de7f43;
            --line: #dce9e6;
            --warn: #b95a1a;
        }

        * {
            font-family: 'Manrope', sans-serif;
        }

        body.admin-body {
            color: var(--ink);
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 10%, rgba(15, 138, 116, 0.16), transparent 27%),
                radial-gradient(circle at 87% 7%, rgba(222, 127, 67, 0.14), transparent 24%),
                linear-gradient(135deg, var(--bg-a) 0%, var(--bg-b) 52%, var(--bg-c) 100%);
            background-attachment: fixed;
            position: relative;
            overflow-x: hidden;
        }

        body.admin-body::before,
        body.admin-body::after {
            content: '';
            position: fixed;
            border-radius: 999px;
            z-index: -1;
            pointer-events: none;
            filter: blur(2px);
        }

        body.admin-body::before {
            width: 320px;
            height: 320px;
            right: -110px;
            top: 110px;
            background: radial-gradient(circle at center, rgba(15, 138, 116, 0.18), rgba(15, 138, 116, 0.03) 65%, transparent 72%);
        }

        body.admin-body::after {
            width: 270px;
            height: 270px;
            left: -90px;
            bottom: 80px;
            background: radial-gradient(circle at center, rgba(222, 127, 67, 0.16), rgba(222, 127, 67, 0.04) 62%, transparent 72%);
        }

        .topbar {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.84), rgba(246, 253, 251, 0.8));
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(200, 221, 215, 0.72);
        }

        .brand-title {
            letter-spacing: 0.02em;
            font-family: 'Sora', sans-serif;
            font-weight: 700;
        }

        .section-card {
            border: 1px solid rgba(220, 233, 230, 0.95);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.9));
            box-shadow: 0 14px 35px rgba(18, 48, 55, 0.08);
            overflow: hidden;
            transition: transform 220ms ease, box-shadow 220ms ease;
        }

        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(18, 48, 55, 0.12);
        }

        .small-muted {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .hero {
            background:
                radial-gradient(circle at 86% 20%, rgba(255, 255, 255, 0.2), transparent 34%),
                linear-gradient(132deg, #0f8a74 0%, #0a6153 55%, #08453b 100%);
            color: #fff;
            border: 0;
            border-radius: 24px;
            padding: 1.5rem 1.55rem;
            box-shadow: 0 16px 36px rgba(11, 103, 86, 0.3);
            animation: riseIn 520ms ease;
        }

        .hero .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-top: 0.85rem;
        }

        .hero .hero-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.36);
            padding: 0.32rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .hero-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            font-family: 'Sora', sans-serif;
            max-width: 800px;
        }

        .metric-card {
            border-radius: 18px;
            border: 1px solid #dbe8e5;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(251, 255, 253, 0.9));
            box-shadow: 0 10px 24px rgba(15, 36, 41, 0.08);
            transition: transform 220ms ease, box-shadow 220ms ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--accent));
            opacity: 0.85;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px rgba(15, 36, 41, 0.14);
        }

        .metric-value {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            color: var(--brand-dark);
            font-family: 'Sora', sans-serif;
        }

        .metric-note {
            font-size: 0.78rem;
            color: #3c555d;
            margin-top: 0.35rem;
        }

        .dashboard-shell {
            margin-top: 1rem;
        }

        .quick-panel {
            position: sticky;
            top: 86px;
        }

        .quick-nav-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
            color: #1f373e;
            border: 1px solid #d9e8e3;
            background: linear-gradient(180deg, #ffffff, #f8fcfb);
            border-radius: 12px;
            padding: 0.62rem 0.72rem;
            transition: all 180ms ease;
            margin-bottom: 0.55rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .quick-nav-link:hover {
            border-color: #b8d9d1;
            background: linear-gradient(180deg, #f4fbf8, #e9f8f3);
            color: #0b6756;
            transform: translateY(-1px);
        }

        .quick-chip {
            border-radius: 999px;
            background: #eef8f5;
            border: 1px solid #cfe9e2;
            color: #0b6756;
            padding: 0.14rem 0.5rem;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.9rem;
            padding-bottom: 0.55rem;
            border-bottom: 1px dashed #d9e9e5;
        }

        .section-anchor {
            color: #1f5f52;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .section-anchor:hover {
            text-decoration: underline;
        }

        .section-title {
            font-size: 1.08rem;
            font-weight: 800;
            margin-bottom: 0.9rem;
            font-family: 'Sora', sans-serif;
        }

        .dashboard-table thead th {
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom-width: 1px;
            border-bottom-color: #dce8e6;
            background: #f4fbf8;
        }

        .dashboard-table tbody tr {
            transition: background-color 180ms ease;
        }

        .dashboard-table tbody tr:nth-child(2n) {
            background: rgba(244, 251, 248, 0.7);
        }

        .dashboard-table tbody tr:hover {
            background-color: rgba(15, 138, 116, 0.06);
        }

        .form-control,
        .form-select,
        .btn {
            border-radius: 10px;
        }

        .form-control,
        .form-select {
            border-color: #d2e4df;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #8ac6b6;
            box-shadow: 0 0 0 0.2rem rgba(15, 138, 116, 0.14);
        }

        .btn-admin {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            border-color: var(--brand-dark);
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #117f6b, #085245);
            border-color: var(--brand-dark);
            color: #fff;
        }

        .btn-soft {
            border-color: #d8dddf;
            color: #24353a;
        }

        .btn-soft:hover {
            border-color: #b7c0c3;
            color: #1b292d;
            background: #f4f7f7;
        }

        .btn-delete-icon {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #f0c8c8;
            background: #fff5f5;
            color: #b42323;
            font-size: 0.9rem;
        }

        .btn-delete-icon:hover {
            color: #ffffff;
            border-color: #ba2727;
            background: #cf3131;
        }

        .accordion-button:not(.collapsed) {
            color: #0b6756;
            background: #ebf8f4;
        }

        .accordion-item {
            border: 1px solid #ddebe7;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 0.65rem;
        }

        .leave-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.7rem;
            margin-bottom: 1rem;
        }

        .leave-stat {
            border: 1px solid #d9eae6;
            border-radius: 14px;
            padding: 0.75rem 0.8rem;
            background: linear-gradient(180deg, #ffffff, #f8fcfb);
        }

        .leave-stat .label {
            color: var(--muted);
            font-size: 0.78rem;
        }

        .leave-stat .value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 0.15rem;
            color: var(--brand-dark);
            font-family: 'Sora', sans-serif;
        }

        .leave-status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.74rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .leave-status-pending {
            background: #fff8e6;
            border-color: #f5dc9b;
            color: #8a6202;
        }

        .leave-status-approved {
            background: #eafbf3;
            border-color: #bfe9d1;
            color: #126644;
        }

        .leave-status-rejected {
            background: #fff0f0;
            border-color: #f0c6c6;
            color: #9f2b2b;
        }

        .leave-request-id {
            display: inline-block;
            border-radius: 999px;
            background: #eaf4ff;
            color: #204c72;
            border: 1px solid #c9def2;
            padding: 0.22rem 0.56rem;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .leave-reason {
            min-width: 220px;
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-soft {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            background: #eef6ff;
            color: #1e4a73;
        }

        .student-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(15, 138, 116, 0.32);
            box-shadow: 0 4px 12px rgba(11, 103, 86, 0.18);
        }

        .avatar-fallback {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #0b6756;
            background: #e4f5f0;
            border: 1px solid rgba(15, 138, 116, 0.24);
        }

        .container.pb-5 > * {
            animation: riseIn 500ms ease both;
        }

        .container.pb-5 > *:nth-child(2) {
            animation-delay: 80ms;
        }

        .container.pb-5 > *:nth-child(3) {
            animation-delay: 150ms;
        }

        .container.pb-5 > *:nth-child(4) {
            animation-delay: 220ms;
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.2rem;
            }

            .metric-value {
                font-size: 1.65rem;
            }

            .quick-panel {
                position: static;
            }

            .leave-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin-body">
<nav class="navbar navbar-expand-lg navbar-light topbar shadow-sm mb-4">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold brand-title">Hostel Admin Studio</span>
        <div class="d-flex align-items-center gap-3">
            <span class="small-muted">Logged in as <?php echo htmlspecialchars((string)($current['email'] ?? 'admin')); ?></span>
            <a href="logout.php" class="btn btn-sm btn-soft">Logout</a>
        </div>
    </div>
</nav>

<div class="container pb-5" id="top">
    <!-- Top hero + KPI cards: quick health snapshot of hostel operations. -->
    <div class="hero mb-4">
        <p class="small mb-2">Control Center</p>
        <h1 class="hero-title">Hostel operations dashboard with live attendance, rooms and RMS status workflow</h1>
        <div class="hero-meta">
            <span class="hero-pill">Date: <?php echo date('d M Y'); ?></span>
            <span class="hero-pill">Open Tickets: <?php echo $openTickets; ?></span>
            <span class="hero-pill">Today Attendance: <?php echo $todayAttendance; ?></span>
        </div>
    </div>

    <div class="row g-4 mb-1">
        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="small-muted">Total Bookings</div>
                <h3 class="metric-value mb-0"><?php echo count($bookings); ?></h3>
                <div class="metric-note">Last 10 bookings listed below</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="small-muted">Configured Rooms</div>
                <h3 class="metric-value mb-0"><?php echo count($rooms); ?></h3>
                <div class="metric-note"><?php echo $availableRooms; ?> currently available</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric-card p-3">
                <div class="small-muted">RMS Status</div>
                <h3 class="metric-value mb-0"><?php echo count($messages); ?></h3>
                <div class="metric-note"><?php echo $openTickets; ?> pending action</div>
            </div>
        </div>
    </div>

    <div class="row g-4 dashboard-shell">
        <div class="col-xl-3">
            <div class="card section-card shadow-sm quick-panel">
                <div class="card-body">
                    <h5 class="section-title">Quick Navigation</h5>
                    <a class="quick-nav-link" href="#attendance">
                        Attendance
                        <span class="quick-chip"><?php echo count($attendanceRows); ?></span>
                    </a>
                    <a class="quick-nav-link" href="#bookings">
                        Bookings
                        <span class="quick-chip"><?php echo count($bookings); ?></span>
                    </a>
                    <a class="quick-nav-link" href="#rooms">
                        Rooms
                        <span class="quick-chip"><?php echo count($rooms); ?></span>
                    </a>
                    <a class="quick-nav-link" href="#support">
                        RMS Status
                        <span class="quick-chip"><?php echo $openTickets; ?></span>
                    </a>
                    <a class="quick-nav-link" href="#leave-requests">
                        Leave Requests
                        <span class="quick-chip"><?php echo $pendingLeaves; ?></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-9">
            <div id="attendance">
                <!-- Attendance section: recent face-marked entries include ki jaati hain. -->
                <div class="section-head">
                    <h5 class="section-title mb-0">Attendance Activity</h5>
                    <a class="section-anchor" href="#top">Back to top</a>
                </div>
                <?php include __DIR__ . '/../frontend/admin-attendance.php'; ?>
            </div>

            <div class="card section-card shadow-sm mb-4" id="bookings">
                <!-- Bookings section: last bookings list + delete action. -->
                <div class="card-body">
                    <div class="section-head">
                        <h5 class="section-title mb-0">Recent Bookings</h5>
                        <a class="section-anchor" href="#top">Back to top</a>
                    </div>
                    <?php if (!$bookings): ?>
                        <div class="alert alert-info mb-0">No bookings yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dashboard-table">
                                <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Room</th>
                                    <th>Bed</th>
                                    <th>Dates</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($bookingErrors): ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="alert alert-danger mb-0">
                                                <ul class="mb-0">
                                                    <?php foreach ($bookingErrors as $error): ?>
                                                        <li><?php echo htmlspecialchars($error); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if ($bookingSuccess !== ''): ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="alert alert-success mb-0"><?php echo htmlspecialchars($bookingSuccess); ?></div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach (array_slice($bookings, -10) as $booking): ?>
                                    <tr data-booking-row-id="<?php echo htmlspecialchars((string)($booking['booking_id'] ?? '')); ?>">
                                        <td><?php echo htmlspecialchars((string)($booking['booking_id'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($booking['student_name'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($booking['student_email'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($booking['room_type'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($booking['bed_id'] ?? '-')); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars((string)($booking['check_in_date'] ?? '')); ?>
                                            to
                                            <?php echo htmlspecialchars((string)($booking['check_out_date'] ?? '')); ?>
                                        </td>
                                        <td>
                                            <form method="post" class="m-0 d-inline">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars((string)($booking['booking_id'] ?? '')); ?>">
                                                <button type="submit" class="btn btn-delete-icon" data-ajax-delete="1" data-remove-target="tr" data-confirm="Delete this booking?" title="Delete booking" aria-label="Delete booking">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4 mb-4" id="rooms">
                <!-- Rooms section: add new room + existing rooms manage/update/delete. -->
                <div class="col-lg-6">
                    <div class="card section-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="section-head">
                                <h5 class="section-title mb-0">Add New Room</h5>
                                <a class="section-anchor" href="#top">Back to top</a>
                            </div>
                    <?php if ($roomErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($roomErrors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ($roomSuccess !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($roomSuccess); ?></div>
                    <?php endif; ?>

                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="add_room">
                        <div class="col-12">
                            <label class="form-label">Room Name</label>
                            <input type="text" class="form-control" name="room_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Type</label>
                            <select name="room_type" class="form-select" required>
                                <option value="">Select</option>
                                <option value="Single">Single</option>
                                <option value="Double">Double</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price Per Term</label>
                            <input type="number" class="form-control" name="price_per_term" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Beds</label>
                            <input type="number" class="form-control" name="total_beds" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Availability</label>
                            <select name="is_available" class="form-select">
                                <option value="1">Available</option>
                                <option value="0">Not Available</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" placeholder="https://...">
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-admin">Add Room</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

                <div class="col-lg-6">
                    <div class="card section-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="section-head">
                                <h5 class="section-title mb-0">Manage Existing Rooms</h5>
                                <a class="section-anchor" href="#top">Back to top</a>
                            </div>
                    <?php if (!$rooms): ?>
                        <div class="alert alert-info mb-0">No rooms configured yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dashboard-table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Beds</th>
                                    <th>Price</th>
                                    <th>Available</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rooms as $room): ?>
                                    <tr data-room-row-id="<?php echo htmlspecialchars((string)($room['id'] ?? '')); ?>">
                                        <form method="post">
                                            <td><?php echo htmlspecialchars((string)($room['name'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($room['room_type'] ?? '')); ?></td>
                                            <td>
                                                <?php echo room_available_bed_count($room); ?> / <?php echo (int)($room['total_beds'] ?? count((array)($room['beds'] ?? []))); ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars((string)($room['id'] ?? '')); ?>">
                                                <input type="number" class="form-control form-control-sm" name="price_per_term" min="1" value="<?php echo (int)($room['price_per_term'] ?? 0); ?>" required>
                                            </td>
                                            <td>
                                                <select name="is_available" class="form-select form-select-sm">
                                                    <option value="1" <?php echo !empty($room['is_available']) ? 'selected' : ''; ?>>Yes</option>
                                                    <option value="0" <?php echo empty($room['is_available']) ? 'selected' : ''; ?>>No</option>
                                                </select>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <button type="submit" name="action" value="update_room" class="btn btn-sm btn-admin">Update</button>
                                                <button type="submit" name="action" value="delete_room" class="btn btn-delete-icon" data-ajax-delete="1" data-remove-target="tr" data-confirm="Delete this room?" title="Delete room" aria-label="Delete room">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

            </div>

            <div class="card section-card shadow-sm mb-4" id="leave-requests">
                <!-- Leave section: request status workflow (pending/approved/rejected). -->
                <div class="card-body">
                    <div class="section-head">
                        <h5 class="section-title mb-0">Hostel Leave Requests</h5>
                        <a class="section-anchor" href="#top">Back to top</a>
                    </div>

                    <div class="leave-stats-grid">
                        <div class="leave-stat">
                            <div class="label">Pending Requests</div>
                            <div class="value"><?php echo $pendingLeaves; ?></div>
                        </div>
                        <div class="leave-stat">
                            <div class="label">Approved Requests</div>
                            <div class="value"><?php echo $approvedLeaves; ?></div>
                        </div>
                        <div class="leave-stat">
                            <div class="label">Rejected Requests</div>
                            <div class="value"><?php echo $rejectedLeaves; ?></div>
                        </div>
                    </div>

                    <?php if ($leaveErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($leaveErrors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($leaveSuccess !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($leaveSuccess); ?></div>
                    <?php endif; ?>

                    <?php if (!$leaveRequests): ?>
                        <div class="alert alert-info mb-0">No leave requests yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dashboard-table">
                                <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Dates</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Admin Note</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($leaveRequests as $request): ?>
                                    <?php
                                        $currentLeaveStatus = (string)($request['status'] ?? 'Pending');
                                        $statusClass = 'leave-status-pending';
                                        if ($currentLeaveStatus === 'Approved') {
                                            $statusClass = 'leave-status-approved';
                                        } elseif ($currentLeaveStatus === 'Rejected') {
                                            $statusClass = 'leave-status-rejected';
                                        }
                                    ?>
                                    <tr data-leave-row-id="<?php echo htmlspecialchars((string)($request['request_id'] ?? '')); ?>">
                                        <form method="post">
                                            <td>
                                                <span class="leave-request-id"><?php echo htmlspecialchars((string)($request['request_id'] ?? '')); ?></span>
                                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars((string)($request['request_id'] ?? '')); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars((string)($request['student_name'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($request['student_email'] ?? '')); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars((string)($request['leave_from'] ?? '')); ?>
                                                to
                                                <?php echo htmlspecialchars((string)($request['leave_to'] ?? '')); ?>
                                            </td>
                                            <td class="leave-reason" title="<?php echo htmlspecialchars((string)($request['reason'] ?? '')); ?>"><?php echo htmlspecialchars((string)($request['reason'] ?? '')); ?></td>
                                            <td style="min-width: 145px;">
                                                <select name="leave_status" class="form-select form-select-sm">
                                                    <option value="Pending" <?php echo ((string)($request['status'] ?? '') === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ((string)($request['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Rejected" <?php echo ((string)($request['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                                <div class="mt-1">
                                                    <span class="leave-status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($currentLeaveStatus); ?></span>
                                                </div>
                                            </td>
                                            <td style="min-width: 200px;">
                                                <input type="text" name="admin_note" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string)($request['admin_note'] ?? '')); ?>" placeholder="Optional note">
                                                <div class="form-check mt-1">
                                                    <input class="form-check-input" type="checkbox" name="allow_leave_override" value="1" id="leave-override-<?php echo htmlspecialchars((string)($request['request_id'] ?? '')); ?>">
                                                    <label class="form-check-label small" for="leave-override-<?php echo htmlspecialchars((string)($request['request_id'] ?? '')); ?>">
                                                        Override overlap policy
                                                    </label>
                                                </div>
                                                <?php if (!empty($request['override_used'])): ?>
                                                    <div class="small text-warning mt-1">Override used</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <button type="submit" name="action" value="update_leave_request" class="btn btn-sm btn-admin">Save</button>
                                                <button type="submit" name="action" value="delete_leave_request" class="btn btn-delete-icon" data-ajax-delete="1" data-remove-target="tr" data-confirm="Delete this leave request?" title="Delete leave request" aria-label="Delete leave request">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card section-card shadow-sm" id="support">
                <!-- Support section: tickets, admin replies aur status lifecycle. -->
                <div class="card-body">
                    <div class="section-head">
                        <h5 class="section-title mb-0">RMS Status</h5>
                        <a class="section-anchor" href="#top">Back to top</a>
                    </div>
            <?php if ($messageErrors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($messageErrors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($messageSuccess !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($messageSuccess); ?></div>
            <?php endif; ?>

            <?php if (!$messages): ?>
                <div class="alert alert-info mb-0">No support messages yet.</div>
            <?php else: ?>
                <div class="accordion" id="messagesAccordion">
                    <?php foreach ($messages as $index => $message): ?>
                        <?php $collapseId = 'ticket-' . $index; ?>
                        <div class="accordion-item" data-message-row-id="<?php echo htmlspecialchars((string)($message['ticket_id'] ?? '')); ?>">
                            <h2 class="accordion-header" id="heading-<?php echo $collapseId; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $collapseId; ?>">
                                    <span class="badge-soft me-2"><?php echo htmlspecialchars((string)($message['ticket_id'] ?? '')); ?></span>
                                    <?php echo htmlspecialchars((string)($message['subject'] ?? '')); ?>
                                    (<?php echo htmlspecialchars((string)($message['status'] ?? 'Open')); ?>)
                                </button>
                            </h2>
                            <div id="collapse-<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $collapseId; ?>" data-bs-parent="#messagesAccordion">
                                <div class="accordion-body">
                                    <p class="mb-1"><strong>Student:</strong> <?php echo htmlspecialchars((string)($message['student_name'] ?? '')); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars((string)($message['student_email'] ?? '')); ?></p>
                                    <p class="mb-2"><strong>Problem:</strong><br><?php echo nl2br(htmlspecialchars((string)($message['problem_message'] ?? ''))); ?></p>

                                    <form method="post" class="row g-2">
                                        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars((string)($message['ticket_id'] ?? '')); ?>">
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="Open" <?php echo ((string)($message['status'] ?? '') === 'Open') ? 'selected' : ''; ?>>Open</option>
                                                <option value="In Progress" <?php echo ((string)($message['status'] ?? '') === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Resolved" <?php echo ((string)($message['status'] ?? '') === 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Admin Reply</label>
                                            <textarea name="admin_reply" class="form-control form-control-sm" rows="3" required><?php echo htmlspecialchars((string)($message['admin_reply'] ?? '')); ?></textarea>
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button type="submit" name="action" value="update_message" class="btn btn-sm btn-admin">Save Reply</button>
                                            <button type="submit" name="action" value="delete_message" class="btn btn-delete-icon" data-ajax-delete="1" data-remove-target=".accordion-item" data-confirm="Delete this message?" title="Delete message" aria-label="Delete message">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        function ensureFeedbackBox() {
            var box = document.getElementById('admin-ajax-feedback');
            if (box) {
                return box;
            }

            box = document.createElement('div');
            box.id = 'admin-ajax-feedback';
            box.style.position = 'fixed';
            box.style.top = '16px';
            box.style.right = '16px';
            box.style.zIndex = '9999';
            box.style.minWidth = '260px';
            document.body.appendChild(box);
            return box;
        }

        function showFeedback(message, isSuccess) {
            var box = ensureFeedbackBox();
            var item = document.createElement('div');
            item.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' shadow-sm mb-2';
            item.textContent = message;
            box.appendChild(item);

            window.setTimeout(function () {
                if (item.parentNode) {
                    item.parentNode.removeChild(item);
                }
            }, 3000);
        }

        document.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-ajax-delete="1"]');
            if (!button) {
                return;
            }

            var form = button.closest('form');
            if (!form) {
                return;
            }

            event.preventDefault();

            var confirmText = button.getAttribute('data-confirm') || 'Delete this item?';
            if (!window.confirm(confirmText)) {
                return;
            }

            var formData = new FormData(form);
            if (button.name) {
                formData.set(button.name, button.value);
            }

            button.disabled = true;

            fetch(form.getAttribute('action') || window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.success) {
                        var targetSelector = button.getAttribute('data-remove-target') || 'tr';
                        var removeNode = button.closest(targetSelector);
                        if (removeNode) {
                            removeNode.remove();
                        }
                        showFeedback(data.message || 'Deleted successfully.', true);
                        return;
                    }

                    showFeedback((data && data.message) ? data.message : 'Delete failed.', false);
                })
                .catch(function () {
                    showFeedback('Network error while deleting. Please try again.', false);
                })
                .finally(function () {
                    button.disabled = false;
                });
        });
    })();
</script>
</body>
</html>



