<?php
/*
FILE OVERVIEW:
- backend\attendance_store.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// DB connection helper: attendance MySQL database se safe connection return karta hai.
if (!function_exists('attendance_db_connection')) {
    function attendance_db_connection(): ?mysqli
    {
        $conn = @new mysqli('localhost', 'root', '', 'attendance_db');

        if ($conn->connect_errno) {
            return null;
        }

        $conn->set_charset('utf8mb4');
        return $conn;
    }
}

// Photo resolver: student id ke basis par image folder me matching photo URL dhundhta hai.
if (!function_exists('resolve_student_photo_url')) {
    function resolve_student_photo_url(string $studentId): string
    {
        $imagesDir = __DIR__ . '/../image';
        $extensions = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];

        foreach ($extensions as $ext) {
            $filename = $studentId . '.' . $ext;
            $path = $imagesDir . '/' . $filename;
            if (file_exists($path)) {
                return '../image/' . rawurlencode($filename);
            }

            $upperFilename = $studentId . '.' . strtoupper($ext);
            $upperPath = $imagesDir . '/' . $upperFilename;
            if (file_exists($upperPath)) {
                return '../image/' . rawurlencode($upperFilename);
            }
        }

        return '';
    }
}

// Attendance loader: latest records fetch karke dashboard-friendly rows format me deta hai.
if (!function_exists('load_recent_attendance')) {
    function load_recent_attendance(int $limit = 25): array
    {
        $conn = attendance_db_connection();
        if (!$conn) {
            // DB unavailable case me graceful error payload return.
            return [
                'rows' => [],
                'error' => 'Attendance database connection failed. Ensure MySQL and attendance_db are available.'
            ];
        }

        $query = 'SELECT id, name, total_attendance, last_attendance_time
                  FROM students
                  WHERE last_attendance_time IS NOT NULL
                  ORDER BY last_attendance_time DESC
                  LIMIT ?';

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $conn->close();
            return [
                'rows' => [],
                'error' => 'Could not prepare attendance query.'
            ];
        }

        $stmt->bind_param('i', $limit);
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            return [
                'rows' => [],
                'error' => 'Could not fetch attendance records.'
            ];
        }

        $result = $stmt->get_result();
        $rows = [];

        // Result transform: raw DB fields ko UI-specific date/time/photo fields me map.
        if ($result) {
            while ($item = $result->fetch_assoc()) {
                $studentId = (string)($item['id'] ?? '');
                $name = (string)($item['name'] ?? '');
                $totalAttendance = (int)($item['total_attendance'] ?? 0);
                $lastTimeRaw = (string)($item['last_attendance_time'] ?? '');

                $dateValue = '-';
                $timeValue = '-';
                $parsed = strtotime($lastTimeRaw);
                if ($parsed !== false) {
                    $dateValue = date('d M Y', $parsed);
                    $timeValue = date('h:i:s A', $parsed);
                }

                $rows[] = [
                    'student_id' => $studentId,
                    'name' => $name,
                    'total_attendance' => $totalAttendance,
                    'attendance_date' => $dateValue,
                    'attendance_time' => $timeValue,
                    'photo_url' => resolve_student_photo_url($studentId)
                ];
            }
        }

        $stmt->close();
        $conn->close();

        // Final API response: rows + error (empty means success).
        return [
            'rows' => $rows,
            'error' => ''
        ];
    }
}



