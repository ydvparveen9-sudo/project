<?php
/*
FILE OVERVIEW:
- backend\booking.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Rooms helper load: availability, beds aur occupancy operations isi module se aate hain.
require_once __DIR__ . '/rooms_store.php';

// Session ensure: pending booking/payment state ko session me hold karne ke liye.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initial room data prep: dropdown options aur room-wise available bed map banana.
$rooms = load_rooms();
$roomOptions = [];
$availableRoomOptions = [];
$roomBedOptions = [];

foreach ($rooms as $room) {
    $roomName = trim((string)($room['name'] ?? ''));
    if ($roomName === '') {
        continue;
    }

    $roomOptions[] = $roomName;
    $availableBeds = available_bed_ids_for_room($room);
    $roomBedOptions[$roomName] = $availableBeds;
    if (count($availableBeds) > 0) {
        $availableRoomOptions[] = $roomName;
    }
}

$programOptions = [
    'BCA',
    'B.Pharm (Pharmacy)',
    'M.Lib.I.Sc (Library Science)',
    'BMLT (Medical Lab Technology)',
    'Design (Fashion, Interior, Graphics, Product, Film & TV)'
];

$selectedRoom = $_GET['room'] ?? '';
if (!in_array($selectedRoom, $availableRoomOptions, true)) {
    $selectedRoom = '';
}

$errors = [];
$successMessage = '';
$infoMessage = '';
$showPaymentStep = false;
$qrImagePath = '../image/Qr.jpeg';

$formData = [
    'student_name' => '',
    'student_email' => '',
    'student_phone' => '',
    'check_in_date' => '',
    'check_out_date' => '',
    'program' => '',
    'room_type' => '',
    'bed_id' => ''
];

// POST prefill: form submit hone par user-entered values preserve rakhna.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['student_name'] = trim((string)($_POST['student_name'] ?? ''));
    $formData['student_email'] = trim((string)($_POST['student_email'] ?? ''));
    $formData['student_phone'] = trim((string)($_POST['student_phone'] ?? ''));
    $formData['check_in_date'] = trim((string)($_POST['check_in_date'] ?? ''));
    $formData['check_out_date'] = trim((string)($_POST['check_out_date'] ?? ''));
    $formData['program'] = trim((string)($_POST['program'] ?? ''));
    $formData['room_type'] = trim((string)($_POST['room_type'] ?? ''));
    $formData['bed_id'] = trim((string)($_POST['bed_id'] ?? ''));
}

// Main booking controller: payment cancel/confirm ya new submit action handle karta hai.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? 'submit_booking'));

    // Cancel payment flow: pending booking session clear karke user ko form par wapas laana.
    if ($action === 'cancel_payment') {
        unset($_SESSION['pending_booking']);
        $infoMessage = 'Payment process cancelled. You can update details and submit again.';
    }

    // Confirm payment flow: pending booking ko paid mark karke bookings.json me final save.
    if ($action === 'confirm_payment') {
        $pendingBooking = $_SESSION['pending_booking'] ?? null;

        if (!is_array($pendingBooking)) {
            $errors[] = 'No pending booking found for payment. Please submit booking details again.';
        } else {
            $bookingFile = __DIR__ . '/../data/bookings.json';
            $bookings = [];

            if (file_exists($bookingFile)) {
                $existingData = file_get_contents($bookingFile);
                $decodedData = json_decode((string)$existingData, true);
                if (is_array($decodedData)) {
                    $bookings = $decodedData;
                }
            }

            $pendingBooking['payment_status'] = 'Paid';
            $pendingBooking['payment_method'] = 'UPI QR';
            $pendingBooking['paid_at'] = date('c');
            $bookings[] = $pendingBooking;

            $saved = file_put_contents(
                $bookingFile,
                json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                LOCK_EX
            );

            if ($saved === false) {
                $errors[] = 'Payment received but booking save nahi ho payi. Please contact admin.';
                $showPaymentStep = true;
                $formData = [
                    'student_name' => (string)($pendingBooking['student_name'] ?? ''),
                    'student_email' => (string)($pendingBooking['student_email'] ?? ''),
                    'student_phone' => (string)($pendingBooking['student_phone'] ?? ''),
                    'check_in_date' => (string)($pendingBooking['check_in_date'] ?? ''),
                    'check_out_date' => (string)($pendingBooking['check_out_date'] ?? ''),
                    'program' => (string)($pendingBooking['program'] ?? ''),
                    'room_type' => (string)($pendingBooking['room_type'] ?? ''),
                    'bed_id' => (string)($pendingBooking['bed_id'] ?? '')
                ];
            } else {
                // Payment ke baad selected bed occupy karke room inventory sync karna.
                $roomsLatest = load_rooms();
                $occupied = occupy_room_bed(
                    $roomsLatest,
                    (string)($pendingBooking['room_type'] ?? ''),
                    (string)($pendingBooking['bed_id'] ?? ''),
                    (string)($pendingBooking['student_email'] ?? ''),
                    (string)($pendingBooking['booking_id'] ?? '')
                );

                if (!$occupied || !save_rooms($roomsLatest)) {
                    $errors[] = 'Selected bed is not available now. Please select another bed.';
                    $showPaymentStep = false;
                    unset($_SESSION['pending_booking']);
                    $rooms = load_rooms();
                    $availableRoomOptions = [];
                    $roomBedOptions = [];
                    foreach ($rooms as $room) {
                        $roomName = trim((string)($room['name'] ?? ''));
                        if ($roomName === '') {
                            continue;
                        }

                        $availableBeds = available_bed_ids_for_room($room);
                        $roomBedOptions[$roomName] = $availableBeds;
                        if (count($availableBeds) > 0) {
                            $availableRoomOptions[] = $roomName;
                        }
                    }
                    $formData = [
                        'student_name' => (string)($pendingBooking['student_name'] ?? ''),
                        'student_email' => (string)($pendingBooking['student_email'] ?? ''),
                        'student_phone' => (string)($pendingBooking['student_phone'] ?? ''),
                        'check_in_date' => (string)($pendingBooking['check_in_date'] ?? ''),
                        'check_out_date' => (string)($pendingBooking['check_out_date'] ?? ''),
                        'program' => (string)($pendingBooking['program'] ?? ''),
                        'room_type' => (string)($pendingBooking['room_type'] ?? ''),
                        'bed_id' => ''
                    ];
                    $pendingBooking = null;
                    goto after_confirm_payment;
                }

                // Successful booking complete: confirmation message + pending state clear.
                $successMessage = 'Payment successful! Booking confirmed. Your Booking ID is ' . (string)$pendingBooking['booking_id'] . '.';
                $selectedRoom = (string)($pendingBooking['room_type'] ?? '');
                unset($_SESSION['pending_booking']);
                $formData = [
                    'student_name' => '',
                    'student_email' => '',
                    'student_phone' => '',
                    'check_in_date' => '',
                    'check_out_date' => '',
                    'program' => '',
                    'room_type' => '',
                    'bed_id' => ''
                ];
            }
        }
    }

    after_confirm_payment:

    // New booking submit flow: validation + pending booking create + payment step trigger.
    if ($action !== 'confirm_payment' && $action !== 'cancel_payment') {
    $studentName = $formData['student_name'];
    $studentEmail = $formData['student_email'];
    $studentPhone = $formData['student_phone'];
    $checkInDate = $formData['check_in_date'];
    $checkOutDate = $formData['check_out_date'];
    $program = $formData['program'];
    $roomType = $formData['room_type'];
    $bedId = $formData['bed_id'];

    if ($studentName === '') {
        $errors[] = 'Student name is required.';
    }

    if (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    if ($studentPhone === '' || !preg_match('/^[0-9]{10}$/', $studentPhone)) {
        $errors[] = 'Phone number must be 10  digits.';
    }

    if ($checkInDate === '' || $checkOutDate === '') {
        $errors[] = 'Both booking dates are required.';
    } elseif ($checkOutDate < $checkInDate) {
        $errors[] = 'Check-out date must be after check-in date.';
    }

    if (!in_array($program, $programOptions, true)) {
        $errors[] = 'Please select a valid program.';
    }

    if (!in_array($roomType, $roomOptions, true)) {
        $errors[] = 'Please select a valid room type.';
    } elseif (!in_array($roomType, $availableRoomOptions, true)) {
        $errors[] = 'Selected room is currently not available.';
    }

    $availableBedsForSelectedRoom = $roomBedOptions[$roomType] ?? [];
    if ($roomType !== '' && count($availableBedsForSelectedRoom) === 0) {
        $errors[] = 'No bed available in selected room.';
    }

    if (!in_array($bedId, $availableBedsForSelectedRoom, true)) {
        $errors[] = 'Please select a valid available bed.';
    }

    if (!$errors) {
        $bookingId = 'BK-' . date('YmdHis') . '-' . random_int(100, 999);

        $_SESSION['pending_booking'] = [
            'booking_id' => $bookingId,
            'student_name' => $studentName,
            'student_email' => $studentEmail,
            'student_phone' => $studentPhone,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'program' => $program,
            'room_type' => $roomType,
            'bed_id' => $bedId,
            'created_at' => date('c')
        ];

        $showPaymentStep = true;
        $infoMessage = 'Booking details verified. Please complete payment to confirm booking ID ' . $bookingId . '.';
    }
    }
}

// Auto-resume flow: agar pending booking session me hai to direct payment step show karna.
if (!$showPaymentStep && isset($_SESSION['pending_booking']) && is_array($_SESSION['pending_booking']) && $successMessage === '') {
    $showPaymentStep = true;
    $pendingBooking = $_SESSION['pending_booking'];
    $infoMessage = 'Pending payment found for Booking ID ' . (string)($pendingBooking['booking_id'] ?? '') . '. Please complete payment.';
    $formData = [
        'student_name' => (string)($pendingBooking['student_name'] ?? ''),
        'student_email' => (string)($pendingBooking['student_email'] ?? ''),
        'student_phone' => (string)($pendingBooking['student_phone'] ?? ''),
        'check_in_date' => (string)($pendingBooking['check_in_date'] ?? ''),
        'check_out_date' => (string)($pendingBooking['check_out_date'] ?? ''),
        'program' => (string)($pendingBooking['program'] ?? ''),
        'room_type' => (string)($pendingBooking['room_type'] ?? ''),
        'bed_id' => (string)($pendingBooking['bed_id'] ?? '')
    ];
}

// Final UI selection state: selected program/room/bed decide karke frontend ko bhejna.
$pendingBooking = $_SESSION['pending_booking'] ?? null;

$selectedProgram = $formData['program'];
$currentRoomSelection = $formData['room_type'] !== '' ? $formData['room_type'] : $selectedRoom;
$currentBedSelection = $formData['bed_id'];

if (!$showPaymentStep && $currentRoomSelection === '' && !empty($availableRoomOptions)) {
    $currentRoomSelection = (string)$availableRoomOptions[0];
}

if (!$showPaymentStep && $currentBedSelection === '' && $currentRoomSelection !== '') {
    $defaultBeds = $roomBedOptions[$currentRoomSelection] ?? [];
    if (is_array($defaultBeds) && !empty($defaultBeds)) {
        $currentBedSelection = (string)$defaultBeds[0];
    }
}



