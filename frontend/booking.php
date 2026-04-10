<?php
/*
FILE OVERVIEW:
- frontend\booking.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/
 require_once __DIR__ . '/../backend/booking.php'; ?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        * { font-family: 'poppins'; }
        .custom-bg { background-color: #2ec; }
        .btn-brand {
            background: linear-gradient(135deg, #1ea98a, #127e67);
            border: none;
            color: #fff;
        }
        .btn-brand:hover {
            color: #fff;
            background: linear-gradient(135deg, #189a7f, #0f6d59);
        }
        .page-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
                        url('https://www.lpu.in/lpu-assets/images/residence/apartments.jpg') center/cover no-repeat;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-box {
            border: 1px solid #c9efe7;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff, #f7fffc);
        }
        .qr-preview {
            max-width: 260px;
            width: 100%;
            border-radius: 14px;
            border: 1px solid #d8ebe6;
            box-shadow: 0 8px 20px rgba(17, 96, 80, 0.14);
        }
        .booking-summary {
            background: #f7fbfa;
            border: 1px solid #e2efec;
            border-radius: 12px;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-light">
<?php require('header.php'); ?>

<div class="page-banner text-white text-center mb-4">
    <div>
        <h1 class="fw-bold">Book Your Hostel Room</h1>
        <p class="mb-0"><a href="index.php" class="text-white text-decoration-none">Home</a> &rsaquo; Booking</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h4 class="mb-4">Room Booking Form</h4>

                    <!-- agar form submit ho gaya successfully
                 to green message show hoga -->

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>

                    <?php if ($infoMessage !== ''): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($infoMessage); ?></div>
                    <?php endif; ?>
                    <!-- agar koi mistake (error) ho
                    to red box me list show hogi -->

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>


                    <?php endif; ?>

                    <?php if (!$showPaymentStep): ?>
                        <form method="post" action="booking.php<?php echo $selectedRoom !== '' ? '?room=' . urlencode($selectedRoom) : ''; ?>">
                            <input type="hidden" name="action" value="submit_booking">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Student Name</label>
                                    <input type="text" name="student_name" class="form-control" required value="<?php echo htmlspecialchars($formData['student_name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="student_email" class="form-control" required value="<?php echo htmlspecialchars($formData['student_email']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="student_phone" class="form-control" required value="<?php echo htmlspecialchars($formData['student_phone']); ?>" placeholder="10 digits" maxlength="10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Program</label>
                                    <select name="program" class="form-select" required>
                                        <option value="">Select Program</option>
                                        <?php foreach ($programOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($selectedProgram === $option) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking Start Date</label>
                                    <input type="date" name="check_in_date" class="form-control" required value="<?php echo htmlspecialchars($formData['check_in_date']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking End Date</label>
                                    <input type="date" name="check_out_date" class="form-control" required value="<?php echo htmlspecialchars($formData['check_out_date']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Room Type</label>
                                    <select name="room_type" id="room_type" class="form-select" required>
                                        <option value="">Select Room Type</option>
                                        <?php foreach ($availableRoomOptions as $option): ?>
                                            <?php
                                                $isSelected = $currentRoomSelection === $option ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo $isSelected; ?>>
                                                <?php echo htmlspecialchars($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bed</label>
                                    <select name="bed_id" id="bed_id" class="form-select" required>
                                        <option value="">Select Bed</option>
                                        <?php
                                            $currentBeds = $roomBedOptions[$currentRoomSelection] ?? [];
                                            foreach ($currentBeds as $bedOption):
                                                $selected = $currentBedSelection === $bedOption ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo htmlspecialchars((string)$bedOption); ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars((string)$bedOption); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-brand px-4">Proceed To Payment</button>
                                    <a href="rooms.php" class="btn btn-outline-dark ms-2">View Rooms</a>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="payment-box p-3 p-md-4">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6 text-center">
                                    <h5 class="mb-3">Scan & Pay</h5>
                                    <img src="<?php echo htmlspecialchars($qrImagePath); ?>" alt="UPI QR Code" class="qr-preview">
                                    <p class="text-muted mt-3 mb-0">QR scan karke payment karein, phir confirm payment button dabayein.</p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (is_array($pendingBooking)): ?>
                                        <div class="booking-summary mb-3">
                                            <h6 class="fw-bold mb-2">Booking Summary</h6>
                                            <div><strong>Booking ID:</strong> <?php echo htmlspecialchars((string)($pendingBooking['booking_id'] ?? '')); ?></div>
                                            <div><strong>Name:</strong> <?php echo htmlspecialchars((string)($pendingBooking['student_name'] ?? '')); ?></div>
                                            <div><strong>Email:</strong> <?php echo htmlspecialchars((string)($pendingBooking['student_email'] ?? '')); ?></div>
                                            <div><strong>Room:</strong> <?php echo htmlspecialchars((string)($pendingBooking['room_type'] ?? '')); ?></div>
                                            <div><strong>Bed:</strong> <?php echo htmlspecialchars((string)($pendingBooking['bed_id'] ?? '')); ?></div>
                                            <div><strong>Dates:</strong> <?php echo htmlspecialchars((string)($pendingBooking['check_in_date'] ?? '')); ?> to <?php echo htmlspecialchars((string)($pendingBooking['check_out_date'] ?? '')); ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2">
                                        <form method="post" action="booking.php<?php echo $selectedRoom !== '' ? '?room=' . urlencode($selectedRoom) : ''; ?>">
                                            <input type="hidden" name="action" value="confirm_payment">
                                            <button type="submit" class="btn btn-success px-4">I Have Paid - Confirm Booking</button>
                                        </form>

                                        <form method="post" action="booking.php<?php echo $selectedRoom !== '' ? '?room=' . urlencode($selectedRoom) : ''; ?>">
                                            <input type="hidden" name="action" value="cancel_payment">
                                            <button type="submit" class="btn btn-outline-danger px-4">Cancel Payment</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!$showPaymentStep): ?>
<script>
    (function () {
        var roomSelect = document.getElementById('room_type');
        var bedSelect = document.getElementById('bed_id');
        var bedMap = <?php echo json_encode($roomBedOptions, JSON_UNESCAPED_SLASHES); ?>;
        var selectedBed = <?php echo json_encode((string)$currentBedSelection, JSON_UNESCAPED_SLASHES); ?>;

        if (!roomSelect || !bedSelect) {
            return;
        }

        function renderBeds(roomName) {
            while (bedSelect.options.length > 0) {
                bedSelect.remove(0);
            }

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select Bed';
            bedSelect.appendChild(placeholder);

            if (!roomName || !Array.isArray(bedMap[roomName])) {
                bedSelect.value = '';
                return;
            }

            bedMap[roomName].forEach(function (bedId) {
                var option = document.createElement('option');
                option.value = bedId;
                option.textContent = bedId;
                if (selectedBed && selectedBed === bedId) {
                    option.selected = true;
                }
                bedSelect.appendChild(option);
            });
        }

        roomSelect.addEventListener('change', function () {
            selectedBed = '';
            renderBeds(roomSelect.value);
        });

        renderBeds(roomSelect.value);
    })();
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>



