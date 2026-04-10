<!--
FILE OVERVIEW:
- frontend\about.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Dynamic blocks yahan PHP tags ke through inject hote hain.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Hostel Management</title>
    <!-- Ready design + icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    
    <style>
        * { font-family: 'poppins'; }
        /* button and col */
        .custom-bg { background-color: rgb(153, 238, 34); }


        
     /* Image + dark overlay Text clear dikhe */


        .page-banner {
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                        url('https://www.lpu.in/lpu-assets/images/residence/residential.jpg') center/cover no-repeat;
            height: 220px;
            display: flex; align-items: center;
        }
           

        /* Numbers bade aur attractive */
        .stat-number { font-size: 2.5rem; font-weight: 700; color: #2ec; }
        .team-img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #2ec; }
    </style>


</head>
<body class="bg-light">

<?php require('header.php') ?>

<!-- Page Banner -->
<div class="page-banner text-white text-center">
    <div>
        <h1 class="fw-bold">About Us</h1>
        <p class="mb-0"><a href="index.php" class="text-white text-decoration-none">Home</a> &rsaquo; About</p>
    </div>
</div>

<!-- About Section -->
<div class="container py-5">

    <!-- Intro -->
    <div class="row align-items-center g-4 mb-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-3">Welcome to LPU Hostel Management</h2>
            <p class="text-muted">Lovely Professional University's hostel management system provides a comfortable, safe, and supportive environment for students. Our hostels are designed to offer a home-away-from-home experience with world-class facilities.</p>
            <p class="text-muted">We ensure every student gets a hygienic, secure, and peaceful place to live, study, and grow. With multiple room categories and modern amenities, we cater to the needs of thousands of students every year.</p>
            <a href="contact.php" class="btn text-white custom-bg shadow-none mt-2">Contact Us</a>
        </div>
        <div class="col-lg-6">
            <img src="https://happenings.lpu.in/wp-content/uploads/2019/06/LPu1.png" class="img-fluid rounded shadow" alt="LPU Campus">
        </div>
    </div>

    <!-- Stats -->
    <div class="row text-center g-3 mb-5">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow py-4 px-2">
                <div class="stat-number">10,000+</div>
                <p class="text-muted small mb-0">Students Accommodated</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow py-4 px-2">
                <div class="stat-number">50+</div>
                <p class="text-muted small mb-0">Hostel Blocks</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow py-4 px-2">
                <div class="stat-number">24/7</div>
                <p class="text-muted small mb-0">Security & Support</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow py-4 px-2">
                <div class="stat-number">15+</div>
                <p class="text-muted small mb-0">Years of Excellence</p>
            </div>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-bullseye me-2 text-success"></i>Our Mission</h5>
                <p class="text-muted mb-0">To provide safe, comfortable, and affordable residential facilities to students, fostering an environment that promotes academic excellence, personal growth, and community living.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-eye-fill me-2 text-primary"></i>Our Vision</h5>
                <p class="text-muted mb-0">To become the most preferred student housing system in India by delivering world-class residential experiences, driven by innovation, inclusivity, and student welfare.</p>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
    <h3 class="fw-bold text-center mb-4">Why Choose LPU Hostel?</h3>
    <div class="row g-3 mb-5">
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Safe & Secure Environment</h6>
                    <p class="text-muted small mb-0">CCTV, trained security guards, and biometric entry for student safety.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Hygienic Food & Mess</h6>
                    <p class="text-muted small mb-0">Nutritious and delicious meals served in well-maintained mess facilities.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">High-Speed Internet</h6>
                    <p class="text-muted small mb-0">24ÃƒÆ’Ã¢â‚¬â€7 Wi-Fi connectivity to support academic and personal needs.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Affordable Pricing</h6>
                    <p class="text-muted small mb-0">Multiple room categories to suit every student's budget and needs.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Sports & Recreation</h6>
                    <p class="text-muted small mb-0">Gym, sports ground, and recreation areas for student well-being.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="d-flex align-items-start gap-3 bg-white rounded shadow-sm p-3 h-100">
                <i class="bi bi-check-circle-fill text-success fs-4 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Medical Support</h6>
                    <p class="text-muted small mb-0">On-campus clinic available 24/7 for health emergencies.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



