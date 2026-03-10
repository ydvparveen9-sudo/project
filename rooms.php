<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        * { font-family: 'poppins'; }
        .custom-bg { background-color: #2ec; }
        .page-banner {
            background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                        url('https://www.lpu.in/lpu-assets/images/residence/apartments.jpg') center/cover no-repeat;
            height: 220px;
            display: flex; align-items: center;
        }
    </style>
</head>
<body class="bg-light">

<?php require('inc/header.php') ?>

<!-- Page Banner -->
<div class="page-banner text-white text-center">
    <div>
        <h1 class="fw-bold">Our Hostel Rooms</h1>
        <p class="mb-0"><a href="index.php" class="text-white text-decoration-none">Home</a> &rsaquo; Rooms</p>
    </div>
</div>

<!-- Rooms Section -->
<div class="container py-5">
    <h2 class="text-center fw-bold mb-5">Choose Your Room</h2>
    <div class="row">

        <!-- Triple Seater -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="https://www.lpu.in/lpu-assets/images/residence/apartments.jpg" class="card-img-top" style="height:220px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="mb-0">Triple Seater</h5>
                        <span class="badge bg-success">Economy</span>
                    </div>
                    <p class="text-muted small mb-2"><i class="bi bi-people-fill me-1"></i>3 Students per Room</p>
                    <h4 class="mb-3">&#8377;25,000 / per Term</h4>
                    <div class="features mb-3">
                        <h6 class="mb-2">Facilities</h6>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-snow me-1"></i>Non-AC Room</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-person-workspace me-1"></i>Study Table &amp; Chair</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24×7 Wi-Fi</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-lightbulb me-1"></i>Common Washroom</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24×7 Security</span>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                        <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Double Seater -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="https://www.lpu.in/lpu-assets/images/about-lpu/infrastructure/large/girls-hostel-room.webp" class="card-img-top" style="height:220px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="mb-0">Double Seater</h5>
                        <span class="badge bg-primary">Popular</span>
                    </div>
                    <p class="text-muted small mb-2"><i class="bi bi-people-fill me-1"></i>2 Students per Room</p>
                    <h4 class="mb-3">&#8377;40,000 / per Term</h4>
                    <div class="features mb-3">
                        <h6 class="mb-2">Facilities</h6>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-snow me-1"></i>AC / Non-AC</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-person-workspace me-1"></i>Study Table &amp; Chair</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24×7 Wi-Fi</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-droplet me-1"></i>Attached Washroom</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24×7 Security</span>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                        <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Single Seater -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="https://www.lpu.in/lpu-assets/images/residence/residential.jpg" class="card-img-top" style="height:220px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="mb-0">Single Seater</h5>
                        <span class="badge bg-warning text-dark">Premium</span>
                    </div>
                    <p class="text-muted small mb-2"><i class="bi bi-person-fill me-1"></i>1 Student per Room</p>
                    <h4 class="mb-3">&#8377;65,000 / per Term</h4>
                    <div class="features mb-3">
                        <h6 class="mb-2">Facilities</h6>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-snow me-1"></i>AC Room</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-person-workspace me-1"></i>Study Table &amp; Chair</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24×7 Wi-Fi</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-droplet me-1"></i>Private Washroom</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-box-seam me-1"></i>Wardrobe &amp; Storage</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24×7 Security</span>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                        <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suite Room -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow h-100">
                <img src="https://www.lpu.in/lpu-assets/images/residence/apartments.jpg" class="card-img-top" style="height:220px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="mb-0">Suite Room</h5>
                        <span class="badge bg-danger">Luxury</span>
                    </div>
                    <p class="text-muted small mb-2"><i class="bi bi-person-fill me-1"></i>1 Student per Room</p>
                    <h4 class="mb-3">&#8377;90,000 / per Term</h4>
                    <div class="features mb-3">
                        <h6 class="mb-2">Facilities</h6>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-snow me-1"></i>AC Room</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-tv me-1"></i>Smart TV</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24×7 Wi-Fi</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-droplet me-1"></i>Private Washroom</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-cup-hot me-1"></i>Mini Kitchen</span>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24×7 Security</span>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                        <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<h5 class="text-center bg-dark text-white p-3 m-0">Design and Developed BY Parveen &amp; RAJ</h5>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
