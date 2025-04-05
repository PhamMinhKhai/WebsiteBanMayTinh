<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: loginRegister.html");
    exit();
}

// Get user information from session
$userFullname = isset($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : 'User';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'user@example.com';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <script src="script.js" defer></script>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <title>TechHub - My Account</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      }

      body {
        background-color: #f5f5f5;
      }

      .navbar {
        background-color: #1a237e;
        color: white;
        padding: 1rem 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      }

      .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .logo {
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
      }

      .logo span {
        color: #64b5f6;
      }

      .nav-links {
        list-style: none;
        display: flex;
        gap: 1.5rem;
      }

      .nav-links li {
        position: relative;
      }

      .nav-links a {
        color: white;
        text-decoration: none;
        font-size: 1rem;
        padding: 0.5rem 0;
        position: relative;
        transition: color 0.3s;
      }

      .nav-links a:hover {
        color: #64b5f6;
      }

      .nav-links a::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #64b5f6;
        transition: width 0.3s;
      }

      .nav-links a:hover::after {
        width: 100%;
      }

      .dropdown {
        position: relative;
      }

      .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: white;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        min-width: 200px;
        z-index: 1;
        border-radius: 4px;
        padding: 0.5rem 0;
      }

      .dropdown:hover .dropdown-content {
        display: block;
      }

      .dropdown-content a {
        color: #333;
        padding: 0.5rem 1rem;
        display: block;
        text-decoration: none;
        transition: background-color 0.3s, color 0.3s;
      }

      .dropdown-content a:hover {
        background-color: #e3f2fd;
        color: #1a237e;
      }

      .dropdown-content a::after {
        display: none;
      }

      .icons {
        display: flex;
        gap: 1.5rem;
        align-items: center;
      }

      .icon {
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: color 0.3s;
        position: relative;
      }

      .icon:hover {
        color: #64b5f6;
      }

      .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #f44336;
        color: white;
        font-size: 0.7rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .mobile-toggle {
        display: none;
        cursor: pointer;
        font-size: 1.5rem;
      }

      .nav-active {
        transform: translateX(0) !important;
      }

      /* Auth CTA Section Styles */
      .auth-cta-section {
        background-color: #f5f7ff;
        padding: 4rem 0;
        margin: 3rem 0;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      }

      .auth-cta-wrapper {
        display: flex;
        align-items: center;
        max-width: 1100px;
        margin: 0 auto;
        gap: 3rem;
      }

      .auth-cta-image {
        flex: 0 0 45%;
      }

      .auth-cta-image img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(74, 108, 247, 0.2);
      }

      .auth-cta-content {
        flex: 0 0 55%;
      }

      .auth-cta-content h2 {
        font-size: 2.2rem;
        color: #1a237e;
        margin-bottom: 1.2rem;
        line-height: 1.3;
      }

      .auth-cta-content p {
        color: #555;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 2rem;
      }

      .auth-cta-buttons {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
      }

      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.9rem 1.8rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .btn i {
        margin-right: 8px;
      }

      .btn-primary {
        background-color: #4a6cf7;
        color: white;
        border: 2px solid #4a6cf7;
      }

      .btn-primary:hover {
        background-color: #3a5bd9;
        border-color: #3a5bd9;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(74, 108, 247, 0.25);
      }

      .btn-outline {
        background-color: transparent;
        color: #4a6cf7;
        border: 2px solid #4a6cf7;
      }

      .btn-outline:hover {
        background-color: #f0f3ff;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(74, 108, 247, 0.15);
      }

      .auth-cta-benefits {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
      }

      .benefit-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .benefit-item i {
        font-size: 1.8rem;
        color: #4a6cf7;
        margin-bottom: 0.5rem;
      }

      .benefit-item span {
        font-size: 0.95rem;
        font-weight: 500;
        color: #444;
      }

      @media (max-width: 992px) {
        .auth-cta-wrapper {
          flex-direction: column;
          padding: 0 2rem;
        }

        .auth-cta-image,
        .auth-cta-content {
          flex: 0 0 100%;
        }

        .auth-cta-content h2 {
          font-size: 1.8rem;
        }
      }

      @media (max-width: 576px) {
        .auth-cta-buttons {
          flex-direction: column;
        }

        .auth-cta-benefits {
          flex-wrap: wrap;
          gap: 1.5rem;
          justify-content: center;
        }

        .benefit-item {
          width: 100%;
          max-width: 120px;
        }
      }

      /* Search Bar Styles */
      .search-container {
        position: relative;
        margin-right: 10px;
      }

      .search-bar {
        display: flex;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.15);
        border-radius: 4px;
        padding: 6px 12px;
        transition: all 0.3s ease;
      }

      .search-bar:focus-within {
        background-color: white;
      }

      .search-input {
        background: transparent;
        border: none;
        outline: none;
        color: white;
        width: 0;
        padding: 0;
        transition: all 0.3s ease;
      }

      .search-bar:focus-within .search-input {
        width: 200px;
        padding: 0 8px;
        color: #333;
      }

      .search-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .search-bar:focus-within .search-btn {
        color: #1a237e;
      }

      /* Account Page Styles */
      .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
      }

      .page-title {
        font-size: 2rem;
        color: #1a237e;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e0e0e0;
      }

      .account-wrapper {
        display: flex;
        gap: 2rem;
      }

      .account-sidebar {
        flex: 0 0 250px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
      }

      .user-info {
        text-align: center;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e0e0e0;
      }

      .user-avatar {
        width: 80px;
        height: 80px;
        background-color: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1rem;
      }

      .user-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
      }

      .user-email {
        font-size: 0.9rem;
        color: #666;
      }

      .sidebar-menu {
        list-style: none;
      }

      .sidebar-menu li {
        margin-bottom: 0.5rem;
      }

      .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 0.8rem 1rem;
        border-radius: 6px;
        color: #555;
        text-decoration: none;
        transition: all 0.3s;
      }

      .sidebar-menu a:hover {
        background-color: #f0f3ff;
        color: #4a6cf7;
      }

      .sidebar-menu a.active {
        background-color: #4a6cf7;
        color: white;
      }

      .sidebar-icon {
        margin-right: 0.8rem;
        font-size: 1.2rem;
      }

      .account-content {
        flex: 1;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 2rem;
      }

      .tab-content {
        display: none;
      }

      .tab-content.active {
        display: block;
      }

      .section-title {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 1.5rem;
      }

      .account-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
      }

      .account-card {
        background-color: #f8f9ff;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
      }

      .account-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
      }

      .card-title {
        display: flex;
        align-items: center;
        font-size: 1rem;
        color: #555;
        margin-bottom: 1rem;
      }

      .card-title span {
        font-size: 1.5rem;
        margin-right: 0.8rem;
      }

      .card-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a237e;
      }

      .order-list {
        width: 100%;
        border-collapse: collapse;
      }

      .order-list th,
      .order-list td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
      }

      .order-list th {
        font-weight: 600;
        color: #555;
        background-color: #f5f5f5;
      }

      .order-list tr:last-child td {
        border-bottom: none;
      }

      .order-status {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
      }

      .status-completed {
        background-color: #e8f5e9;
        color: #2e7d32;
      }

      .status-processing {
        background-color: #e3f2fd;
        color: #1565c0;
      }

      .status-pending {
        background-color: #fff8e1;
        color: #f57f17;
      }

      .status-cancelled {
        background-color: #ffebee;
        color: #c62828;
      }

      .btn-view {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        background-color: #f0f3ff;
        color: #4a6cf7;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
      }

      .btn-view:hover {
        background-color: #4a6cf7;
        color: white;
      }

      /* Responsive styles for account page */
      @media (max-width: 992px) {
        .account-wrapper {
          flex-direction: column;
        }

        .account-sidebar {
          flex: 0 0 100%;
        }
      }

      @media (max-width: 768px) {
        .account-cards {
          grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .order-list {
          font-size: 0.9rem;
        }

        .order-list th,
        .order-list td {
          padding: 0.8rem 0.5rem;
        }
      }

      @media (max-width: 576px) {
        .container {
          padding: 1rem;
        }

        .account-content {
          padding: 1.5rem 1rem;
        }

        .order-list {
          display: block;
          overflow-x: auto;
          white-space: nowrap;
        }
      }

      /* Notification Panel Styles */
      .notification-panel {
        position: fixed;
        top: 70px;
        right: -350px;
        width: 350px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        transition: right 0.3s ease;
        max-height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
      }

      .notification-panel.active {
        right: 20px;
      }

      .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
      }

      .notification-header h3 {
        font-size: 1.1rem;
        color: #333;
        margin: 0;
      }

      .mark-all-read {
        background: none;
        border: none;
        color: #4a6cf7;
        font-size: 0.9rem;
        cursor: pointer;
      }

      .notification-list {
        overflow-y: auto;
        padding: 10px 0;
        flex-grow: 1;
      }

      .notification-item {
        display: flex;
        padding: 12px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
      }

      .notification-item:hover {
        background-color: #f9f9f9;
      }

      .notification-item.unread {
        background-color: #f0f7ff;
      }

      .notification-item.unread:hover {
        background-color: #e3f0ff;
      }

      .notification-content {
        flex-grow: 1;
      }

      .notification-content h4 {
        font-size: 0.95rem;
        margin: 0 0 5px 0;
        color: #333;
      }

      .notification-content p {
        font-size: 0.85rem;
        color: #666;
        margin: 0 0 5px 0;
      }

      .notification-time {
        font-size: 0.75rem;
        color: #999;
      }

      .notification-delete {
        background: none;
        border: none;
        color: #ccc;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0 0 0 10px;
        align-self: flex-start;
      }

      .notification-delete:hover {
        color: #f44336;
      }

      .notification-settings {
        padding: 12px 20px;
        border-top: 1px solid #eee;
        text-align: center;
      }

      .notification-settings a {
        color: #666;
        font-size: 0.85rem;
        text-decoration: none;
      }

      .notification-settings a:hover {
        color: #4a6cf7;
        text-decoration: underline;
      }

      .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
      }

      .overlay.active {
        display: block;
      }

      /* Modal Styles */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1001;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
      }

      .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .modal-content {
        background-color: white;
        margin: auto;
        width: 90%;
        max-width: 450px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        animation: modalFadeIn 0.3s;
      }

      @keyframes modalFadeIn {
        from {
          opacity: 0;
          transform: translateY(-20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eee;
      }

      .modal-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.2rem;
      }

      .close-modal {
        font-size: 1.5rem;
        font-weight: 700;
        color: #aaa;
        cursor: pointer;
        transition: color 0.3s;
      }

      .close-modal:hover {
        color: #333;
      }

      .modal-body {
        padding: 1.5rem;
      }

      .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
      }
    </style>
  </head>
  <body>
    <!-- Navigation Bar -->
    <nav class="navbar">
      <div class="navbar-container">
        <a href="/index.html" class="logo">Tech<span>Hub</span></a>
        <div class="mobile-toggle" id="mobile-toggle">☰</div>

        <!-- Navigation Links -->
        <ul class="nav-links" id="nav-links">
          <li><a href="/index.html">Home</a></li>
          <li class="dropdown">
            <a href="computers.html">Computers ▾</a>
            <div class="dropdown-content">
              <a href="/Desktop.html">Desktop PCs</a>
              <a href="laptops.html">Laptops</a>
              <a href="gaming.html">Gaming PCs</a>
              <a href="workstations.html">Workstations</a>
              <a href="mini-pcs.html">Mini PCs</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="components.html">Components ▾</a>
            <div class="dropdown-content">
              <a href="/Processors.html">Processors</a>
              <a href="motherboards.html">Motherboards</a>
              <a href="graphics-cards.html">Graphics Cards</a>
              <a href="memory.html">Memory (RAM)</a>
              <a href="storage.html">Storage</a>
              <a href="power-supplies.html">Power Supplies</a>
              <a href="cooling.html">Cooling</a>
              <a href="cases.html">Cases</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="peripherals.html">Peripherals ▾</a>
            <div class="dropdown-content">
              <a href="/Monitors.html">Monitors</a>
              <a href="keyboards.html">Keyboards</a>
              <a href="mice.html">Mice</a>
              <a href="headsets.html">Headsets</a>
              <a href="speakers.html">Speakers</a>
            </div>
          </li>
          <li><a href="deals.html">Deals</a></li>
          <li><a href="support.html">Support</a></li>
        </ul>

        <!-- Icons for Search, Notifications, Account, and Cart -->
        <div class="icons">
          <div class="search-container">
            <div class="search-bar">
              <input
                type="text"
                class="search-input"
                id="search-input"
                placeholder="Search products..."
                aria-label="Search"
              />
              <button class="search-btn" id="search-btn">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                  width="24"
                  height="24"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </div>
            <div class="search-results" id="search-results"></div>
          </div>
          <div class="icon notification-icon" id="notification-icon">
            <i class="fas fa-bell"></i>
            <span class="notification-count">2</span>
          </div>
          <div class="icon" id="account-icon">
            <a href="account.php" style="color: inherit; text-decoration: none">
              <i class="fas fa-user"></i>
            </a>
          </div>
          <div class="icon" id="cart-icon">
            <a href="cart.html" style="color: inherit; text-decoration: none">
              <i class="fas fa-shopping-cart"></i>
              <span class="cart-count">3</span>
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Notification Panel -->
    <div class="notification-panel" id="notification-panel">
      <div class="notification-header">
        <h3>Thông báo</h3>
        <button class="mark-all-read" id="mark-all-read">
          Đánh dấu tất cả đã đọc
        </button>
      </div>
      <div class="notification-list" id="notification-list">
        <div class="notification-item unread">
          <div class="notification-content">
            <h4>Khuyến mãi đặc biệt</h4>
            <p>Giảm giá 15% cho tất cả laptop trong tuần này!</p>
            <span class="notification-time">2 giờ trước</span>
          </div>
          <button class="notification-delete">×</button>
        </div>
        <div class="notification-item unread">
          <div class="notification-content">
            <h4>Sản phẩm đã về kho</h4>
            <p>RTX 4080 đã có hàng trở lại. Nhanh tay đặt ngay!</p>
            <span class="notification-time">1 ngày trước</span>
          </div>
          <button class="notification-delete">×</button>
        </div>
        <div class="notification-item">
          <div class="notification-content">
            <h4>Đơn hàng #12345 đã giao</h4>
            <p>Đơn hàng của bạn đã được giao thành công.</p>
            <span class="notification-time">3 ngày trước</span>
          </div>
          <button class="notification-delete">×</button>
        </div>
      </div>
      <div class="notification-settings">
        <a href="notification-settings.html">Cài đặt thông báo</a>
      </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <!-- Logout Confirmation Modal -->
    <div class="modal" id="logout-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Confirm Logout</h3>
          <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to log out?</p>
        </div>
        <div class="modal-footer">
          <button class="btn-outline" id="cancel-logout">Cancel</button>
          <button class="btn-primary" id="confirm-logout">Logout</button>
        </div>
      </div>
    </div>

    <div class="container">
      <h1 class="page-title">My Account</h1>

      <div class="account-wrapper">
        <div class="account-sidebar">
          <div class="user-info">
            <div class="user-avatar">👤</div>
            <div class="user-name"><?php echo htmlspecialchars($userFullname); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($userEmail); ?></div>
          </div>

          <ul class="sidebar-menu">
            <li>
              <a href="#" class="active" data-tab="dashboard">
                <span class="sidebar-icon">📊</span>
                Dashboard
              </a>
            </li>
            <li>
              <a href="#" data-tab="orders">
                <span class="sidebar-icon">📦</span>
                Orders
              </a>
            </li>
            <li>
              <a href="#" data-tab="wishlist">
                <span class="sidebar-icon">❤️</span>
                Wishlist
              </a>
            </li>
            <li>
              <a href="#" data-tab="addresses">
                <span class="sidebar-icon">📍</span>
                Addresses
              </a>
            </li>
            <li>
              <a href="#" data-tab="profile">
                <span class="sidebar-icon">👤</span>
                Profile Details
              </a>
            </li>
            <li>
              <a href="#" data-tab="security">
                <span class="sidebar-icon">🔒</span>
                Security
              </a>
            </li>
            <li>
              <a href="#" id="logout-link">
                <span class="sidebar-icon">🚪</span>
                Logout
              </a>
            </li>
          </ul>
        </div>

        <div class="account-content">
          <!-- Dashboard Tab -->
          <div class="tab-content active" id="dashboard">
            <h2 class="section-title">Dashboard</h2>

            <div class="account-cards">
              <div class="account-card">
                <div class="card-title">
                  <span>📦</span>
                  Total Orders
                </div>
                <div class="card-value">8</div>
              </div>

              <div class="account-card">
                <div class="card-title">
                  <span>⭐</span>
                  Reward Points
                </div>
                <div class="card-value">560</div>
              </div>

              <div class="account-card">
                <div class="card-title">
                  <span>💰</span>
                  Store Credit
                </div>
                <div class="card-value">$25.00</div>
              </div>
            </div>

            <h3 class="section-title">Recent Orders</h3>

            <table class="order-list">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>#TH80945</td>
                  <td>Feb 18, 2023</td>
                  <td>
                    <span class="order-status status-completed">Completed</span>
                  </td>
                  <td>$1,249.99</td>
                  <td><a href="#" class="btn-view">View</a></td>
                </tr>
                <tr>
                  <td>#TH80832</td>
                  <td>Jan 25, 2023</td>
                  <td>
                    <span class="order-status status-processing">Processing</span>
                  </td>
                  <td>$349.50</td>
                  <td><a href="#" class="btn-view">View</a></td>
                </tr>
                <tr>
                  <td>#TH80791</td>
                  <td>Jan 12, 2023</td>
                  <td>
                    <span class="order-status status-completed">Completed</span>
                  </td>
                  <td>$89.99</td>
                  <td><a href="#" class="btn-view">View</a></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Orders Tab -->
          <div class="tab-content" id="orders">
            <h2 class="section-title">My Orders</h2>
            <!-- Orders content here -->
          </div>

          <!-- Wishlist Tab -->
          <div class="tab-content" id="wishlist">
            <h2 class="section-title">My Wishlist</h2>
            <!-- Wishlist content here -->
          </div>

          <!-- Addresses Tab -->
          <div class="tab-content" id="addresses">
            <h2 class="section-title">My Addresses</h2>
            <!-- Addresses content here -->
          </div>

          <!-- Profile Tab -->
          <div class="tab-content" id="profile">
            <h2 class="section-title">Profile Details</h2>
            <!-- Profile content here -->
          </div>

          <!-- Security Tab -->
          <div class="tab-content" id="security">
            <h2 class="section-title">Security Settings</h2>
            <!-- Security content here -->
          </div>
        </div>
      </div>
    </div>

    <script>
      // Tab switching functionality
      document.addEventListener("DOMContentLoaded", function () {
        // Get shared elements
        const overlay = document.getElementById("overlay");

        // Logout functionality
        const logoutLink = document.getElementById("logout-link");
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogout = document.getElementById("cancel-logout");
        const confirmLogout = document.getElementById("confirm-logout");
        const closeModal = document.querySelector(".close-modal");

        // Show logout confirmation modal
        logoutLink.addEventListener("click", function(e) {
          e.preventDefault();
          logoutModal.classList.add("active");
          overlay.classList.add("active");
        });

        // Hide modal when cancel is clicked
        cancelLogout.addEventListener("click", function() {
          logoutModal.classList.remove("active");
          overlay.classList.remove("active");
        });

        // Hide modal when X is clicked
        closeModal.addEventListener("click", function() {
          logoutModal.classList.remove("active");
          overlay.classList.remove("active");
        });

        // Proceed with logout when confirmed
        confirmLogout.addEventListener("click", function() {
          window.location.href = "logout.php";
        });

        // Tab switching functionality
        const tabLinks = document.querySelectorAll(".sidebar-menu a[data-tab]");
        const tabContents = document.querySelectorAll(".tab-content");

        tabLinks.forEach((link) => {
          link.addEventListener("click", function (e) {
            e.preventDefault();

            // Remove active class from all links and tabs
            tabLinks.forEach((l) => l.classList.remove("active"));
            tabContents.forEach((t) => t.classList.remove("active"));

            // Add active class to clicked link
            this.classList.add("active");

            // Show corresponding tab content
            const tabId = this.getAttribute("data-tab");
            document.getElementById(tabId).classList.add("active");
          });
        });

        // Notification panel toggle
        const notificationIcon = document.getElementById("notification-icon");
        const notificationPanel = document.getElementById("notification-panel");

        notificationIcon.addEventListener("click", function () {
          notificationPanel.classList.toggle("active");
          overlay.classList.toggle("active");

          // Hide logout modal when notification panel is opened
          logoutModal.classList.remove("active");
        });

        // Close all modals when overlay is clicked
        overlay.addEventListener("click", function () {
          notificationPanel.classList.remove("active");
          logoutModal.classList.remove("active");
          overlay.classList.remove("active");
        });

        // Mark all notifications as read
        const markAllReadBtn = document.getElementById("mark-all-read");
        markAllReadBtn.addEventListener("click", function () {
          const unreadNotifications = document.querySelectorAll(
            ".notification-item.unread"
          );
          unreadNotifications.forEach((notification) => {
            notification.classList.remove("unread");
          });
          document.querySelector(".notification-count").textContent = "0";
        });

        // Delete notification
        const deleteButtons = document.querySelectorAll(".notification-delete");
        deleteButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const notification = this.closest(".notification-item");
            notification.style.height = "0";
            notification.style.opacity = "0";
            notification.style.padding = "0";
            notification.style.margin = "0";
            notification.style.overflow = "hidden";
            notification.style.transition = "all 0.3s ease";

            setTimeout(() => {
              notification.remove();
              updateNotificationCount();
            }, 300);
          });
        });

        function updateNotificationCount() {
          const unreadCount = document.querySelectorAll(
            ".notification-item.unread"
          ).length;
          document.querySelector(".notification-count").textContent = unreadCount;
        }
      });
    </script>
  </body>
</html>