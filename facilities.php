<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        * { font-family: 'poppins'; }
        .custom-bg { background-color: #2ec; }
        .page-banner {
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                        url('https://www.lpu.in/lpu-assets/images/residence/residential.jpg') center/cover no-repeat;
            height: 220px;
            display: flex; align-items: center;
        }
        .facility-icon { font-size: 2.5rem; color: #2ec; }
    </style>
</head>
<body class="bg-light">

<?php require('inc/header.php') ?>

<!-- Page Banner -->
<div class="page-banner text-white text-center">
    <div>
        <h1 class="fw-bold">Facilities</h1>
        <p class="mb-0"><a href="index.php" class="text-white text-decoration-none">Home</a> &rsaquo; Facilities</p>
    </div>
</div>

<!-- Facilities Section -->
<div class="container py-5">
    <h2 class="text-center fw-bold mb-2">World Class Facilities</h2>
    <p class="text-center text-muted mb-5">We provide top-notch facilities to ensure a comfortable stay for all students.</p>

    <div class="row g-4">

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-wifi facility-icon mb-3"></i>
                <h5 class="fw-bold">24×7 Wi-Fi</h5>
                <p class="text-muted small">High-speed internet connectivity available throughout the hostel premises.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-shield-check facility-icon mb-3"></i> 
                <h5 class="fw-bold">24×7 Security</h5>
                <p class="text-muted small">Round the clock CCTV surveillance and trained security personnel.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-cup-hot facility-icon mb-3"></i>
                <h5 class="fw-bold">Mess / Cafeteria</h5>
                <p class="text-muted small">Nutritious and hygienic food served three times a day in the mess.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-droplet facility-icon mb-3"></i>
                <h5 class="fw-bold">Hot Water</h5>
                <p class="text-muted small">24-hour hot water facility available in all rooms and washrooms.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-lightning-charge facility-icon mb-3"></i>
                <h5 class="fw-bold">Power Backup</h5>
                <p class="text-muted small">Uninterrupted power supply with 100% power backup available 24/7.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-bicycle facility-icon mb-3"></i>
                <h5 class="fw-bold">Gym & Sports</h5>
                <p class="text-muted small">Well-equipped gymnasium and outdoor sports facilities for students.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-book facility-icon mb-3"></i>
                <h5 class="fw-bold">Library</h5>
                <p class="text-muted small">Quiet and spacious library with a wide collection of books and journals.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-camera-video facility-icon mb-3"></i>
                <h5 class="fw-bold">CCTV Surveillance</h5>
                <p class="text-muted small">Complete campus covered with CCTV cameras for student safety.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-snow facility-icon mb-3"></i>
                <h5 class="fw-bold">AC Rooms</h5>
                <p class="text-muted small">Air conditioned rooms available for premium and suite room bookings.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-prescription2 facility-icon mb-3"></i>
                <h5 class="fw-bold">Medical Facility</h5>
                <p class="text-muted small">On-campus clinic and doctor available for medical emergencies.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-bus-front facility-icon mb-3"></i>
                <h5 class="fw-bold">Transport</h5>
                <p class="text-muted small">Bus facility available for students to and from campus/city.</p>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow text-center p-4 h-100">
                <i class="bi bi-washing-machine facility-icon mb-3"></i>
                <h5 class="fw-bold">Laundry</h5>
                <p class="text-muted small">Laundry service available within the hostel at affordable prices.</p>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<h5 class="text-center bg-dark text-white p-3 m-0">Design and Developed BY Parveen &amp; RAJ</h5>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
