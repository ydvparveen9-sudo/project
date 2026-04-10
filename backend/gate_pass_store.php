<?php
/*
FILE OVERVIEW:
- backend\gate_pass_store.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Gate pass JSON storage path helper.
function gate_passes_file_path(): string
{
    return __DIR__ . '/../data/gate_passes.json';
}

// Loader: gate passes file read karke safe array return.
function load_gate_passes(): array
{
    $file = gate_passes_file_path();
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

// Saver: gate pass records ko JSON me persist karta hai.
function save_gate_passes(array $passes): bool
{
    return file_put_contents(
        gate_passes_file_path(),
        json_encode(array_values($passes), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

// ID generator: next unique gate pass id (GP-xxxx) nikalta hai.
function next_gate_pass_id(array $passes): string
{
    $max = 1000;

    foreach ($passes as $pass) {
        $id = (string)($pass['pass_id'] ?? '');
        if (preg_match('/^GP-(\d+)$/', $id, $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }

    return 'GP-' . (string)($max + 1);
}

// Lookup helper: email ke basis par passes filter + latest-first sort.
function find_gate_passes_by_email(array $passes, string $email): array
{
    $normalized = strtolower(trim($email));
    $result = [];

    foreach ($passes as $pass) {
        if (strtolower((string)($pass['student_email'] ?? '')) === $normalized) {
            $result[] = $pass;
        }
    }

    usort($result, static function (array $a, array $b): int {
        $aTime = strtotime((string)($a['created_at'] ?? '')) ?: 0;
        $bTime = strtotime((string)($b['created_at'] ?? '')) ?: 0;
        return $bTime <=> $aTime;
    });

    return $result;
}



