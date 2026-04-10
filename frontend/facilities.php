<!--
FILE OVERVIEW:
- frontend\facilities.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Dynamic blocks yahan PHP tags ke through inject hote hain.
-->
<?php
$facilities = [
    ['icon' => 'bi-wifi', 'title' => 'Wi-Fi', 'desc' => 'High-speed internet connectivity in all hostel blocks.'],
    ['icon' => 'bi-shield-check', 'title' => 'Security', 'desc' => '24x7 security staff and regular surveillance support.'],
    ['icon' => 'bi-cup-hot', 'title' => 'Mess / Cafeteria', 'desc' => 'Healthy meals with hygienic and student-friendly service.'],
    ['icon' => 'bi-droplet', 'title' => 'Hot Water', 'desc' => 'Continuous hot water availability in room wash areas.'],
    ['icon' => 'bi-lightning-charge', 'title' => 'Power Backup', 'desc' => 'Reliable backup system for uninterrupted daily routine.'],
    ['icon' => 'bi-bicycle', 'title' => 'Gym & Sports', 'desc' => 'Fitness space and sports facilities for active lifestyle.'],
    ['icon' => 'bi-book', 'title' => 'Library', 'desc' => 'Quiet reading zone with books and study-friendly seating.'],
    ['icon' => 'bi-camera-video', 'title' => 'CCTV', 'desc' => 'Important campus areas monitored for student safety.'],
    ['icon' => 'bi-snow', 'title' => 'AC Rooms', 'desc' => 'Premium room options with AC and added comfort.'],
    ['icon' => 'bi-prescription2', 'title' => 'Medical Help', 'desc' => 'Basic medical assistance and emergency support nearby.'],
    ['icon' => 'bi-bus-front', 'title' => 'Transport', 'desc' => 'Planned transport support for city and campus routes.'],
    ['icon' => 'bi-basket3', 'title' => 'Laundry', 'desc' => 'Convenient and affordable laundry service for students.']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f7f9;
        }

        .page-banner {
            background:
                linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
                url('https://www.lpu.in/lpu-assets/images/residence/residential.jpg') center/cover no-repeat;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-heading h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .section-heading p {
            color: #6c757d;
            margin-bottom: 0;
        }

        .facility-card {
            height: 100%;
            border: 1px solid #e7edf1;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .facility-card:hover {
            transform: translateY(-4px);
        }

        .facility-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8f6f2;
            color: #0f8a74;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .facility-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.45rem;
        }

        .facility-desc {
            margin: 0;
            color: #6c757d;
            font-size: 0.92rem;
        }

        .page-footer {
            background: #1f2e35;
            color: #fff;
            text-align: center;
            padding: 0.9rem;
            margin: 0;
        }
    </style>
</head>
<body>
<?php require('header.php'); ?>

<div class="page-banner">
    <div>
        <h1 class="fw-bold mb-2">Facilities</h1>
        <p class="mb-0">Comfortable and secure hostel life for every student</p>
    </div>
</div>

<section class="container py-5">
    <div class="section-heading">
        <h2>Our Hostel Facilities</h2>
        <p>Simple, practical and student-friendly services across campus.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($facilities as $item): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="facility-card bg-white p-4 text-center">
                    <span class="facility-icon"><i class="bi <?php echo htmlspecialchars((string)$item['icon']); ?>"></i></span>
                    <h3 class="facility-title"><?php echo htmlspecialchars((string)$item['title']); ?></h3>
                    <p class="facility-desc"><?php echo htmlspecialchars((string)$item['desc']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


