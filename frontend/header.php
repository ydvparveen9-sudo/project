<!--
FILE OVERVIEW:
- frontend\header.php
- Frontend page file: UI render karta hai aur required hone par backend se aayi dynamic values display karta hai.
- Dynamic blocks yahan PHP tags ke through inject hote hain.
-->
<?php
require_once __DIR__ . '/../backend/auth.php';
$activeUser = current_user();

// Build current page URL for admin login return: jab admin logout ho to is page par vapis aa jayega.
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$currentPage = basename((string)(parse_url($uri, PHP_URL_PATH) ?? 'index.php'));
$currentPageUrl = urlencode($scheme . '://' . $host . $uri);
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&display=swap');

  .hm-navbar {
    background: linear-gradient(180deg, #ffffff 0%, #f5fbff 100%);
    border-bottom: 1px solid #dbe9f6;
  }
  .hm-navbar::before {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #19a0ff 0%, #2ec4b6 50%, #ffbf69 100%);
  }
  .hm-navbar .container-fluid {
    position: relative;
    z-index: 1;
  }
  .hm-navbar .navbar-brand {
    margin-right: 1rem !important;
    font-size: 1.18rem !important;
    letter-spacing: 0.6px;
    color: #0f3554 !important;
    white-space: nowrap;
    font-family: 'Cinzel', Georgia, serif;
    line-height: 1.1;
  }
  .hm-navbar .navbar-brand .hm-brand-main {
    background: linear-gradient(135deg, #0f3554 0%, #1f8bd6 50%, #17b3a9 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 8px 18px rgba(17, 106, 170, 0.16);
  }
  .hm-navbar .navbar-brand .hm-brand-sub {
    font-size: 0.78rem;
    letter-spacing: 1.6px;
    color: #3a6b91;
    text-transform: uppercase;
    display: block;
  }
  .hm-navbar .navbar-nav {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 0.2rem;
  }
  .hm-navbar .nav-link {
    font-size: 0.88rem;
    padding: 0.42rem 0.72rem !important;
    border-radius: 999px;
    color: #275273 !important;
    transition: all 0.2s ease;
    white-space: nowrap;
  }
  .hm-navbar .nav-link:hover {
    background-color: #e7f4ff;
    color: #0d5f96 !important;
  }
  .hm-navbar .nav-link.is-active {
    background: linear-gradient(135deg, #1597e5, #30c5b7);
    color: #ffffff !important;
    box-shadow: 0 6px 14px rgba(21, 151, 229, 0.25);
  }
  .hm-actions {
    gap: 0.45rem;
  }
  .hm-actions .btn {
    border-radius: 999px;
    padding: 0.38rem 0.85rem !important;
    font-size: 0.8rem;
    white-space: nowrap;
  }
  .hm-btn-admin {
    border-color: #1579bf !important;
    color: #1579bf !important;
  }
  .hm-btn-admin:hover {
    background-color: #1579bf !important;
    color: #ffffff !important;
  }
  .hm-btn-dashboard {
    background: #0f3554 !important;
    border-color: #0f3554 !important;
    color: #ffffff !important;
  }
  .hm-btn-dashboard:hover {
    background: #1a4f79 !important;
    border-color: #1a4f79 !important;
  }
  @media (max-width: 991.98px) {
    .hm-navbar .navbar-brand {
      font-size: 1rem !important;
    }
    .hm-navbar .navbar-brand .hm-brand-sub {
      font-size: 0.68rem;
      letter-spacing: 1.2px;
    }
    .hm-navbar .navbar-nav {
      margin-top: 0.4rem;
    }
    .hm-actions {
      margin-top: 0.6rem;
    }
  }
</style>
<!-- ============================================================ -->
<!-- HEADER / NAVBAR START                                      -->
<!-- ============================================================ -->
<nav class="navbar navbar-expand-lg navbar-light px-lg-3 py-lg-2 shadow-sm sticky-top hm-navbar">
  <div class="container-fluid">
    <a class="navbar-brand me-5 fw-bold fs-3" href="index.php">
      <span class="hm-brand-main">Hostel Management</span>
      <span class="hm-brand-sub">Smart Stay Portal</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?php echo $currentPage === 'index.php' ? ' is-active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'rooms.php' ? ' is-active' : ''; ?>" href="rooms.php">Rooms</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'facilities.php' ? ' is-active' : ''; ?>" href="facilities.php">Facilities</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'contact.php' ? ' is-active' : ''; ?>" href="contact.php">Contact Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'about.php' ? ' is-active' : ''; ?>" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'student-support.php' ? ' is-active' : ''; ?>" href="student-support.php">RMS Status</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'leave-request.php' ? ' is-active' : ''; ?>" href="leave-request.php">Leave Request</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2<?php echo $currentPage === 'gate-pass.php' ? ' is-active' : ''; ?>" href="gate-pass.php">Gate Pass QR</a>
        </li>
      </ul>
      <div class="d-flex flex-wrap hm-actions">
        <a class="btn btn-sm btn-outline-dark hm-btn-admin" href="../backend/admin-login.php?return=<?php echo $currentPageUrl; ?>">Admin Login</a>
        <?php if (is_array($activeUser) && (string)($activeUser['role'] ?? '') === 'admin'): ?>
          <a class="btn btn-sm btn-outline-dark hm-btn-dashboard" href="../backend/admin-dashboard.php">Admin Dashboard</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- ============================================================ -->



