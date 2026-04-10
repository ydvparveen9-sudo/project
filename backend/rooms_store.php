<?php
/*
FILE OVERVIEW:
- backend\rooms_store.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Rooms JSON storage path helper.
function rooms_file_path(): string
{
    return __DIR__ . '/../data/rooms.json';
}

// Rooms loader: file read + data validate + normalize karke clean structure return.
function load_rooms(): array
{
    $file = rooms_file_path();
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

    $normalized = normalize_rooms($decoded);
    if ($normalized !== $decoded) {
        save_rooms($normalized);
    }

    return $normalized;
}

// Rooms saver: updated room list ko JSON file me persist karta hai.
function save_rooms(array $rooms): bool
{
    $file = rooms_file_path();

    return file_put_contents(
        $file,
        json_encode(array_values($rooms), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

// ID generator: next unique room id (RM-xxxx) nikalta hai.
function next_room_id(array $rooms): string
{
    $max = 1000;

    foreach ($rooms as $room) {
        $id = (string)($room['id'] ?? '');
        if (preg_match('/^RM-(\d+)$/', $id, $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }

    return 'RM-' . (string)($max + 1);
}

// Finder helper: room id ke basis par array index return.
function find_room_index_by_id(array $rooms, string $id): int
{
    foreach ($rooms as $idx => $room) {
        if ((string)($room['id'] ?? '') === $id) {
            return $idx;
        }
    }

    return -1;
}

// Available room names: sirf wahi rooms jisme bed available ho.
function available_room_names(array $rooms): array
{
    $names = [];

    foreach ($rooms as $room) {
        if (!empty($room['is_available']) && room_available_bed_count($room) > 0) {
            $names[] = (string)($room['name'] ?? '');
        }
    }

    return $names;
}

// Default bed count rule based on room type.
function room_default_bed_count(array $room): int
{
    $roomType = strtolower((string)($room['room_type'] ?? ''));
    if ($roomType === 'double') {
        return 2;
    }

    if ($roomType === 'single') {
        return 1;
    }

    return 1;
}

// Bed normalizer: room ke beds ko standard structure/status me convert karta hai.
function normalize_room_beds(array $room): array
{
    $requestedBeds = (int)($room['total_beds'] ?? 0);
    if ($requestedBeds <= 0) {
        $requestedBeds = room_default_bed_count($room);
    }

    $existing = [];
    if (isset($room['beds']) && is_array($room['beds'])) {
        foreach ($room['beds'] as $bed) {
            $bedId = trim((string)($bed['bed_id'] ?? ''));
            if ($bedId === '') {
                continue;
            }

            $existing[$bedId] = [
                'bed_id' => $bedId,
                'status' => (string)($bed['status'] ?? 'available'),
                'assigned_student_email' => (string)($bed['assigned_student_email'] ?? ''),
                'booking_id' => (string)($bed['booking_id'] ?? ''),
                'last_updated' => (string)($bed['last_updated'] ?? date('c'))
            ];
        }
    }

    $beds = [];
    for ($i = 1; $i <= $requestedBeds; $i++) {
        $bedId = 'B' . $i;
        if (isset($existing[$bedId])) {
            $bed = $existing[$bedId];
            if (!in_array($bed['status'], ['available', 'occupied', 'maintenance'], true)) {
                $bed['status'] = 'available';
            }
            $beds[] = $bed;
            continue;
        }

        $beds[] = [
            'bed_id' => $bedId,
            'status' => 'available',
            'assigned_student_email' => '',
            'booking_id' => '',
            'last_updated' => date('c')
        ];
    }

    $room['total_beds'] = $requestedBeds;
    $room['beds'] = $beds;
    $room['available_beds'] = room_available_bed_count($room);
    $room['is_available'] = !empty($room['is_available']) && $room['available_beds'] > 0;

    return $room;
}

// Full rooms normalizer: each room par normalize_room_beds apply karta hai.
function normalize_rooms(array $rooms): array
{
    $normalized = [];
    foreach ($rooms as $room) {
        if (!is_array($room)) {
            continue;
        }

        $normalized[] = normalize_room_beds($room);
    }

    return $normalized;
}

// Counter helper: room me abhi kitne beds available hain.
function room_available_bed_count(array $room): int
{
    $count = 0;
    $beds = $room['beds'] ?? [];
    if (!is_array($beds)) {
        return 0;
    }

    foreach ($beds as $bed) {
        if ((string)($bed['status'] ?? 'available') === 'available') {
            $count++;
        }
    }

    return $count;
}

// Finder helper: room name ke basis par room index return.
function find_room_index_by_name(array $rooms, string $name): int
{
    foreach ($rooms as $idx => $room) {
        if ((string)($room['name'] ?? '') === $name) {
            return $idx;
        }
    }

    return -1;
}

// Bed list helper: selected room ke free bed IDs return.
function available_bed_ids_for_room(array $room): array
{
    $ids = [];
    $beds = $room['beds'] ?? [];
    if (!is_array($beds)) {
        return $ids;
    }

    foreach ($beds as $bed) {
        if ((string)($bed['status'] ?? 'available') === 'available') {
            $ids[] = (string)($bed['bed_id'] ?? '');
        }
    }

    return $ids;
}

// Occupy flow: booking confirm hone par specific bed occupied mark karta hai.
function occupy_room_bed(array &$rooms, string $roomName, string $bedId, string $studentEmail, string $bookingId): bool
{
    $roomIndex = find_room_index_by_name($rooms, $roomName);
    if ($roomIndex < 0) {
        return false;
    }

    $beds = $rooms[$roomIndex]['beds'] ?? [];
    if (!is_array($beds)) {
        return false;
    }

    foreach ($beds as $idx => $bed) {
        if ((string)($bed['bed_id'] ?? '') !== $bedId) {
            continue;
        }

        if ((string)($bed['status'] ?? 'available') !== 'available') {
            return false;
        }

        $beds[$idx]['status'] = 'occupied';
        $beds[$idx]['assigned_student_email'] = $studentEmail;
        $beds[$idx]['booking_id'] = $bookingId;
        $beds[$idx]['last_updated'] = date('c');
        $rooms[$roomIndex]['beds'] = $beds;
        $rooms[$roomIndex]['available_beds'] = room_available_bed_count($rooms[$roomIndex]);
        if ($rooms[$roomIndex]['available_beds'] <= 0) {
            $rooms[$roomIndex]['is_available'] = false;
        }
        return true;
    }

    return false;
}

// Vacate flow: booking/delete/cancel cases me bed ko wapas available karta hai.
function vacate_room_bed(array &$rooms, string $roomName, string $bedId): bool
{
    $roomIndex = find_room_index_by_name($rooms, $roomName);
    if ($roomIndex < 0) {
        return false;
    }

    $beds = $rooms[$roomIndex]['beds'] ?? [];
    if (!is_array($beds)) {
        return false;
    }

    foreach ($beds as $idx => $bed) {
        if ((string)($bed['bed_id'] ?? '') !== $bedId) {
            continue;
        }

        $beds[$idx]['status'] = 'available';
        $beds[$idx]['assigned_student_email'] = '';
        $beds[$idx]['booking_id'] = '';
        $beds[$idx]['last_updated'] = date('c');
        $rooms[$roomIndex]['beds'] = $beds;
        $rooms[$roomIndex]['available_beds'] = room_available_bed_count($rooms[$roomIndex]);
        if ($rooms[$roomIndex]['available_beds'] > 0) {
            $rooms[$roomIndex]['is_available'] = true;
        }
        return true;
    }

    return false;
}



