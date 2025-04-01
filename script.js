// Utility Modules
const DOM = {
  getEl: (selector, parent = document) => parent.querySelector(selector),
  getAllEl: (selector, parent = document) => parent.querySelectorAll(selector),
  toggleClass: (el, className) => el && el.classList.toggle(className),
  addClass: (el, className) => el && el.classList.add(className),
  removeClass: (el, className) => el && el.classList.remove(className),
  on: (el, event, callback, options = {}) =>
    el && el.addEventListener(event, callback, options),
  trigger: (el, eventType) => {
    if (el && typeof eventType === "string") {
      const event = new Event(eventType, { bubbles: true });
      el.dispatchEvent(event);
    }
  },
};

const Utils = {
  debounce: (func, delay) => {
    let timeout;
    return function (...args) {
      const context = this;
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(context, args), delay);
    };
  },
  escapeRegex: (string) => string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"),
};

// Constants
const CLASS_NAMES = {
  ACTIVE: "active",
  SHOW: "show",
  NAV_ACTIVE: "nav-active",
  UNREAD: "unread",
  REMOVING: "removing",
  HIGHLIGHT: "highlight",
};

const SELECTORS = {
  DROPDOWN: ".dropdown",
  TAB_LINK: ".sidebar-menu a",
  TAB_CONTENT: ".tab-content",
  SEARCH_CONTAINER: ".search-container",
  NOTIFICATION_DELETE: ".notification-delete",
  NOTIFICATION_ITEM: ".notification-item",
  CART_ITEM: ".cart-item",
  CART_ITEM_REMOVE: ".cart-item-remove",
  CART_ITEM_QUANTITY: ".cart-item-quantity input",
  CART_ITEM_PRICE: ".cart-item-price",
  CART_TOTAL: ".cart-total",
  NOTIFICATION_COUNT: ".notification-count",
  CAROUSEL_SLIDE: ".carousel-slide",
  CAROUSEL_INDICATOR: ".carousel-indicator",
  CAROUSEL_BTN_PREV: ".carousel-btn-prev",
  CAROUSEL_BTN_NEXT: ".carousel-btn-next",
  CAROUSEL_WRAPPER: ".carousel-wrapper",
  QUICK_VIEW: ".quick-view",
  PRODUCT_CARD: ".product-card",
  PRODUCT_NAME: ".product-name",
  CURRENT_PRICE: ".current-price",
  ADD_TO_WISHLIST: ".add-to-wishlist",
  ADD_TO_CART_BTN: ".add-to-cart-btn",
  CART_COUNT: ".cart-count",
};

// Element Cache - populate on init()
const ELEMENTS = {};

// Data Store
const STORE = {
  products: [
    {
      id: 1,
      name: "Intel Core i9-13900K",
      category: "Processors",
      url: "processors.html#i9-13900k",
    },
    {
      id: 2,
      name: "AMD Ryzen 9 7950X",
      category: "Processors",
      url: "processors.html#ryzen-7950x",
    },
    {
      id: 3,
      name: "NVIDIA RTX 4090",
      category: "Graphics Cards",
      url: "graphics-cards.html#rtx-4090",
    },
    {
      id: 4,
      name: "AMD Radeon RX 7900 XTX",
      category: "Graphics Cards",
      url: "graphics-cards.html#rx-7900-xtx",
    },
    {
      id: 5,
      name: "Samsung 990 Pro SSD",
      category: "Storage",
      url: "storage.html#samsung-990-pro",
    },
    {
      id: 6,
      name: "ASUS ROG Swift PG32UQX",
      category: "Monitors",
      url: "monitors.html#pg32uqx",
    },
    {
      id: 7,
      name: "Corsair Vengeance RGB DDR5",
      category: "Memory (RAM)",
      url: "memory.html#corsair-vengeance-ddr5",
    },
    {
      id: 8,
      name: "NZXT H7 Flow",
      category: "Cases",
      url: "cases.html#nzxt-h7-flow",
    },
    {
      id: 9,
      name: "MSI MAG Z790 Tomahawk",
      category: "Motherboards",
      url: "motherboards.html#msi-z790-tomahawk",
    },
    {
      id: 10,
      name: "ASUS ROG Strix G15 Gaming Laptop",
      category: "Laptops",
      url: "laptops.html#rog-strix-g15",
    },
  ],
};

// Application Modules
const App = {
  init() {
    this.cacheElements();
    this.initModules();
    this.initBackToTop();
    this.initAuthForms();
  },

  cacheElements() {
    // Navigation elements
    ELEMENTS.mobileToggle = DOM.getEl("#mobile-toggle");
    ELEMENTS.navLinks = DOM.getEl("#nav-links");
    ELEMENTS.overlay = DOM.getEl("#overlay");
    ELEMENTS.dropdowns = DOM.getAllEl(SELECTORS.DROPDOWN);
    ELEMENTS.tabLinks = DOM.getAllEl(SELECTORS.TAB_LINK);
    ELEMENTS.tabContents = DOM.getAllEl(SELECTORS.TAB_CONTENT);

    // Account
    ELEMENTS.accountIcon = DOM.getEl("#account-icon");

    // Search
    ELEMENTS.searchInput = DOM.getEl("#search-input");
    ELEMENTS.searchBtn = DOM.getEl("#search-btn");
    ELEMENTS.searchResults = DOM.getEl("#search-results");

    // Notifications
    ELEMENTS.notificationIcon = DOM.getEl("#notification-icon");
    ELEMENTS.notificationPanel = DOM.getEl("#notification-panel");
    ELEMENTS.notificationList = DOM.getEl("#notification-list");
    ELEMENTS.markAllReadBtn = DOM.getEl("#mark-all-read");

    // Cart
    ELEMENTS.cartContainer = DOM.getEl(".cart-container");
    ELEMENTS.emptyCartMessage = DOM.getEl(".empty-cart-message");
    ELEMENTS.cartSummary = DOM.getEl(".cart-summary");
    ELEMENTS.checkoutButton = DOM.getEl(".checkout-btn");

    // Back to Top
    ELEMENTS.backToTopButton = DOM.getEl("#back-to-top");

    // Auth forms
    ELEMENTS.container = DOM.getEl(".container");
    ELEMENTS.registerBtn = DOM.getEl(".register-btn");
    ELEMENTS.loginBtn = DOM.getEl(".login-btn");
    ELEMENTS.loginForm = DOM.getEl("#login-form");
    ELEMENTS.registerForm = DOM.getEl("#register-form");
  },

  initModules() {
    Navigation.init();
    Account.init();
    Search.init();
    Notifications.init();
    Cart.init();
    Carousel.init();
    ProductInteraction.init();
  },

  initBackToTop() {
    const { backToTopButton } = ELEMENTS;
    if (!backToTopButton) return;

    // Show/hide back to top button based on scroll position
    DOM.on(window, "scroll", () => {
      if (window.pageYOffset > 300) {
        DOM.addClass(backToTopButton, CLASS_NAMES.SHOW);
      } else {
        DOM.removeClass(backToTopButton, CLASS_NAMES.SHOW);
      }
    });

    // Scroll to top when button is clicked
    DOM.on(backToTopButton, "click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  },

  initAuthForms() {
    const { container, registerBtn, loginBtn, loginForm, registerForm } =
      ELEMENTS;
    if (!container || !registerBtn || !loginBtn || !loginForm || !registerForm)
      return;

    // Toggle between login and register views
    DOM.on(registerBtn, "click", () => {
      DOM.addClass(container, CLASS_NAMES.ACTIVE);
    });

    DOM.on(loginBtn, "click", () => {
      DOM.removeClass(container, CLASS_NAMES.ACTIVE);
    });

    // Form submission handling
    DOM.on(loginForm, "submit", (event) => {
      event.preventDefault();
      const username = DOM.getEl("#login-username").value;
      const password = DOM.getEl("#login-password").value;

      // Here you would typically send the data to your server
      console.log("Login attempt:", { username, password });

      // For demo purposes only - replace with actual authentication
      alert("Login successful!");
    });

    DOM.on(registerForm, "submit", (event) => {
      event.preventDefault();
      const username = DOM.getEl("#register-username").value;
      const email = DOM.getEl("#register-email").value;
      const password = DOM.getEl("#register-password").value;

      // Here you would typically send the data to your server
      console.log("Registration attempt:", { username, email, password });

      // For demo purposes only - replace with actual registration
      alert("Registration successful!");

      // Optionally switch back to login view after registration
      DOM.removeClass(container, CLASS_NAMES.ACTIVE);
    });
  },
};

const Navigation = {
  init() {
    this.setupMobileNav();
    this.setupDropdowns();
    this.setupTabs();
  },

  setupMobileNav() {
    const { mobileToggle, navLinks, overlay } = ELEMENTS;
    if (!mobileToggle || !navLinks || !overlay) return;

    DOM.on(mobileToggle, "click", () => {
      DOM.toggleClass(navLinks, CLASS_NAMES.NAV_ACTIVE);
      DOM.toggleClass(overlay, CLASS_NAMES.ACTIVE);
    });

    DOM.on(overlay, "click", () => {
      DOM.removeClass(navLinks, CLASS_NAMES.NAV_ACTIVE);
      DOM.removeClass(overlay, CLASS_NAMES.ACTIVE);
      DOM.removeClass(ELEMENTS.notificationPanel, CLASS_NAMES.SHOW);
      DOM.removeClass(ELEMENTS.searchResults, CLASS_NAMES.ACTIVE);
    });
  },

  setupDropdowns() {
    const { dropdowns } = ELEMENTS;
    if (!dropdowns || !dropdowns.length) return;

    if (window.innerWidth <= 768) {
      dropdowns.forEach((dropdown) => {
        const link = DOM.getEl("a", dropdown);
        DOM.on(link, "click", (e) => {
          e.preventDefault();
          DOM.toggleClass(dropdown, CLASS_NAMES.ACTIVE);
          dropdowns.forEach((otherDropdown) => {
            if (otherDropdown !== dropdown) {
              DOM.removeClass(otherDropdown, CLASS_NAMES.ACTIVE);
            }
          });
        });
      });
    }
  },

  setupTabs() {
    const { tabLinks, tabContents } = ELEMENTS;
    if (!tabLinks || !tabContents) return;

    tabLinks.forEach((link) => {
      DOM.on(link, "click", (e) => {
        e.preventDefault();
        const targetTab = link.getAttribute("data-tab");

        tabLinks.forEach((l) => DOM.removeClass(l, CLASS_NAMES.ACTIVE));
        tabContents.forEach((content) =>
          DOM.removeClass(content, CLASS_NAMES.ACTIVE)
        );

        DOM.addClass(link, CLASS_NAMES.ACTIVE);
        DOM.addClass(DOM.getEl(`#${targetTab}`), CLASS_NAMES.ACTIVE);
      });
    });
  },
};

const Account = {
  init() {
    const { accountIcon } = ELEMENTS;
    if (!accountIcon) return;

    DOM.on(accountIcon, "click", () => {
      window.location.href = "account.html";
    });
  },
};

const Search = {
  init() {
    const { searchInput, searchBtn, searchResults } = ELEMENTS;
    if (!searchInput || !searchBtn || !searchResults) return;

    const debouncedSearch = Utils.debounce(this.performSearch.bind(this), 300);

    DOM.on(searchInput, "input", debouncedSearch);
    DOM.on(searchBtn, "click", (e) => {
      e.preventDefault();
      this.performSearch();
    });

    DOM.on(searchInput, "focus", () => {
      if (searchInput.value.trim().length >= 2) {
        this.performSearch();
      }
    });

    DOM.on(searchInput, "keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        this.performSearch();
      }
    });

    // Hide search results when clicking outside
    DOM.on(document, "click", (e) => {
      if (!e.target.closest(SELECTORS.SEARCH_CONTAINER)) {
        DOM.removeClass(searchResults, CLASS_NAMES.ACTIVE);
      }
    });
  },

  highlightMatch(text, query) {
    const regex = new RegExp(`(${Utils.escapeRegex(query)})`, "gi");
    return text.replace(
      regex,
      `<span class="${CLASS_NAMES.HIGHLIGHT}">$1</span>`
    );
  },

  performSearch() {
    const { searchInput, searchResults } = ELEMENTS;
    if (!searchInput || !searchResults) return;

    const query = searchInput.value.trim().toLowerCase();

    if (query.length < 2) {
      DOM.removeClass(searchResults, CLASS_NAMES.ACTIVE);
      return;
    }

    const matches = STORE.products.filter(
      (product) =>
        product.name.toLowerCase().includes(query) ||
        product.category.toLowerCase().includes(query)
    );

    searchResults.innerHTML =
      matches.length > 0
        ? matches
            .map(
              (product) => `
                <div class="search-result-item">
                    <a href="${product.url}">
                        <div class="result-title">${this.highlightMatch(
                          product.name,
                          query
                        )}</div>
                        <div class="result-category">${this.highlightMatch(
                          product.category,
                          query
                        )}</div>
                    </a>
                </div>
            `
            )
            .join("")
        : '<div class="search-result-item">No products found</div>';

    DOM.addClass(searchResults, CLASS_NAMES.ACTIVE);
  },
};

const Notifications = {
  init() {
    const {
      notificationIcon,
      notificationPanel,
      notificationList,
      markAllReadBtn,
      overlay,
    } = ELEMENTS;

    if (
      !notificationIcon ||
      !notificationPanel ||
      !notificationList ||
      !markAllReadBtn ||
      !overlay
    ) {
      return;
    }

    DOM.on(notificationIcon, "click", () => {
      DOM.toggleClass(notificationPanel, CLASS_NAMES.SHOW);
      DOM.toggleClass(overlay, CLASS_NAMES.ACTIVE);
    });

    DOM.on(markAllReadBtn, "click", () => {
      DOM.getAllEl(`.${CLASS_NAMES.UNREAD}`, notificationList).forEach(
        (item) => {
          DOM.removeClass(item, CLASS_NAMES.UNREAD);
        }
      );
      this.updateNotificationCount();
    });

    DOM.on(notificationList, "click", (e) => {
      if (
        e.target.classList.contains(SELECTORS.NOTIFICATION_DELETE.substring(1))
      ) {
        e.stopPropagation();
        const notificationItem = e.target.closest(SELECTORS.NOTIFICATION_ITEM);
        DOM.addClass(notificationItem, CLASS_NAMES.REMOVING);
        setTimeout(() => {
          notificationItem.remove();
          this.updateNotificationCount();
        }, 300);
      }
    });

    this.updateNotificationCount();
  },

  updateNotificationCount() {
    const { notificationList } = ELEMENTS;
    if (!notificationList) return;

    const unreadCount = DOM.getAllEl(
      `.${CLASS_NAMES.UNREAD}`,
      notificationList
    ).length;

    const notificationCount = DOM.getEl(SELECTORS.NOTIFICATION_COUNT);
    if (notificationCount) {
      notificationCount.textContent = unreadCount;
      notificationCount.style.display = unreadCount > 0 ? "flex" : "none";
    }
  },
};

const Cart = {
  init() {
    const { cartContainer, emptyCartMessage, cartSummary, checkoutButton } =
      ELEMENTS;
    if (!cartContainer || !emptyCartMessage || !cartSummary || !checkoutButton)
      return;

    DOM.on(cartContainer, "click", (e) => {
      if (
        e.target.classList.contains(SELECTORS.CART_ITEM_REMOVE.substring(1))
      ) {
        e.target.closest(SELECTORS.CART_ITEM).remove();
        this.updateCartTotal();
      }
    });

    DOM.on(cartContainer, "change", (e) => {
      if (
        e.target.classList.contains(SELECTORS.CART_ITEM_QUANTITY.substring(1))
      ) {
        this.updateCartTotal();
      }
    });

    DOM.on(checkoutButton, "click", () => {
      alert("Checkout page is under construction");
    });

    this.updateCartTotal();
  },

  updateCartTotal() {
    const { emptyCartMessage, cartSummary } = ELEMENTS;
    if (!emptyCartMessage || !cartSummary) return;

    let total = 0;
    const cartItems = DOM.getAllEl(SELECTORS.CART_ITEM);

    cartItems.forEach((item) => {
      const priceText =
        DOM.getEl(SELECTORS.CART_ITEM_PRICE, item)?.textContent || "0";
      const price = parseFloat(priceText.replace("$", "")) || 0;
      const quantityInput = DOM.getEl(SELECTORS.CART_ITEM_QUANTITY, item);
      const quantity = quantityInput
        ? parseInt(quantityInput.value, 10) || 0
        : 0;
      total += price * quantity;
    });

    const totalElement = DOM.getEl(SELECTORS.CART_TOTAL);
    if (totalElement) {
      totalElement.textContent = `Total: $${total.toFixed(2)}`;
    }

    emptyCartMessage.style.display = cartItems.length === 0 ? "block" : "none";
    cartSummary.style.display = cartItems.length === 0 ? "none" : "flex";
  },
};

const Carousel = {
  currentIndex: 0,
  interval: null,
  slides: [],
  indicators: [],
  prevBtn: null,
  nextBtn: null,
  carouselWrapper: null,

  init() {
    this.slides = DOM.getAllEl(SELECTORS.CAROUSEL_SLIDE);
    this.indicators = DOM.getAllEl(SELECTORS.CAROUSEL_INDICATOR);
    this.prevBtn = DOM.getEl(SELECTORS.CAROUSEL_BTN_PREV);
    this.nextBtn = DOM.getEl(SELECTORS.CAROUSEL_BTN_NEXT);
    this.carouselWrapper = DOM.getEl(SELECTORS.CAROUSEL_WRAPPER);

    if (
      !this.slides.length ||
      !this.indicators.length ||
      !this.prevBtn ||
      !this.nextBtn ||
      !this.carouselWrapper
    ) {
      return;
    }

    this.changeSlide(this.currentIndex); // Initial slide
    this.addEventListeners();
    this.startAutoSlide();
  },

  changeSlide(index) {
    DOM.removeClass(this.slides[this.currentIndex], CLASS_NAMES.ACTIVE);
    DOM.removeClass(this.indicators[this.currentIndex], CLASS_NAMES.ACTIVE);

    this.currentIndex = (index + this.slides.length) % this.slides.length;

    DOM.addClass(this.slides[this.currentIndex], CLASS_NAMES.ACTIVE);
    DOM.addClass(this.indicators[this.currentIndex], CLASS_NAMES.ACTIVE);
  },

  startAutoSlide() {
    this.stopAutoSlide(); // Clear any existing interval
    this.interval = setInterval(
      () => this.changeSlide(this.currentIndex + 1),
      5000
    );
  },

  stopAutoSlide() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },

  addEventListeners() {
    DOM.on(this.prevBtn, "click", () => {
      this.stopAutoSlide();
      this.changeSlide(this.currentIndex - 1);
      this.startAutoSlide();
    });

    DOM.on(this.nextBtn, "click", () => {
      this.stopAutoSlide();
      this.changeSlide(this.currentIndex + 1);
      this.startAutoSlide();
    });

    this.indicators.forEach((indicator, index) => {
      DOM.on(indicator, "click", () => {
        this.stopAutoSlide();
        this.changeSlide(index);
        this.startAutoSlide();
      });
    });

    DOM.on(this.carouselWrapper, "mouseenter", () => this.stopAutoSlide());
    DOM.on(this.carouselWrapper, "mouseleave", () => this.startAutoSlide());
  },
};

const ProductInteraction = {
  toast: null,

  init() {
    this.setupQuickView();
    this.setupWishlist();
    this.setupAddToCart();
    this.setupToast();
  },

  setupQuickView() {
    const quickViewButtons = DOM.getAllEl(SELECTORS.QUICK_VIEW);
    if (!quickViewButtons.length) return;

    quickViewButtons.forEach((button) => {
      DOM.on(button, "click", (e) => {
        e.preventDefault();
        const productName = DOM.getEl(
          SELECTORS.PRODUCT_NAME,
          button.closest(SELECTORS.PRODUCT_CARD)
        ).textContent;
        alert(`Quick view for ${productName} will open in a modal window`);
        // Open modal logic here
      });
    });
  },

  setupWishlist() {
    const wishlistButtons = DOM.getAllEl(SELECTORS.ADD_TO_WISHLIST);
    if (!wishlistButtons.length) return;

    wishlistButtons.forEach((button) => {
      DOM.on(button, "click", (e) => {
        e.preventDefault();
        DOM.toggleClass(button, CLASS_NAMES.ACTIVE);
        const message = button.classList.contains(CLASS_NAMES.ACTIVE)
          ? "Product added to your wishlist!"
          : "Product removed from your wishlist!";
        this.showToast(message);
        button.innerHTML = button.classList.contains(CLASS_NAMES.ACTIVE)
          ? '<i class="fas fa-heart" style="color: #ff5252;"></i>'
          : '<i class="fas fa-heart"></i>';
      });
    });
  },

  setupAddToCart() {
    const addToCartButtons = DOM.getAllEl(SELECTORS.ADD_TO_CART_BTN);
    if (!addToCartButtons.length) return;

    addToCartButtons.forEach((button) => {
      DOM.on(button, "click", () => {
        const productCard = button.closest(SELECTORS.PRODUCT_CARD);
        const productName = DOM.getEl(
          SELECTORS.PRODUCT_NAME,
          productCard
        ).textContent;

        // Update cart count
        const cartCount = DOM.getEl(SELECTORS.CART_COUNT);
        if (cartCount) {
          cartCount.textContent = parseInt(cartCount.textContent) + 1;
        }

        // Show feedback
        this.showToast(`${productName} added to your cart!`);
        // Add to cart logic
      });
    });
  },

  setupToast() {
    this.toast = DOM.getEl("#toast-notification");
    if (!this.toast) {
      this.toast = document.createElement("div");
      this.toast.id = "toast-notification";
      document.body.appendChild(this.toast);
      Object.assign(this.toast.style, {
        position: "fixed",
        bottom: "20px",
        right: "20px",
        background: "#1a237e",
        color: "white",
        padding: "12px 20px",
        borderRadius: "4px",
        boxShadow: "0 4px 8px rgba(0,0,0,0.2)",
        zIndex: "1000",
        transition: "all 0.3s ease",
        opacity: "0",
        transform: "translateY(20px)",
      });
    }
  },

  showToast(message) {
    if (!this.toast) return;

    this.toast.textContent = message;
    this.toast.style.opacity = "1";
    this.toast.style.transform = "translateY(0)";

    // Hide toast after 3 seconds
    setTimeout(() => {
      this.toast.style.opacity = "0";
      this.toast.style.transform = "translateY(20px)";
    }, 3000);
  },
};

// Initialize the application when DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => App.init());
