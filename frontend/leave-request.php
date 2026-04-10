<?php
/*
FILE OVERVIEW:
- frontend\leave-request.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/
 require_once __DIR__ . '/../backend/leave-request.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Leave Request - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { font-family: 'poppins'; }
    </style>
</head>
<body class="bg-light">
<?php require('header.php'); ?>

<div class="container py-4">
    <h3 class="mb-3">Hostel Leave Request</h3>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Submit Leave Form</h5>

                    <?php if ($submitSuccess !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($submitSuccess); ?></div>
                    <?php endif; ?>

                    <?php if ($submitErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($submitErrors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="submit_leave_request">
                        <div class="col-md-6">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="student_name" class="form-control" required value="<?php echo htmlspecialchars($form['student_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email" class="form-control" required value="<?php echo htmlspecialchars($form['student_email']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Leave From</label>
                            <input type="date" name="leave_from" class="form-control" required value="<?php echo htmlspecialchars($form['leave_from']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Leave To</label>
                            <input type="date" name="leave_to" class="form-control" required value="<?php echo htmlspecialchars($form['leave_to']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" maxlength="10" required value="<?php echo htmlspecialchars($form['emergency_contact']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="4" required><?php echo htmlspecialchars($form['reason']); ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="emergency_override_request" id="emergency_override_request" value="1" <?php echo !empty($form['emergency_override_request']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="emergency_override_request">
                                    Emergency leave request (requires strict admin review)
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Submit Leave Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Check Leave Status</h5>

                    <?php if ($viewErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($viewErrors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mb-3">
                        <input type="hidden" name="action" value="view_leave_status">
                        <label class="form-label">Student Email</label>
                        <input type="email" name="student_email_lookup" class="form-control mb-2" required>
                        <button type="submit" class="btn btn-dark">View Requests</button>
                    </form>

                    <?php if ($foundRequests): ?>
                        <?php foreach ($foundRequests as $request): ?>
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-bold"><?php echo htmlspecialchars((string)$request['request_id']); ?></div>
                                <div><strong>Dates:</strong> <?php echo htmlspecialchars((string)$request['leave_from']); ?> to <?php echo htmlspecialchars((string)$request['leave_to']); ?></div>
                                <div><strong>Status:</strong> <?php echo htmlspecialchars((string)$request['status']); ?></div>
                                <div><strong>Reason:</strong> <?php echo nl2br(htmlspecialchars((string)$request['reason'])); ?></div>
                                <div><strong>Emergency Request:</strong> <?php echo !empty($request['emergency_override_requested']) ? 'Yes' : 'No'; ?></div>
                                <div><strong>Override Used:</strong> <?php echo !empty($request['override_used']) ? 'Yes' : 'No'; ?></div>
                                <div><strong>Admin Note:</strong>
                                    <?php
                                    $note = trim((string)($request['admin_note'] ?? ''));
                                    echo $note !== '' ? nl2br(htmlspecialchars($note)) : 'Pending review.';
                                    ?>
                                </div>
                                <?php if (!empty($request['override_note'])): ?>
                                    <div><strong>Override Reason:</strong> <?php echo nl2br(htmlspecialchars((string)$request['override_note'])); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



