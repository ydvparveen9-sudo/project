<?php
/*
FILE OVERVIEW:
// Store helpers load: gate-pass records aur leave records ko read/write karne ke liye.
- backend\gate-pass.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
// Initial state setup: forms, errors, lookup results aur generated pass placeholders.
*/


require_once __DIR__ . '/gate_pass_store.php';
require_once __DIR__ . '/leave_requests_store.php';

$bookingsFile = __DIR__ . '/../data/bookings.json';
$gatePasses = load_gate_passes();
$leaveRequests = load_leave_requests();
$errors = [];
$successMessage = '';
$lookupErrors = [];
$foundPasses = [];
$generatedPass = null;
$generatedQrUrl = '';

$form = [
    'booking_id' => '',
    'student_email' => '',
    'destination' => '',
    'purpose' => '',
    'exit_datetime' => '',
    'return_datetime' => ''
];

// Helper: pass dates ke liye approved leave window exist karti hai ya nahi check karta hai.
function has_approved_leave_for_window(array $leaveRequests, string $studentEmail, string $exitDateTime, string $returnDateTime): bool
{
    $passExitDate = date('Y-m-d', strtotime($exitDateTime));
    $passReturnDate = date('Y-m-d', strtotime($returnDateTime));

    foreach ($leaveRequests as $leaveRequest) {
        $isSameEmail = strtolower((string)($leaveRequest['student_email'] ?? '')) === strtolower($studentEmail);
        $isApproved = (string)($leaveRequest['status'] ?? '') === 'Approved';
        $leaveFrom = (string)($leaveRequest['leave_from'] ?? '');
        $leaveTo = (string)($leaveRequest['leave_to'] ?? '');

        if (!$isSameEmail || !$isApproved || $leaveFrom === '' || $leaveTo === '') {
            continue;
        }

        if ($passExitDate >= $leaveFrom && $passReturnDate <= $leaveTo) {
            return true;
        }
    }

    return false;
}

// Main gate-pass controller: request create, approved pass generate, ya lookup actions handle karta hai.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    // New gate-pass request flow: form read + validation + booking/leave eligibility checks.
    if ($action === 'generate_gate_pass') {
        $form['booking_id'] = trim((string)($_POST['booking_id'] ?? ''));
        $form['student_email'] = trim((string)($_POST['student_email'] ?? ''));
        $form['destination'] = trim((string)($_POST['destination'] ?? ''));
        $form['purpose'] = trim((string)($_POST['purpose'] ?? ''));
        $form['exit_datetime'] = trim((string)($_POST['exit_datetime'] ?? ''));
        $form['return_datetime'] = trim((string)($_POST['return_datetime'] ?? ''));

        if ($form['booking_id'] === '') {
            $errors[] = 'Booking ID is required.';
        }

        if (!filter_var($form['student_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid student email is required.';
        }

        if ($form['destination'] === '') {
            $errors[] = 'Destination is required.';
        }

        if ($form['purpose'] === '') {
            $errors[] = 'Purpose is required.';
        }

        if ($form['exit_datetime'] === '' || $form['return_datetime'] === '') {
            $errors[] = 'Exit and return date-time are required.';
        } elseif ($form['return_datetime'] <= $form['exit_datetime']) {
            $errors[] = 'Return date-time must be after exit date-time.';
        }

        $matchedBooking = null;
        if (!$errors) {
            // Booking verify: same booking/email aur payment status Paid hona zaroori hai.
            $bookings = [];
            if (file_exists($bookingsFile)) {
                $raw = file_get_contents($bookingsFile);
                $decoded = json_decode((string)$raw, true);
                if (is_array($decoded)) {
                    $bookings = $decoded;
                }
            }

            foreach ($bookings as $booking) {
                if (
                    (string)($booking['booking_id'] ?? '') === $form['booking_id']
                    && strtolower((string)($booking['student_email'] ?? '')) === strtolower($form['student_email'])
                    && (string)($booking['payment_status'] ?? '') === 'Paid'
                ) {
                    $matchedBooking = $booking;
                    break;
                }
            }

            if (!is_array($matchedBooking)) {
                $errors[] = 'Paid booking not found for this Booking ID and email.';
            }
        }

        // Leave approval check: selected exit-return dates approved leave window me honi chahiye.
        if (!$errors && is_array($matchedBooking)) {
            if (!has_approved_leave_for_window($leaveRequests, $form['student_email'], $form['exit_datetime'], $form['return_datetime'])) {
                $errors[] = 'Gate pass can be generated only after admin approves leave for the selected dates.';
            }
        }

        // Duplicate prevention: same booking/email ke active pass request ko block karna.
        if (!$errors && is_array($matchedBooking)) {
            foreach ($gatePasses as $existingPass) {
                if (
                    (string)($existingPass['booking_id'] ?? '') === $form['booking_id']
                    && strtolower((string)($existingPass['student_email'] ?? '')) === strtolower($form['student_email'])
                    && in_array((string)($existingPass['status'] ?? ''), ['Pending', 'Approved'], true)
                ) {
                    $errors[] = 'A gate pass request already exists for this booking/email. Wait for admin action.';
                    break;
                }
            }
        }

        // Final create: pass_id generate karke pending gate-pass record save.
        if (!$errors && is_array($matchedBooking)) {
            $passId = next_gate_pass_id($gatePasses);
            $newPass = [
                'pass_id' => $passId,
                'booking_id' => $form['booking_id'],
                'student_name' => (string)($matchedBooking['student_name'] ?? ''),
                'student_email' => $form['student_email'],
                'destination' => $form['destination'],
                'purpose' => $form['purpose'],
                'exit_datetime' => $form['exit_datetime'],
                'return_datetime' => $form['return_datetime'],
                'status' => 'Pending',
                'is_generated' => false,
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];

            $gatePasses[] = $newPass;

            if (save_gate_passes($gatePasses)) {
                $successMessage = 'Gate pass request submitted. Please wait for admin approval.';

                $form = [
                    'booking_id' => '',
                    'student_email' => '',
                    'destination' => '',
                    'purpose' => '',
                    'exit_datetime' => '',
                    'return_datetime' => ''
                ];
            } else {
                $errors[] = 'Unable to generate gate pass right now. Please try again.';
            }
        }
    }

    // Approved pass generation flow: admin-approved pass ko final generated mark + QR build.
    if ($action === 'generate_approved_gate_pass') {
        $emailForGenerate = trim((string)($_POST['student_email_generate'] ?? ''));
        $bookingIdForGenerate = trim((string)($_POST['booking_id_generate'] ?? ''));

        if (!filter_var($emailForGenerate, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required to generate gate pass.';
        }

        if ($bookingIdForGenerate === '') {
            $errors[] = 'Booking ID is required to generate gate pass.';
        }

        $approvedPassIndex = -1;
        if (!$errors) {
            foreach ($gatePasses as $index => $gatePass) {
                if (
                    strtolower((string)($gatePass['student_email'] ?? '')) === strtolower($emailForGenerate)
                    && (string)($gatePass['booking_id'] ?? '') === $bookingIdForGenerate
                    && (string)($gatePass['status'] ?? '') === 'Approved'
                ) {
                    $approvedPassIndex = $index;
                    break;
                }
            }

            if ($approvedPassIndex < 0) {
                $errors[] = 'No approved gate pass found for this Booking ID and email.';
            }
        }

        // Safety check: final printable pass tabhi generate karo jab leave abhi bhi approved range me ho.
        if (!$errors && $approvedPassIndex >= 0) {
            $selectedPass = $gatePasses[$approvedPassIndex];
            $overrideUsed = !empty($selectedPass['override_used']);
            if (!$overrideUsed && !has_approved_leave_for_window(
                $leaveRequests,
                (string)($selectedPass['student_email'] ?? ''),
                (string)($selectedPass['exit_datetime'] ?? ''),
                (string)($selectedPass['return_datetime'] ?? '')
            )) {
                $errors[] = 'Gate pass generation blocked: matching approved leave request not found for these dates.';
            }
        }

        if (!$errors && $approvedPassIndex >= 0) {
            $gatePasses[$approvedPassIndex]['is_generated'] = true;
            $gatePasses[$approvedPassIndex]['generated_at'] = date('c');
            $gatePasses[$approvedPassIndex]['updated_at'] = date('c');

            if (!save_gate_passes($gatePasses)) {
                $errors[] = 'Approved gate pass found, but pass generation save failed.';
            } else {
                // QR payload: printable pass details ko QR API me encode karna.
                $generatedPass = $gatePasses[$approvedPassIndex];
                $successMessage = 'Approved gate pass generated successfully.';
                $qrData = implode(' | ', [
                    'Pass ID: ' . (string)$generatedPass['pass_id'],
                    'Student: ' . (string)$generatedPass['student_name'],
                    'Booking: ' . (string)$generatedPass['booking_id'],
                    'Exit: ' . (string)$generatedPass['exit_datetime'],
                    'Return: ' . (string)$generatedPass['return_datetime']
                ]);
                $generatedQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($qrData);
            }
        }
    }

    // Lookup flow: student email se existing pass history fetch karna.
    if ($action === 'lookup_gate_pass') {
        $emailLookup = trim((string)($_POST['student_email_lookup'] ?? ''));

        if (!filter_var($emailLookup, FILTER_VALIDATE_EMAIL)) {
            $lookupErrors[] = 'Please enter a valid email.';
        } else {
            $foundPasses = find_gate_passes_by_email($gatePasses, $emailLookup);
            if (!$foundPasses) {
                $lookupErrors[] = 'No gate pass found for this email.';
            }
        }
    }
}



