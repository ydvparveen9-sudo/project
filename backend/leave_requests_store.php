<?php
/*
FILE OVERVIEW:
- backend\leave_requests_store.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Leave requests JSON storage path helper.
function leave_requests_file_path(): string
{
    return __DIR__ . '/../data/leave_requests.json';
}

// Loader: leave requests file read karke safe array return karta hai.
function load_leave_requests(): array
{
    $file = leave_requests_file_path();
    if (!file_exists($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    // Backward compatibility: some older payloads stored requests inside wrapper keys.
    $isList = array_keys($decoded) === range(0, count($decoded) - 1);
    if ($isList) {
        return $decoded;
    }

    if (isset($decoded['requests']) && is_array($decoded['requests'])) {
        return $decoded['requests'];
    }

    if (isset($decoded['leave_requests']) && is_array($decoded['leave_requests'])) {
        return $decoded['leave_requests'];
    }

    return [];
}

// Saver: requests list ko JSON file me persist karta hai.
function save_leave_requests(array $requests): bool
{
    return file_put_contents(
        leave_requests_file_path(),
        json_encode(array_values($requests), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

// ID generator: next unique request id (LV-xxxx) create karta hai.
function next_leave_request_id(array $requests): string
{
    $max = 1000;

    foreach ($requests as $request) {
        $id = (string)($request['request_id'] ?? '');
        if (preg_match('/^LV-(\d+)$/', $id, $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }

    return 'LV-' . (string)($max + 1);
}

// Finder helper: request id ke basis par array index return.
function find_leave_request_index(array $requests, string $requestId): int
{
    foreach ($requests as $idx => $request) {
        if ((string)($request['request_id'] ?? '') === $requestId) {
            return $idx;
        }
    }

    return -1;
}

// Lookup helper: email ke related requests latest-first sort me return.
function find_leave_requests_by_email(array $requests, string $email): array
{
    $normalized = strtolower(trim($email));
    $result = [];

    foreach ($requests as $request) {
        if (strtolower((string)($request['student_email'] ?? '')) === $normalized) {
            $result[] = $request;
        }
    }

    usort($result, static function (array $a, array $b): int {
        $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
        $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
        return $bTime <=> $aTime;
    });

    return $result;
}



