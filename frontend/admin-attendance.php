<!--
FILE OVERVIEW:
- frontend\admin-attendance.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Dynamic blocks yahan PHP tags ke through inject hote hain.
-->
<div class="card section-card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="section-title mb-0">Student Attendance</h5>
            <span class="small-muted">Latest face-marked entries</span>
        </div>

        <?php if (($attendanceError ?? '') !== ''): ?>
            <div class="alert alert-warning mb-0"><?php echo htmlspecialchars((string)$attendanceError); ?></div>
        <?php elseif (empty($attendanceRows)): ?>
            <div class="alert alert-info mb-0">No attendance data found yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle dashboard-table">
                    <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Total Attendance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($attendanceRows as $attendance): ?>
                        <tr>
                            <td>
                                <?php if (!empty($attendance['photo_url'])): ?>
                                    <img
                                        src="<?php echo htmlspecialchars((string)$attendance['photo_url']); ?>"
                                        alt="<?php echo htmlspecialchars((string)$attendance['name']); ?>"
                                        class="student-avatar"
                                    >
                                <?php else: ?>
                                    <div class="avatar-fallback"><?php echo strtoupper(substr((string)($attendance['name'] ?? 'S'), 0, 1)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)($attendance['name'] ?? '')); ?></div>
                                <div class="small-muted">ID: <?php echo htmlspecialchars((string)($attendance['student_id'] ?? '')); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars((string)($attendance['attendance_date'] ?? '-')); ?></td>
                            <td><?php echo htmlspecialchars((string)($attendance['attendance_time'] ?? '-')); ?></td>
                            <td><?php echo (int)($attendance['total_attendance'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>



