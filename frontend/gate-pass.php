<?php
/*
FILE OVERVIEW:
- frontend\gate-pass.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/
require_once __DIR__ . '/../backend/leave-request.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Gate Pass QR - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { font-family: 'poppins'; }
    </style>
</head>
<body class="bg-light">
<?php require('header.php'); ?>

<div class="container py-4">
    <h3 class="mb-3">Generate Gate Pass QR (Approved Leave Only)</h3>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-3">Approved leave request ka ID aur same student email use karein.</p>

                    <?php if ($gatePassSuccess !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($gatePassSuccess); ?></div>
                    <?php endif; ?>

                    <?php if ($gatePassErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($gatePassErrors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mb-3">
                        <input type="hidden" name="action" value="generate_gate_pass_from_leave">
                        <label class="form-label">Leave Request ID (Approved)</label>
                        <input type="text" name="request_id" class="form-control mb-2" required value="<?php echo htmlspecialchars((string)($gateForm['request_id'] ?? '')); ?>">
                        <label class="form-label">Student Email</label>
                        <input type="email" name="student_email_for_gate" class="form-control mb-3" required value="<?php echo htmlspecialchars((string)($gateForm['student_email'] ?? '')); ?>">
                        <button type="submit" class="btn btn-success">Generate Gate Pass QR</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Generated Pass</h5>

                    <?php if (is_array($generatedGatePass) && $generatedGateQrUrl !== ''): ?>
                        <div class="border rounded p-3">
                            <div><strong>Pass ID:</strong> <?php echo htmlspecialchars((string)($generatedGatePass['pass_id'] ?? '')); ?></div>
                            <div><strong>Leave Request ID:</strong> <?php echo htmlspecialchars((string)($generatedGatePass['leave_request_id'] ?? '')); ?></div>
                            <div><strong>Status:</strong> <?php echo htmlspecialchars((string)($generatedGatePass['status'] ?? '')); ?></div>
                            <img src="<?php echo htmlspecialchars($generatedGateQrUrl); ?>" alt="Gate Pass QR" class="img-fluid mt-3" style="max-width: 220px;">
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"> pass .</div>
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



