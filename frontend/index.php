<!--
FILE OVERVIEW:
- frontend\index.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Dynamic blocks yahan PHP tags ke through inject hote hain.
-->
<!DOCTYPE html>
<!-- Home page  -->

<html lang="en">



<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile me page responsive banane ke liye -->
  <title>Hostel Management</title>



  <!-- Bootstrap CSS  ready-made CSS ka shortcut-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- Google Fonts  ready-made CSS ka shortcut-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Betania+Patmos+In&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- Image slider ke liye library -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />



  <style>
    * {
      font-family: 'poppins';
    }

    /* Slider ka size set */
    .swiper-container {
      width: 100%;
      height: 450px;
      margin: 0 auto;
    }

    .swiper-slide {
      height: 450px;
      overflow: hidden;
    }

    /* Image stretch na ho, properly fit ho */
    .swiper-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .custom-bg {
      background-color: #2ec;
    }
  </style>
</head>








<body class="bg-light">

  <!-- Shared header include: homepage par bhi same full navbar show karega -->
  <?php require_once __DIR__ . '/header.php'; ?>










  <!-- SLIDER START - Page ka  image slider -->

  <div class="container-fluid">
    <div class="swiper swiper-container">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <img src="	https://www.lpu.in/lpu-assets/images/about-lpu/infrastructure/large/girls-hostel-room.webp	" class="w-100" d-block>
        </div>
        <div class="swiper-slide">
          <img src="		https://www.lpu.in/lpu-assets/images/residence/residential.jpg" alt="Hostel 2" />
        </div>
        <div class="swiper-slide">
          <img src="https://happenings.lpu.in/wp-content/uploads/2019/06/LPu1.png" alt="Hostel 4" />
        </div>
        <div class="swiper-slide">
          <img src="image/nirf-slide.webp" alt="NIRF" />

          <div class="swiper-slide">
            <img src="		https://www.lpu.in/lpu-assets/images/residence/residential.jpg" alt="Hostel 2" />
          </div>
          <div class="swiper-slide">
            <img src="		https://www.lpu.in/lpu-assets/images/residence/residential.jpg" alt="Hostel 2" />
          </div>
          <div class="swiper-slide">
            <img src="		https://www.lpu.in/lpu-assets/images/residence/residential.jpg" alt="Hostel 2" />
          </div>
          <div class="swiper-slide">
            <img src="		https://www.lpu.in/lpu-assets/images/residence/residential.jpg" alt="Hostel 2" />
          </div>
        </div>
      </div>
    </div>
  </div>








  <!--  Check BOOKING AVAILABILITY SECTION START    -->

  <div class="container">
    <div class="row">
      <div class="col-lg-10bg-white shadow p-4 rounded">
        <h4 class="mb-4"> Check Booking Availabilty </h4>
        <form>

          <div class="row align-items-end">

            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Booking Hostal Room</label>
              <input type="date" class="form-control shadow-none">
            </div>

            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Check Term OUT</label>
              <input type="date" class="form-control shadow-none">
            </div>

            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Program</label>
              <select class="form-select shadow-none">
                <option selected>Open this select menu</option>

                <option value="1">BCA</option>
                <option value="2">B.Pharm (Pharmacy)</option>
                <option value="3">M.Lib.I.Sc (Library Science)</option>
                <option value="4">BMLT (Medical Lab Technology)</option>
                <option value="5">Design (Fashion, Interior, Graphics, Product, Film & TV)</option>
              </select>
              </select>
            </div>

            <div class="col-lg-1 mb-lg-3 mt-2">
              <button type="submit" class="btn text-white shadow-none custom-bg">Submit</button>
            </div>
          </div>
      </div>






      <!-- ROOMS SECTION START - Hostel ke rooms ki cards             -->
      <!-- ============================================================ -->
      <h2 class="mt-5 pt-4 mb-4 text-center fw-bold"> OUR Hostel Rooms</h2>
      <div class="container">
        <div class="row">

          <!-- Card 1: Triple Seater -->
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
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24Ãƒâ€”7 Wi-Fi</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-lightbulb me-1"></i>Common Washroom</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24Ãƒâ€”7 Security</span>
                </div>
                <div class="d-flex gap-2 mt-auto">
                  <a href="booking.php?room=Triple+Seater" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                  <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Double Seater -->
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
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24Ãƒâ€”7 Wi-Fi</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-droplet me-1"></i>Attached Washroom</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24Ãƒâ€”7 Security</span>
                </div>
                <div class="d-flex gap-2 mt-auto">
                  <a href="booking.php?room=Double+Seater" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                  <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3: Single Seater -->
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
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-wifi me-1"></i>24Ãƒâ€”7 Wi-Fi</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-droplet me-1"></i>Private Washroom</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-box-seam me-1"></i>Wardrobe &amp; Storage</span>
                  <span class="badge bg-light text-dark border me-1 mb-1"><i class="bi bi-shield-check me-1"></i>24Ãƒâ€”7 Security</span>
                </div>
                <div class="d-flex gap-2 mt-auto">
                  <a href="booking.php?room=Single+Seater" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                  <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="col-lg-12 text-center mt-3">
          <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none"> More rooms &raquo; </a>










          <!-- LOCATION + CALL US + FOLLOW US SECTION START  -->

          <h2 class="mt-5 pt-4 text-start fw-bold h-front"> Location </h2>
          <div class="container-fluid px-0">
            <div class="row justify-content-start">
              <div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
                <iframe class="w-100" height="250" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502022.9106280264!2d72.76978934198!3d30.999931723743494!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ca2277bd0f120b3%3A0xffde6d244bfac71c!2sDivision%20of%20Residential%20Services%2C%20LPU!5e0!3m2!1sen!2sin!4v1772508793621!5m2!1sen!2sin" style="border: 4px solid rgba(255,255,255,0.85); border-radius: 8px;" loading="fast"></iframe>
              </div>
              <div class="col-lg-4 col-md-4 ps-lg-4">

                <!-- Call Us -->
                <div class="bg-white rounded shadow-sm p-4 mb-4">
                  <h5 class="fw-bold mb-3"><i class="bi bi-telephone-fill me-2 text-success"></i>Call Us</h5>
                  <p class="mb-1 text-muted small">LPU Hostel Helpdesk</p>
                  <a href="tel:01824404404" class="d-block fw-semibold text-dark text-decoration-none mb-1"><i class="bi bi-phone me-1"></i> 01824-404404</a>
                  <a href="tel:7027463676" class="d-block fw-semibold text-dark text-decoration-none mb-1"><i class="bi bi-phone me-1"></i> 7027463676</a>
                  <p class="mb-1 mt-3 text-muted small">Address</p>
                  <p class="mb-0 small"><i class="bi bi-geo-alt-fill me-1 text-danger"></i>Lovely Professional University,<br>Phagwara, Punjab Ã¢â‚¬â€œ 144411, India</p>
                </div>
              </div>
              <br> <br>



  <?php require_once __DIR__ . '/footer.php'; ?>
                  <!-- ============================================================ -->
                  <!-- FOOTER END                                                 -->
                  <!-- ============================================================ -->

                  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
                  <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

                  <script>
                    var swiper = new Swiper(".swiper-container", {
                      spaceBetween: 30,
                      effect: "fade",
                      loop: true,
                      autoplay: {
                        delay: 1500,
                        disableOnInteraction: false,
                      }

                    });
                  </script>
</body>

</html>
