<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session check for "Remember Me" functionality
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TechHub - Computers & Components</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <!-- Navigation Bar -->
    <nav class="navbar">
      <div class="navbar-container">
        <a href="index.php" class="logo">Tech<span>Hub</span></a>
        <div class="mobile-toggle" id="mobile-toggle">☰</div>

        <!-- Navigation Links -->
        <ul class="nav-links" id="nav-links">
          <li><a href="index.php">Home</a></li>
          <li class="dropdown">
            <a href="computers.html">Computers ▾</a>
            <div class="dropdown-content">
              <a href="Desktop.html">Desktop PCs</a>
              <a href="laptops.html">Laptops</a>
              <a href="gaming.html">Gaming PCs</a>
              <a href="workstations.html">Workstations</a>
              <a href="mini-pcs.html">Mini PCs</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="components.html">Components ▾</a>
            <div class="dropdown-content">
              <a href="processors.html">Processors</a>
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
              <a href="Monitors.html">Monitors</a>
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
            <a href="<?php echo isset($_SESSION['logged_in']) ? 'account.php' : 'loginRegister.html'; ?>" style="color: inherit; text-decoration: none">
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

    <!-- Overlay for Modal or Sidebar -->
    <div class="overlay" id="overlay"></div>

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

    <!-- Carousel Section -->
    <div class="carousel-container">
      <div class="carousel-wrapper">
        <div class="carousel-slides">
          <div class="carousel-slide active">
            <div class="carousel-content">
              <h2>Powerful Gaming PCs</h2>
              <p>Experience next-level gaming with our custom-built gaming PCs</p>
              <a href="gaming.html" class="btn-primary">Shop Now</a>
            </div>
            <img src="/api/placeholder/1200/500" alt="Gaming PCs" />
          </div>
          <div class="carousel-slide">
            <div class="carousel-content">
              <h2>Professional Workstations</h2>
              <p>High-performance workstations for creative professionals</p>
              <a href="workstations.html" class="btn-primary">Explore</a>
            </div>
            <img src="/api/placeholder/1200/500" alt="Workstations" />
          </div>
          <div class="carousel-slide">
            <div class="carousel-content">
              <h2>Latest Components</h2>
              <p>Upgrade your system with the newest hardware</p>
              <a href="components.html" class="btn-primary">Upgrade Now</a>
            </div>
            <img src="/api/placeholder/1200/500" alt="Components" />
          </div>
        </div>
        <div class="carousel-controls">
          <button class="carousel-prev" id="carousel-prev">❮</button>
          <div class="carousel-dots">
            <span class="carousel-dot active" data-slide="0"></span>
            <span class="carousel-dot" data-slide="1"></span>
            <span class="carousel-dot" data-slide="2"></span>
          </div>
          <button class="carousel-next" id="carousel-next">❯</button>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <section class="categories-section">
      <div class="container">
        <h2 class="section-title">Browse Categories</h2>
        <div class="categories-grid">
          <a href="gaming.html" class="category-card">
            <div class="category-icon">🎮</div>
            <h3>Gaming PCs</h3>
            <p>High-performance systems for gamers</p>
          </a>
          <a href="laptops.html" class="category-card">
            <div class="category-icon">💻</div>
            <h3>Laptops</h3>
            <p>Portable computing solutions</p>
          </a>
          <a href="processors.html" class="category-card">
            <div class="category-icon">⚙️</div>
            <h3>Processors</h3>
            <p>CPUs from leading manufacturers</p>
          </a>
          <a href="graphics-cards.html" class="category-card">
            <div class="category-icon">🖥️</div>
            <h3>Graphics Cards</h3>
            <p>Latest GPUs for gaming and design</p>
          </a>
          <a href="storage.html" class="category-card">
            <div class="category-icon">💾</div>
            <h3>Storage</h3>
            <p>SSDs, HDDs, and external drives</p>
          </a>
          <a href="peripherals.html" class="category-card">
            <div class="category-icon">🖱️</div>
            <h3>Peripherals</h3>
            <p>Keyboards, mice, and accessories</p>
          </a>
        </div>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="products-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Featured Products</h2>
          <a href="products.html" class="view-all">View All</a>
        </div>
        <div class="products-grid">
          <!-- Product Card 1 -->
          <div class="product-card">
            <div class="product-badge sale-badge">Sale</div>
            <div class="product-image">
              <img src="/api/placeholder/240/180" alt="AMD Ryzen 9 7950X" />
              <div class="product-actions">
                <button class="quick-view" title="Quick View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="add-to-wishlist" title="Add to Wishlist">
                  <i class="fas fa-heart"></i>
                </button>
                <button class="add-to-compare" title="Compare">
                  <i class="fas fa-exchange-alt"></i>
                </button>
              </div>
            </div>
            <div class="product-info">
              <h3 class="product-title">
                <a href="product-detail.html">AMD Ryzen 9 7950X</a>
              </h3>
              <div class="product-category">Processors</div>
              <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span class="rating-count">(128)</span>
              </div>
              <div class="product-price">
                <span class="current-price">$549.99</span>
                <span class="old-price">$699.99</span>
              </div>
              <button class="add-to-cart-btn">
                <i class="fas fa-shopping-cart"></i> Add to Cart
              </button>
            </div>
          </div>

          <!-- Product Card 2 -->
          <div class="product-card">
            <div class="product-badge new-badge">New</div>
            <div class="product-image">
              <img src="/api/placeholder/240/180" alt="ASUS ROG Strix X870-E" />
              <div class="product-actions">
                <button class="quick-view" title="Quick View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="add-to-wishlist" title="Add to Wishlist">
                  <i class="fas fa-heart"></i>
                </button>
                <button class="add-to-compare" title="Compare">
                  <i class="fas fa-exchange-alt"></i>
                </button>
              </div>
            </div>
            <div class="product-info">
              <h3 class="product-title">
                <a href="product-detail.html">ASUS ROG Strix X870-E Gaming</a>
              </h3>
              <div class="product-category">Motherboards</div>
              <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <span class="rating-count">(64)</span>
              </div>
              <div class="product-price">
                <span class="current-price">$429.99</span>
              </div>
              <button class="add-to-cart-btn">
                <i class="fas fa-shopping-cart"></i> Add to Cart
              </button>
            </div>
          </div>

          <!-- Product Card 3 -->
          <div class="product-card">
            <div class="product-image">
              <img
                src="/api/placeholder/240/180"
                alt="NVIDIA GeForce RTX 4080"
              />
              <div class="product-actions">
                <button class="quick-view" title="Quick View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="add-to-wishlist" title="Add to Wishlist">
                  <i class="fas fa-heart"></i>
                </button>
                <button class="add-to-compare" title="Compare">
                  <i class="fas fa-exchange-alt"></i>
                </button>
              </div>
            </div>
            <div class="product-info">
              <h3 class="product-title">
                <a href="product-detail.html">NVIDIA GeForce RTX 4080 16GB</a>
              </h3>
              <div class="product-category">Graphics Cards</div>
              <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="far fa-star"></i>
                <span class="rating-count">(96)</span>
              </div>
              <div class="product-price">
                <span class="current-price">$1,199.99</span>
              </div>
              <button class="add-to-cart-btn out-of-stock" disabled>
                Out of Stock
              </button>
            </div>
          </div>

          <!-- Product Card 4 -->
          <div class="product-card">
            <div class="product-badge sale-badge">Sale</div>
            <div class="product-image">
              <img
                src="/api/placeholder/240/180"
                alt="Corsair Vengeance RGB Pro 32GB"
              />
              <div class="product-actions">
                <button class="quick-view" title="Quick View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="add-to-wishlist" title="Add to Wishlist">
                  <i class="fas fa-heart"></i>
                </button>
                <button class="add-to-compare" title="Compare">
                  <i class="fas fa-exchange-alt"></i>
                </button>
              </div>
            </div>
            <div class="product-info">
              <h3 class="product-title">
                <a href="product-detail.html"
                  >Corsair Vengeance RGB Pro 32GB (2x16GB) DDR5</a
                >
              </h3>
              <div class="product-category">Memory</div>
              <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span class="rating-count">(112)</span>
              </div>
              <div class="product-price">
                <span class="current-price">$149.99</span>
                <span class="old-price">$189.99</span>
              </div>
              <button class="add-to-cart-btn">
                <i class="fas fa-shopping-cart"></i> Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Deals Section -->
    <section class="deals-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Hot Deals</h2>
          <a href="deals.html" class="view-all">View All Deals</a>
        </div>
        <div class="deals-grid">
          <!-- Deal Card 1 -->
          <div class="deal-card">
            <div class="deal-image">
              <img
                src="/api/placeholder/300/200"
                alt="Gaming PC Bundle Deal"
              />
              <div class="deal-countdown" data-countdown="2023-12-31T23:59:59">
                <div class="countdown-item">
                  <span class="countdown-value days">00</span>
                  <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value hours">00</span>
                  <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value minutes">00</span>
                  <span class="countdown-label">Mins</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value seconds">00</span>
                  <span class="countdown-label">Secs</span>
                </div>
              </div>
            </div>
            <div class="deal-info">
              <h3 class="deal-title">Gaming PC Bundle Deal</h3>
              <p class="deal-description">
                RTX 4070 Gaming PC with 27" Monitor, Mechanical Keyboard & Mouse
              </p>
              <div class="deal-price">
                <span class="current-price">$1,899.99</span>
                <span class="old-price">$2,499.99</span>
                <span class="discount-badge">-24%</span>
              </div>
              <div class="deal-progress">
                <div class="progress-bar">
                  <div class="progress-fill" style="width: 75%"></div>
                </div>
                <div class="progress-text">
                  <span class="sold">75 Sold</span>
                  <span class="available">25 Available</span>
                </div>
              </div>
              <button class="deal-btn">Shop Now</button>
            </div>
          </div>

          <!-- Deal Card 2 -->
          <div class="deal-card">
            <div class="deal-image">
              <img
                src="/api/placeholder/300/200"
                alt="SSD & RAM Upgrade Kit"
              />
              <div class="deal-countdown" data-countdown="2023-12-25T23:59:59">
                <div class="countdown-item">
                  <span class="countdown-value days">00</span>
                  <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value hours">00</span>
                  <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value minutes">00</span>
                  <span class="countdown-label">Mins</span>
                </div>
                <div class="countdown-item">
                  <span class="countdown-value seconds">00</span>
                  <span class="countdown-label">Secs</span>
                </div>
              </div>
            </div>
            <div class="deal-info">
              <h3 class="deal-title">SSD & RAM Upgrade Kit</h3>
              <p class="deal-description">
                1TB NVMe SSD + 32GB DDR5 RAM Bundle - Perfect Upgrade Combo
              </p>
              <div class="deal-price">
                <span class="current-price">$199.99</span>
                <span class="old-price">$349.99</span>
                <span class="discount-badge">-43%</span>
              </div>
              <div class="deal-progress">
                <div class="progress-bar">
                  <div class="progress-fill" style="width: 60%"></div>
                </div>
                <div class="progress-text">
                  <span class="sold">60 Sold</span>
                  <span class="available">40 Available</span>
                </div>
              </div>
              <button class="deal-btn">Shop Now</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Brands Section -->
    <section class="brands-section">
      <div class="container">
        <h2 class="section-title">Popular Brands</h2>
        <div class="brands-slider">
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="ASUS" />
          </div>
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="MSI" />
          </div>
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="NVIDIA" />
          </div>
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="AMD" />
          </div>
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="Intel" />
          </div>
          <div class="brand-item">
            <img src="/api/placeholder/150/60" alt="Corsair" />
          </div>
        </div>
      </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
      <div class="container">
        <div class="newsletter-content">
          <h2>Subscribe to Our Newsletter</h2>
          <p>
            Get the latest updates on new products, special offers, and tech
            news.
          </p>
          <form class="newsletter-form">
            <input
              type="email"
              placeholder="Enter your email address"
              required
            />
            <button type="submit">Subscribe</button>
          </form>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-top">
          <div class="footer-column">
            <h3 class="footer-title">Shop</h3>
            <ul class="footer-links">
              <li><a href="computers.html">Computers</a></li>
              <li><a href="components.html">Components</a></li>
              <li><a href="peripherals.html">Peripherals</a></li>
              <li><a href="networking.html">Networking</a></li>
              <li><a href="software.html">Software</a></li>
            </ul>
          </div>
          <div class="footer-column">
            <h3 class="footer-title">Customer Service</h3>
            <ul class="footer-links">
              <li><a href="contact.html">Contact Us</a></li>
              <li><a href="faq.html">FAQs</a></li>
              <li><a href="shipping.html">Shipping & Delivery</a></li>
              <li><a href="returns.html">Returns & Exchanges</a></li>
              <li><a href="warranty.html">Warranty Information</a></li>
            </ul>
          </div>
          <div class="footer-column">
            <h3 class="footer-title">About Us</h3>
            <ul class="footer-links">
              <li><a href="about.html">Our Story</a></li>
              <li><a href="blog.html">Blog</a></li>
              <li><a href="careers.html">Careers</a></li>
              <li><a href="press.html">Press Releases</a></li>
              <li><a href="privacy.html">Privacy Policy</a></li>
            </ul>
          </div>
          <div class="footer-column">
            <h3 class="footer-title">Contact</h3>
            <ul class="footer-contact">
              <li>
                <i class="fas fa-map-marker-alt"></i> 123 Tech Street, Silicon
                Valley, CA 94043
              </li>
              <li><i class="fas fa-phone"></i> (800) 123-4567</li>
              <li><i class="fas fa-envelope"></i> support@techhub.com</li>
            </ul>
            <div class="social-links">
              <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
              <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
              <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <div class="copyright">
            &copy; 2023 TechHub. All rights reserved.
          </div>
          <div class="payment-methods">
            <i class="fab fa-cc-visa"></i>
            <i class="fab fa-cc-mastercard"></i>
            <i class="fab fa-cc-amex"></i>
            <i class="fab fa-cc-paypal"></i>
            <i class="fab fa-cc-discover"></i>
          </div>
        </div>
      </div>
    </footer>

    <script src="script.js"></script>
  </body>
</html>