<?php
/*
FILE OVERVIEW:
- frontend\student-support.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/
 require_once __DIR__ . '/../backend/student-support.php'; ?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS Status - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { font-family: 'poppins'; }
    </style>
</head>
<body class="bg-light">
<?php require('header.php'); ?>

<div class="container py-4">
    <h3 class="mb-3">RMS Status</h3>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-2">Submit Your Problem</h5>
                  

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

                    <form method="post">
                        <input type="hidden" name="action" value="submit_problem">
                        <div class="mb-2">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="student_name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Problem Message</label>
                            <textarea name="problem_message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Problem</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="mb-2">Check Your Solution</h5>
                    <p class="text-muted small">Ender your Email</p>

                    <?php if ($viewErrors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($viewErrors as $err): ?>
                                    <li><?php echo htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="view_solution">
                        <div class="col-12">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email_lookup" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">View Status</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($foundMessages): ?>
                <?php foreach ($foundMessages as $foundMessage): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <!-- Ticket ID show ÃƒÂ Ã‚Â¤Ã¢â‚¬Â¢ÃƒÂ Ã‚Â¤Ã‚Â° ÃƒÂ Ã‚Â¤Ã‚Â°ÃƒÂ Ã‚Â¤Ã‚Â¹ÃƒÂ Ã‚Â¥Ã¢â‚¬Â¡ ÃƒÂ Ã‚Â¤Ã‚Â¹ÃƒÂ Ã‚Â¥Ã‹â€ ÃƒÂ Ã‚Â¤Ã¢â‚¬Å¡ -->
                            <h5 class="mb-2">Ticket: <?php echo htmlspecialchars((string)$foundMessage['ticket_id']); ?></h5> 
                            <p class="mb-1"><strong>Status:</strong> <?php echo htmlspecialchars((string)$foundMessage['status']); ?></p>
                            <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars((string)$foundMessage['subject']); ?></p>
                            <p class="mb-1"><strong>Your Problem:</strong><br><?php echo nl2br(htmlspecialchars((string)$foundMessage['problem_message'])); ?></p>
                            <hr>
                            <p class="mb-0"><strong>Admin Reply:</strong><br>
                                <?php
                                    $reply = trim((string)($foundMessage['admin_reply'] ?? ''));
                                    echo $reply !== ''
                                        ? nl2br(htmlspecialchars($reply))
                                        : 'Admin reply pending. Please check later.';
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>



