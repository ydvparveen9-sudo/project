<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        * { font-family: 'poppins'; }
        .custom-bg { background-color: #2ec; }
        .page-banner {
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                        url('https://happenings.lpu.in/wp-content/uploads/2019/06/LPu1.png') center/cover no-repeat;
            height: 220px;
            display: flex; align-items: center;
        }
        .info-icon { font-size: 1.8rem; color: #2ec; }
    </style>
</head>
<body class="bg-light">

<?php require('inc/header.php') ?>

<!-- Page Banner -->
<div class="page-banner text-white text-center">
    <div>
        <h1 class="fw-bold">Contact Us</h1>
        <p class="mb-0"><a href="index.php" class="text-white text-decoration-none">Home</a> &rsaquo; Contact Us</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">

        <!-- Contact Info Cards -->
        <div class="col-lg-4">

            <div class="card border-0 shadow p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-geo-alt-fill info-icon"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="text-muted small mb-0">Lovely Professional University,<br>Phagwara, Punjab – 144411, India</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-telephone-fill info-icon"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Phone</h6>
                        <a href="tel:01824404404" class="d-block text-dark text-decoration-none small">01824-404404</a>
                        <a href="tel:7027463676" class="d-block text-dark text-decoration-none small">7027463676</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-envelope-fill info-icon"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <a href="mailto:hostel@lpu.co.in" class="d-block text-dark text-decoration-none small">hostel@lpu.co.in</a>
                        <a href="mailto:admissions@lpu.co.in" class="d-block text-dark text-decoration-none small">admissions@lpu.co.in</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-share-fill me-2 text-primary"></i>Follow Us</h6>
                <a href="https://www.facebook.com/LPUuniversity" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2 shadow-none text-start">
                    <i class="bi bi-facebook me-2"></i>Facebook
                </a>
                <a href="https://www.instagram.com/lpu_university/" target="_blank" class="btn btn-sm w-100 mb-2 shadow-none text-start text-white" style="background:#E1306C;">
                    <i class="bi bi-instagram me-2"></i>Instagram
                </a>
                <a href="https://twitter.com/LPU_univ" target="_blank" class="btn btn-outline-dark btn-sm w-100 mb-2 shadow-none text-start">
                    <i class="bi bi-twitter-x me-2"></i>Twitter / X
                </a>
                <a href="https://www.youtube.com/@LPU" target="_blank" class="btn btn-sm w-100 shadow-none text-start text-white" style="background:#FF0000;">
                    <i class="bi bi-youtube me-2"></i>YouTube
                </a>
            </div>

        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow p-4 mb-4">
                <h4 class="fw-bold mb-4">Send us a Message</h4>
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control shadow-none" placeholder="Enter your name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control shadow-none" placeholder="Enter your email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="number" class="form-control shadow-none" placeholder="Enter phone number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control shadow-none" placeholder="Subject">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control shadow-none" rows="5" placeholder="Write your message here..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn text-white custom-bg shadow-none px-4">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Map -->
            <div class="card border-0 shadow p-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-map me-2"></i>Our Location</h6>
                <iframe class="w-100 rounded" height="280"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502022.9106280264!2d72.76978934198!3d30.999931723743494!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ca2277bd0f120b3%3A0xffde6d244bfac71c!2sDivision%20of%20Residential%20Services%2C%20LPU!5e0!3m2!1sen!2sin!4v1772508793621!5m2!1sen!2sin"
                    style="border: 3px solid #eee; border-radius: 8px;" loading="lazy"></iframe>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<h5 class="text-center bg-dark text-white p-3 m-0">Design and Developed BY Parveen &amp; RAJ</h5>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
