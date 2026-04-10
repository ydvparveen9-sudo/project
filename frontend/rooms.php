<?php
/*
FILE OVERVIEW:
- frontend\rooms.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/
 require_once __DIR__ . '/../backend/rooms.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - Hostel Management</title>

    <!-- Bootstrap ek ready-made CSS library hai -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

   <!--  Icons use karne ke liye (ÃƒÂ°Ã…Â¸Ã¢â‚¬ËœÃ‚Â¤, ÃƒÂ°Ã…Â¸Ã¢â‚¬Å“Ã‚Â¶, ÃƒÂ¢Ã‚ÂÃ¢â‚¬Å¾ÃƒÂ¯Ã‚Â¸Ã‚Â etc.) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">



    <style>
        /*  poppins Sab text ka font same ho jayega */
        * { font-family: 'poppins'; }
        

            /* Ye ek custom class hai ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢ buttons ke liye color */
        .custom-bg { background-color: #2ec; }


        /* Image background */
        .page-banner {  
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                        url('https://www.lpu.in/lpu-assets/images/residence/apartments.jpg') center/cover no-repeat;
            height: 220px;
            display: flex; align-items: center;
        }
    </style>
</head>






<body class="bg-light">

<!-- Same navbar har page pe use karne ke liye
 Code repeat nahi karna padta -->

<?php require('header.php') ?>



<!-- Page Banner -->
<div class="page-banner text-white text-center">
    <div>
        <h1 class="fw-bold">Our Hostel Rooms</h1>
    </div>
</div>




<!-- Rooms Section -->
<div class="container py-5">
    <h2 class="text-center fw-bold mb-5">Choose Your Room</h2>
    <div class="row">

        <?php if (!$rooms): ?>
            <div class="col-12">
                <div class="alert alert-info">No rooms configured yet. Admin can add rooms from Admin Dashboard.</div>
            </div>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <?php
                    $isAvailable = room_available_bed_count($room) > 0;
                    $imageUrl = (string)($room['image_url'] ?? '');
                    if ($imageUrl === '') {
                        $imageUrl = 'https://www.lpu.in/lpu-assets/images/residence/residential.jpg';
                    }
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow h-100">
                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="card-img-top" style="height:220px; object-fit:cover;" alt="Room image">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h5 class="mb-0"><?php echo htmlspecialchars((string)($room['name'] ?? '')); ?></h5>
                                <span class="badge <?php echo $isAvailable ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $isAvailable ? 'Available' : 'Not Available'; ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-people-fill me-1"></i>
                                <?php echo htmlspecialchars((string)($room['room_type'] ?? '')); ?> Room
                            </p>
                            <h4 class="mb-3">&#8377;<?php echo number_format((int)($room['price_per_term'] ?? 0)); ?> / per Term</h4>
                            <div class="d-flex gap-2 mt-auto">
                                <?php if ($isAvailable): ?>
                                    <a href="booking.php?room=<?php echo urlencode((string)($room['name'] ?? '')); ?>" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>Currently Full</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



