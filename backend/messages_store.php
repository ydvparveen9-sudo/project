<?php
/*
FILE OVERVIEW:
- backend\messages_store.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Messages JSON storage path helper.
function messages_file_path(): string
{
    return __DIR__ . '/../data/messages.json';
}

// Loader: messages.json read karke safe array return karta hai.
function load_messages(): array
{
    $file = messages_file_path();

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

// Saver: updated messages list ko JSON me persist karta hai.
function save_messages(array $messages): bool
{
    $file = messages_file_path();

    return file_put_contents(
        $file,
        json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

// ID generator: next unique ticket id (MSG-xxxx) nikalta hai.
function next_message_id(array $messages): string
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

// Finder helper: ticket id ke basis par message index return.
function find_message_index(array $messages, string $ticketId): int
{
    foreach ($messages as $idx => $message) {
        if ((string)($message['ticket_id'] ?? '') === $ticketId) {
            return $idx;
        }
    }

    return -1;
}

// Finder helper: ticket id + email se exact message record locate karta hai.
function find_message_by_ticket_and_email(array $messages, string $ticketId, string $email): ?array
{
    $normalizedEmail = strtolower(trim($email));

    foreach ($messages as $message) {
        if (
            (string)($message['ticket_id'] ?? '') === trim($ticketId)
            && strtolower((string)($message['student_email'] ?? '')) === $normalizedEmail
        ) {
            return $message;
        }
    }

    return null;
}

// Lookup helper: email ke sab messages latest-first sort karke return.
function find_messages_by_email(array $messages, string $email): array
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



