<?php
/*
FILE OVERVIEW:
// Leave request store helper load: JSON-based leave records ko handle karta hai.
- backend\leave-request.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
// Initial state: existing requests load + form/result variables initialize.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


require_once __DIR__ . '/leave_requests_store.php';
require_once __DIR__ . '/gate_pass_store.php';

$leaveRequests = load_leave_requests();
$gatePasses = load_gate_passes();
$submitErrors = [];
$submitSuccess = '';
$viewErrors = [];
$foundRequests = [];
$gatePassErrors = [];
$gatePassSuccess = '';
$generatedGatePass = null;
$generatedGateQrUrl = '';

$form = [
    'student_name' => '',
    'student_email' => '',
    'reason' => '',
    'leave_from' => '',
    'leave_to' => '',
    'emergency_contact' => '',
    'emergency_override_request' => false
];

$gateForm = [
    'request_id' => '',
    'student_email' => ''
];

// Helper: do date ranges overlap karte hain ya nahi.
function leave_ranges_overlap(string $fromA, string $toA, string $fromB, string $toB): bool
{
    return !($toA < $fromB || $toB < $fromA);
}

// Helper: request id + email ke base par specific leave request find karta hai.
function find_leave_request_by_id_and_email(array $leaveRequests, string $requestId, string $email): ?array
{
    $normalizedEmail = strtolower(trim($email));
    $normalizedRequestId = trim($requestId);

    foreach ($leaveRequests as $request) {
        if (
            (string)($request['request_id'] ?? '') === $normalizedRequestId
            && strtolower((string)($request['student_email'] ?? '')) === $normalizedEmail
        ) {
            return $request;
        }
    }

    return null;
}

// Main controller: submit leave request ya status lookup action process karta hai.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    // Submit flow: form read, validation, new request create aur save.
    if ($action === 'submit_leave_request') {
        $form['student_name'] = trim((string)($_POST['student_name'] ?? ''));
        $form['student_email'] = trim((string)($_POST['student_email'] ?? ''));
        $form['reason'] = trim((string)($_POST['reason'] ?? ''));
        $form['leave_from'] = trim((string)($_POST['leave_from'] ?? ''));
        $form['leave_to'] = trim((string)($_POST['leave_to'] ?? ''));
        $form['emergency_contact'] = trim((string)($_POST['emergency_contact'] ?? ''));
        $form['emergency_override_request'] = (string)($_POST['emergency_override_request'] ?? '0') === '1';

        if ($form['student_name'] === '') {
            $submitErrors[] = 'Student name is required.';
        }

        if (!filter_var($form['student_email'], FILTER_VALIDATE_EMAIL)) {
            $submitErrors[] = 'Valid email is required.';
        }

        if ($form['reason'] === '') {
            $submitErrors[] = 'Leave reason is required.';
        }

        if ($form['leave_from'] === '' || $form['leave_to'] === '') {
            $submitErrors[] = 'Both leave dates are required.';
        } elseif ($form['leave_to'] < $form['leave_from']) {
            $submitErrors[] = 'Leave end date must be after start date.';
        }

        // University-style policy checks: past leave block + max duration cap.
        if ($form['leave_from'] !== '' && $form['leave_to'] !== '' && $form['leave_to'] >= $form['leave_from']) {
            $today = date('Y-m-d');
            if ($form['leave_from'] < $today) {
                $submitErrors[] = 'Leave start date cannot be in the past.';
            }

            $fromTs = strtotime($form['leave_from']);
            $toTs = strtotime($form['leave_to']);
            if ($fromTs !== false && $toTs !== false) {
                $leaveDays = (int)floor(($toTs - $fromTs) / 86400) + 1;
                if ($leaveDays > 7) {
                    $submitErrors[] = 'Leave duration cannot exceed 7 days in one request.';
                }
            }
        }

        if ($form['emergency_contact'] === '' || !preg_match('/^[0-9]{10}$/', $form['emergency_contact'])) {
            $submitErrors[] = 'Emergency contact must be 10 digits.';
        }

        // Same student ke overlapping Pending/Approved leave requests allow nahi hote.
        if (!$submitErrors && filter_var($form['student_email'], FILTER_VALIDATE_EMAIL)) {
            $normalizedEmail = strtolower($form['student_email']);
            foreach ($leaveRequests as $existingRequest) {
                $existingEmail = strtolower((string)($existingRequest['student_email'] ?? ''));
                $existingStatus = (string)($existingRequest['status'] ?? 'Pending');
                $existingFrom = (string)($existingRequest['leave_from'] ?? '');
                $existingTo = (string)($existingRequest['leave_to'] ?? '');

                if ($existingEmail !== $normalizedEmail) {
                    continue;
                }

                if (!in_array($existingStatus, ['Pending', 'Approved'], true)) {
                    continue;
                }

                if ($existingFrom === '' || $existingTo === '') {
                    continue;
                }

                if (leave_ranges_overlap($form['leave_from'], $form['leave_to'], $existingFrom, $existingTo)) {
                    $submitErrors[] = 'An overlapping leave request already exists (Pending/Approved).';
                    break;
                }
            }
        }

        if (!$submitErrors) {
            // Unique request id generate karke pending request store me add karna.
            $requestId = next_leave_request_id($leaveRequests);
            $leaveRequests[] = [
                'request_id' => $requestId,
                'student_name' => $form['student_name'],
                'student_email' => $form['student_email'],
                'reason' => $form['reason'],
                'leave_from' => $form['leave_from'],
                'leave_to' => $form['leave_to'],
                'emergency_contact' => $form['emergency_contact'],
                'emergency_override_requested' => $form['emergency_override_request'],
                'override_used' => false,
                'override_note' => '',
                'status' => 'Pending',
                'admin_note' => '',
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];

            if (save_leave_requests($leaveRequests)) {
                // Save success par form reset + latest requests reload.
                $submitSuccess = 'Leave request submitted. Request ID: ' . $requestId . '.';
                $form = [
                    'student_name' => '',
                    'student_email' => '',
                    'reason' => '',
                    'leave_from' => '',
                    'leave_to' => '',
                    'emergency_contact' => '',
                    'emergency_override_request' => false
                ];
                $leaveRequests = load_leave_requests();
            } else {
                $submitErrors[] = 'Unable to submit request right now. Please try again.';
            }
        }
    }

    // Status lookup flow: email ke basis par user ke leave requests fetch karna.
    if ($action === 'view_leave_status') {
        $emailLookup = trim((string)($_POST['student_email_lookup'] ?? ''));

        if (!filter_var($emailLookup, FILTER_VALIDATE_EMAIL)) {
            $viewErrors[] = 'Please enter a valid email.';
        } else {
            $foundRequests = find_leave_requests_by_email($leaveRequests, $emailLookup);
            if (!$foundRequests) {
                $viewErrors[] = 'No leave requests found for this email.';
            }
        }
    }

    // Leave approved hone par ID+email se gate pass QR generate hota hai.
    if ($action === 'generate_gate_pass_from_leave') {
        $gateForm['request_id'] = trim((string)($_POST['request_id'] ?? ''));
        $gateForm['student_email'] = trim((string)($_POST['student_email_for_gate'] ?? ''));

        if ($gateForm['request_id'] === '') {
            $gatePassErrors[] = 'Leave Request ID is required.';
        }

        if (!filter_var($gateForm['student_email'], FILTER_VALIDATE_EMAIL)) {
            $gatePassErrors[] = 'Valid student email is required.';
        }

        $matchedLeave = null;
        if (!$gatePassErrors) {
            $matchedLeave = find_leave_request_by_id_and_email($leaveRequests, $gateForm['request_id'], $gateForm['student_email']);
            if (!is_array($matchedLeave)) {
                $gatePassErrors[] = 'Leave request not found for this ID and email.';
            }
        }

        if (!$gatePassErrors && is_array($matchedLeave)) {
            $leaveStatus = (string)($matchedLeave['status'] ?? 'Pending');
            if ($leaveStatus === 'Rejected') {
                $gatePassErrors[] = 'Gate pass cannot be generated because this leave request is rejected.';
            } elseif ($leaveStatus !== 'Approved') {
                $gatePassErrors[] = 'Gate pass can be generated only after leave request is approved.';
            }
        }

        $existingPassIndex = -1;
        if (!$gatePassErrors) {
            foreach ($gatePasses as $index => $pass) {
                if (
                    (string)($pass['leave_request_id'] ?? '') === $gateForm['request_id']
                    && strtolower((string)($pass['student_email'] ?? '')) === strtolower($gateForm['student_email'])
                ) {
                    $existingPassIndex = $index;
                    break;
                }
            }
        }

        if (!$gatePassErrors && $existingPassIndex >= 0) {
            $existingPass = $gatePasses[$existingPassIndex];
            if ((string)($existingPass['status'] ?? '') === 'Rejected') {
                $gatePassErrors[] = 'Existing gate pass for this leave request is rejected by admin.';
            } else {
                if (empty($existingPass['is_generated'])) {
                    $gatePasses[$existingPassIndex]['status'] = 'Approved';
                    $gatePasses[$existingPassIndex]['is_generated'] = true;
                    $gatePasses[$existingPassIndex]['generated_at'] = date('c');
                    $gatePasses[$existingPassIndex]['updated_at'] = date('c');

                    if (!save_gate_passes($gatePasses)) {
                        $gatePassErrors[] = 'Existing gate pass found but QR generation save failed.';
                    }
                }

                if (!$gatePassErrors) {
                    $generatedGatePass = $gatePasses[$existingPassIndex];
                    $gatePassSuccess = 'Gate pass generated from approved leave request.';
                }
            }
        }

        if (!$gatePassErrors && $existingPassIndex < 0 && is_array($matchedLeave)) {
            $passId = next_gate_pass_id($gatePasses);
            $newPass = [
                'pass_id' => $passId,
                'leave_request_id' => (string)$matchedLeave['request_id'],
                'booking_id' => '',
                'student_name' => (string)($matchedLeave['student_name'] ?? ''),
                'student_email' => (string)($matchedLeave['student_email'] ?? ''),
                'destination' => 'As per approved leave',
                'purpose' => (string)($matchedLeave['reason'] ?? ''),
                'exit_datetime' => (string)($matchedLeave['leave_from'] ?? '') . ' 09:00',
                'return_datetime' => (string)($matchedLeave['leave_to'] ?? '') . ' 18:00',
                'status' => 'Approved',
                'is_generated' => true,
                'generated_at' => date('c'),
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];

            $gatePasses[] = $newPass;

            if (save_gate_passes($gatePasses)) {
                $generatedGatePass = $newPass;
                $gatePassSuccess = 'Gate pass generated successfully for approved leave request.';
            } else {
                $gatePassErrors[] = 'Unable to generate gate pass right now. Please try again.';
            }
        }

        if (!$gatePassErrors && is_array($generatedGatePass)) {
            $qrData = implode(' | ', [
                'Pass ID: ' . (string)($generatedGatePass['pass_id'] ?? ''),
                'Leave ID: ' . (string)($generatedGatePass['leave_request_id'] ?? ''),
                'Student: ' . (string)($generatedGatePass['student_name'] ?? ''),
                'Exit: ' . (string)($generatedGatePass['exit_datetime'] ?? ''),
                'Return: ' . (string)($generatedGatePass['return_datetime'] ?? '')
            ]);
            $generatedGateQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($qrData);
        }
    }
}



