<?php
/*
FILE OVERVIEW:
- backend\student-support.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Messages JSON file path helper: support tickets ki storage location return karta hai.
function support_messages_file_path(): string
{
    return __DIR__ . '/../data/messages.json';
}

// Load helper: messages file read karke safe array return karta hai.
function support_load_messages(): array
{
    $file = support_messages_file_path();

    if (!file_exists($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

// Save helper: messages list ko JSON file me atomic lock ke saath persist karta hai.
function support_save_messages(array $messages): bool
{
    $file = support_messages_file_path();

    return file_put_contents(
        $file,
        json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

// ID helper: existing ticket ids scan karke next sequential MSG id generate karta hai.
function support_next_message_id(array $messages): string
{
    $max = 1000;

    foreach ($messages as $message) {
        $id = (string)($message['ticket_id'] ?? '');
        if (preg_match('/^MSG-(\d+)$/', $id, $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }

    return 'MSG-' . (string)($max + 1);
}

// Lookup helper: email ke basis par messages filter karke latest-first sort return karta hai.
function support_find_messages_by_email(array $messages, string $email): array
{
    $normalizedEmail = strtolower(trim($email));
    $result = [];

    foreach ($messages as $message) {
        if (strtolower((string)($message['student_email'] ?? '')) === $normalizedEmail) {
            $result[] = $message;
        }
    }

    usort($result, static function (array $a, array $b): int {
        $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
        $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
        return $bTime <=> $aTime;
    });

    return $result;
}

// Initial runtime state: messages load + form feedback buckets initialize.
$messages = support_load_messages();
$submitErrors = [];
$submitSuccess = '';
$viewErrors = [];
$foundMessages = [];

// Main controller: naya problem submit ya old solution lookup action handle karta hai.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    // Submit problem flow: validation + new ticket create + save.
    if ($action === 'submit_problem') {
        $studentName = trim((string)($_POST['student_name'] ?? ''));
        $studentEmail = trim((string)($_POST['student_email'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $problemMessage = trim((string)($_POST['problem_message'] ?? ''));

        if ($studentName === '') {
            $submitErrors[] = 'Student name is required.';
        }

        if (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $submitErrors[] = 'Valid email is required.';
        }

        if ($subject === '') {
            $submitErrors[] = 'Subject is required.';
        }

        if ($problemMessage === '') {
            $submitErrors[] = 'Problem message is required.';
        }

        if (!$submitErrors) {
            // Ticket create karke default status Open ke saath record save karna.
            $ticketId = support_next_message_id($messages);
            $messages[] = [
                'ticket_id' => $ticketId,
                'student_name' => $studentName,
                'student_email' => $studentEmail,
                'subject' => $subject,
                'problem_message' => $problemMessage,
                'admin_reply' => '',
                'status' => 'Open',
                'created_at' => date('c'),
                'updated_at' => date('c')
            ];

            if (support_save_messages($messages)) {
                // Save success par form clear + latest messages reload.
                $submitSuccess = 'Problem submitted successfully. Your ticket ID is ' . $ticketId . '.';
                $_POST = [];
                $messages = support_load_messages();
            } else {
                $submitErrors[] = 'Unable to save your message right now.';
            }
        }
    }

    // Solution lookup flow: student email se related ticket updates fetch.
    if ($action === 'view_solution') {
        $studentEmail = trim((string)($_POST['student_email_lookup'] ?? ''));

        if ($studentEmail === '') {
            $viewErrors[] = 'Email is required to check solution.';
        } elseif (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $viewErrors[] = 'Please enter a valid email.';
        } else {
            $foundMessages = support_find_messages_by_email($messages, $studentEmail);
            if (!$foundMessages) {
                $viewErrors[] = 'No message found for this email.';
            }
        }
    }
}



