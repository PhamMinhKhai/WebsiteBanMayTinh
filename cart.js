// TechHub Cart and Checkout Functionality

// Cart data structure
let cart = {
  items: [],
  subtotal: 0,
  shipping: 0,
  tax: 0,
  total: 0,
};

// Load cart from localStorage on page load
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM content loaded, initializing cart");
  loadCart();
  window.updateCartDisplay();
  window.updateCartCount();
  setupEventListeners();

  // Log cart state for debugging
  console.log("Cart initialized:", cart);
});

// Load cart data from localStorage
window.loadCart = function () {
  console.log("Loading cart from localStorage");
  const savedCart = localStorage.getItem("techHubCart");
  if (savedCart) {
    try {
      cart = JSON.parse(savedCart);
      console.log("Cart loaded successfully:", cart);
    } catch (e) {
      console.error("Error parsing cart data:", e);
      cart = { items: [], subtotal: 0, shipping: 0, tax: 0, total: 0 };
    }
  } else {
    console.log("No cart found in localStorage, initializing empty cart");
    cart = { items: [], subtotal: 0, shipping: 0, tax: 0, total: 0 };
  }
};

// Save cart data to localStorage
function saveCart() {
  localStorage.setItem("techHubCart", JSON.stringify(cart));
  window.updateCartCount();
}

// Update cart count in the header
window.updateCartCount = function () {
  const cartCountElements = document.querySelectorAll(".cart-count");
  const itemCount = cart.items.reduce(
    (total, item) => total + item.quantity,
    0
  );

  cartCountElements.forEach((element) => {
    element.textContent = itemCount;

    // Show/hide based on whether there are items
    if (itemCount > 0) {
      element.style.display = "inline-block";
    } else {
      element.style.display = "none";
    }
  });
};

// Add item to cart
window.addToCart = function (product) {
  console.log("Adding to cart:", product);

  // Validate product data
  if (!product.id || !product.name || isNaN(product.price)) {
    console.error("Invalid product data:", product);
    return;
  }

  // Prepare data for API call
  const formData = new FormData();
  formData.append("product_id", product.id);
  formData.append("product_name", product.name);
  formData.append("price", product.price);
  formData.append("image_url", product.image || "");
  formData.append("specs", product.specs || "");
  formData.append("quantity", product.quantity || 1);

  // Send data to server
  fetch("add_to_cart.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Update cart count in the header
        const cartCountElements = document.querySelectorAll(".cart-count");
        cartCountElements.forEach((element) => {
          element.textContent = data.cart_count;
          element.style.display = data.cart_count > 0 ? "inline-block" : "none";
        });

        // Show success notification
        showAddToCartConfirmation(product.name);
      } else {
        console.error("Failed to add item to cart:", data.message);
        alert("Failed to add item to cart: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error adding to cart:", error);
      alert(
        "An error occurred while adding the item to cart. Please try again."
      );
    });
};

// Show confirmation when item is added to cart
function showAddToCartConfirmation(productName) {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = "add-to-cart-notification";
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-check-circle"></i>
            <p>${productName} has been added to your cart</p>
            <a href="cart.html" class="view-cart-btn">View Cart</a>
        </div>
        <button class="close-notification">&times;</button>
    `;

  // Add to document
  document.body.appendChild(notification);

  // Remove after 5 seconds
  setTimeout(() => {
    notification.classList.add("fade-out");
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 500);
  }, 5000);

  // Close button functionality
  const closeBtn = notification.querySelector(".close-notification");
  closeBtn.addEventListener("click", () => {
    document.body.removeChild(notification);
  });
}

// Calculate cart totals
function calculateCartTotals() {
  console.log("Calculating cart totals for items:", cart.items);

  // Calculate subtotal
  cart.subtotal = cart.items.reduce((total, item) => {
    const itemTotal = item.price * item.quantity;
    console.log(
      `Item ${item.name}: ${item.price} × ${item.quantity} = ${itemTotal}`
    );
    return total + itemTotal;
  }, 0);

  // Round to 2 decimal places to avoid floating point issues
  cart.subtotal = Math.round(cart.subtotal * 100) / 100;
  console.log("Calculated subtotal:", cart.subtotal);

  // Calculate shipping (free over $50, otherwise $5.99)
  cart.shipping = cart.subtotal > 50 ? 0 : 5.99;
  console.log("Calculated shipping:", cart.shipping);

  // Calculate tax (estimated at 6%)
  cart.tax = Math.round(cart.subtotal * 0.06 * 100) / 100;
  console.log("Calculated tax:", cart.tax);

  // Calculate total
  cart.total = cart.subtotal + cart.shipping + cart.tax;
  console.log("Calculated total:", cart.total);
}

// Update cart display on cart page
window.updateCartDisplay = function () {
  // Only run on cart page
  const cartItemsContainer = document.getElementById("cart-items");
  const emptyCartMessage = document.querySelector(".empty-cart-message");
  const cartSummary = document.querySelector(".cart-summary");

  if (!cartItemsContainer) {
    console.log("Cart items container not found, not on cart page");
    return;
  }

  // Fetch cart items from server
  fetch("get_cart_items.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Clear current items
        cartItemsContainer.innerHTML = "";

        // Check if cart is empty
        if (data.items.length === 0) {
          if (emptyCartMessage) emptyCartMessage.style.display = "block";
          if (cartSummary) cartSummary.style.display = "none";
          return;
        }

        // Show cart summary, hide empty message
        if (emptyCartMessage) emptyCartMessage.style.display = "none";
        if (cartSummary) cartSummary.style.display = "block";

        // Add each item to the cart display
        data.items.forEach((item, index) => {
          const cartItemElement = document.createElement("div");
          cartItemElement.className = "cart-item";
          cartItemElement.innerHTML = `
            <div class="cart-item-image">
              <img src="${item.image || "/api/placeholder/240/180"}" alt="${
            item.name
          }" />
            </div>
            <div class="cart-item-details">
              <div class="cart-item-title">${item.name}</div>
              <div class="cart-item-specs">${item.specs || ""}</div>
              <div class="cart-item-price">$${item.price.toFixed(2)}</div>
              <div class="cart-item-quantity">
                <label for="quantity${index}">Quantity:</label>
                <div class="quantity-controls">
                  <button class="quantity-btn decrease" data-item-id="${
                    item.id
                  }">-</button>
                  <input type="number" id="quantity${index}" class="quantity-input"
                    value="${item.quantity}" min="1" max="10" data-item-id="${
            item.id
          }" />
                  <button class="quantity-btn increase" data-item-id="${
                    item.id
                  }">+</button>
                </div>
              </div>
            </div>
            <button class="cart-item-remove" data-item-id="${item.id}">
              <i class="fas fa-trash-alt"></i>
            </button>
          `;

          cartItemsContainer.appendChild(cartItemElement);
        });

        // Update summary values
        document.getElementById(
          "subtotal"
        ).textContent = `$${data.summary.subtotal.toFixed(2)}`;
        document.getElementById("shipping").textContent =
          data.summary.shipping === 0
            ? "Free"
            : `$${data.summary.shipping.toFixed(2)}`;
        document.getElementById(
          "tax"
        ).textContent = `$${data.summary.tax.toFixed(2)}`;
        document.getElementById(
          "total"
        ).textContent = `$${data.summary.total.toFixed(2)}`;

        // Add event listeners to new elements
        setupCartItemEventListeners();
      } else {
        console.error("Failed to fetch cart items:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error fetching cart items:", error);
    });
};

// Update the cart summary section
window.updateCartSummaryDisplay = function () {
  console.log("Updating cart summary display with values:", {
    subtotal: cart.subtotal,
    shipping: cart.shipping,
    tax: cart.tax,
    total: cart.total,
  });

  // Recalculate totals to ensure they're correct
  calculateCartTotals();

  const subtotalElement = document.getElementById("subtotal");
  const shippingElement = document.getElementById("shipping");
  const taxElement = document.getElementById("tax");
  const totalElement = document.getElementById("total");

  // Also check for checkout page elements
  const checkoutSubtotalElement = document.getElementById("checkout-subtotal");
  const checkoutShippingElement = document.getElementById("checkout-shipping");
  const checkoutTaxElement = document.getElementById("checkout-tax");
  const checkoutTotalElement = document.getElementById("checkout-total");

  // Format currency
  const formatCurrency = (amount) => {
    return (
      "$" +
      parseFloat(amount)
        .toFixed(2)
        .replace(/\d(?=(\d{3})+\.)/g, "$&,")
    );
  };

  // Update cart page elements
  if (subtotalElement) {
    subtotalElement.textContent = formatCurrency(cart.subtotal);
    console.log("Updated subtotal element:", subtotalElement.textContent);
  }

  if (shippingElement) {
    shippingElement.textContent =
      cart.shipping === 0 ? "Free" : formatCurrency(cart.shipping);
    console.log("Updated shipping element:", shippingElement.textContent);
  }

  if (taxElement) {
    taxElement.textContent = formatCurrency(cart.tax);
    console.log("Updated tax element:", taxElement.textContent);
  }

  if (totalElement) {
    totalElement.textContent = formatCurrency(cart.total);
    console.log("Updated total element:", totalElement.textContent);
  }

  // Update checkout page elements
  if (checkoutSubtotalElement)
    checkoutSubtotalElement.textContent = formatCurrency(cart.subtotal);
  if (checkoutShippingElement)
    checkoutShippingElement.textContent =
      cart.shipping === 0 ? "Free" : formatCurrency(cart.shipping);
  if (checkoutTaxElement)
    checkoutTaxElement.textContent = formatCurrency(cart.tax);
  if (checkoutTotalElement)
    checkoutTotalElement.textContent = formatCurrency(cart.total);
};

// Set up event listeners for cart items
function setupCartItemEventListeners() {
  // Quantity decrease buttons
  document.querySelectorAll(".quantity-btn.decrease").forEach((button) => {
    button.addEventListener("click", function () {
      const itemId = this.getAttribute("data-item-id");
      updateItemQuantity(itemId, -1);
    });
  });

  // Quantity increase buttons
  document.querySelectorAll(".quantity-btn.increase").forEach((button) => {
    button.addEventListener("click", function () {
      const itemId = this.getAttribute("data-item-id");
      updateItemQuantity(itemId, 1);
    });
  });

  // Quantity input fields
  document.querySelectorAll(".quantity-input").forEach((input) => {
    input.addEventListener("change", function () {
      const itemId = this.getAttribute("data-item-id");
      const newQuantity = parseInt(this.value);
      setItemQuantity(itemId, newQuantity);
    });
  });

  // Remove item buttons
  document.querySelectorAll(".cart-item-remove").forEach((button) => {
    button.addEventListener("click", function () {
      const itemId = this.getAttribute("data-item-id");
      removeItemFromCart(itemId);
    });
  });
}

// Update item quantity by a delta amount
function updateItemQuantity(itemId, delta) {
  // Find the input field to get the current quantity
  const inputField = document.querySelector(
    `.quantity-input[data-item-id="${itemId}"]`
  );
  if (!inputField) {
    console.error("Quantity input field not found for item", itemId);
    return;
  }
  const currentQuantity = parseInt(inputField.value);
  const newQuantity = currentQuantity + delta;

  // Call setItemQuantity to handle validation and API call
  setItemQuantity(itemId, newQuantity);
}

// Set item quantity to a specific value and update server
function setItemQuantity(itemId, quantity) {
  // Validate quantity (client-side)
  quantity = Math.max(0, Math.min(99, quantity)); // Allow 0 for removal via quantity change

  console.log(`Setting quantity for item ${itemId} to ${quantity}`);

  // Prepare data for API call
  const formData = new FormData();
  formData.append("item_id", itemId);
  formData.append("quantity", quantity);

  // Send update request to server
  fetch("update_cart_quantity.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Update quantity response:", data);
      if (data.success) {
        // Reload the cart display to show changes and updated totals
        window.updateCartDisplay();
        // Update cart count in header
        if (typeof data.cart_count !== "undefined") {
          updateCartCountInHeader(data.cart_count);
        }
      } else {
        console.error("Failed to update quantity:", data.message);
        alert("Failed to update quantity: " + data.message);
        // Optionally revert UI change if needed, but updateCartDisplay handles it
      }
    })
    .catch((error) => {
      console.error("Error updating quantity:", error);
      alert("An error occurred while updating the quantity.");
      // Optionally revert UI change if needed
    });
}

// Remove item from cart via API
function removeItemFromCart(itemId) {
  console.log(`Removing item ${itemId} from cart`);

  // Confirm before removing (optional but good UX)
  if (!confirm("Are you sure you want to remove this item from your cart?")) {
    return;
  }

  // Prepare data for API call
  const formData = new FormData();
  formData.append("item_id", itemId);

  // Send remove request to server
  fetch("remove_from_cart.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Remove item response:", data);
      if (data.success) {
        // Reload the cart display
        window.updateCartDisplay();
        // Update cart count in header
        if (typeof data.cart_count !== "undefined") {
          updateCartCountInHeader(data.cart_count);
        }
      } else {
        console.error("Failed to remove item:", data.message);
        alert("Failed to remove item: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error removing item:", error);
      alert("An error occurred while removing the item.");
    });
}

// Helper function to update cart count in header specifically
function updateCartCountInHeader(count) {
  const cartCountElements = document.querySelectorAll(".cart-count");
  cartCountElements.forEach((element) => {
    element.textContent = count;
    element.style.display = count > 0 ? "inline-block" : "none";
  });
}

// Set up general event listeners
function setupEventListeners() {
  // Checkout button
  const checkoutBtn = document.getElementById("checkout-btn");
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", function (e) {
      // If the button contains an anchor tag, let it handle navigation
      if (!e.target.closest("a")) {
        e.preventDefault();
        window.location.href = "Checkout.html";
      }
    });
  }

  // Place order button
  const placeOrderBtn = document.getElementById("place-order-btn");
  if (placeOrderBtn) {
    placeOrderBtn.addEventListener("click", function (e) {
      e.preventDefault();
      processOrder();
    });
  }

  // Check if we're on the cart page and initialize if needed
  if (window.location.pathname.includes("cart.html")) {
    console.log("On cart page, ensuring cart is initialized");
    window.loadCart();
    window.updateCartDisplay();
    window.updateCartCount();
  }

  // Add to cart buttons on product pages
  document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const productCard = this.closest(".product-card");
      if (!productCard) {
        console.error("Product card not found");
        return;
      }

      // Get product ID
      const productId = productCard.getAttribute("data-product-id");
      if (!productId) {
        console.error("Product ID not found");
        return;
      }

      // Get product title
      const titleElement =
        productCard.querySelector(".product-title") ||
        productCard.querySelector(".product-name");
      if (!titleElement) {
        console.error("Product title element not found");
        return;
      }

      // Get product price - try different selectors
      let priceElement = productCard.querySelector(".current-price");
      if (!priceElement) {
        priceElement = productCard.querySelector(".product-price");
      }
      if (!priceElement) {
        console.error("Product price element not found");
        return;
      }

      // Get product image
      const imageElement = productCard.querySelector(".product-image img");
      if (!imageElement) {
        console.error("Product image element not found");
        return;
      }

      // Extract price correctly
      const priceText = priceElement.textContent.trim();
      const priceMatch = priceText.match(/[\d,.]+/);
      if (!priceMatch) {
        console.error("Could not extract price from:", priceText);
        return;
      }

      const price = parseFloat(priceMatch[0].replace(/,/g, ""));
      if (isNaN(price)) {
        console.error("Invalid price:", priceMatch[0]);
        return;
      }

      // Create product object
      const product = {
        id: productId,
        name: titleElement.textContent.trim(),
        price: price,
        image: imageElement.src,
        specs: productCard.querySelector(".product-specs")
          ? productCard.querySelector(".product-specs").textContent.trim()
          : "",
        quantity: 1,
      };

      console.log("Adding product to cart:", product);
      addToCart(product);
    });
  });
}

// Process the order
function processOrder() {
  // Get form data
  const firstName = document.getElementById("firstname").value;
  const lastName = document.getElementById("lastname").value;
  const email = document.getElementById("email").value;
  const phone = document.getElementById("phone").value;
  const address = document.getElementById("address").value;
  const city = document.getElementById("city").value;
  const state = document.getElementById("state").value;
  const country = document.getElementById("country").value;

  // Validate form
  if (
    !firstName ||
    !lastName ||
    !email ||
    !phone ||
    !address ||
    !city ||
    !state ||
    !country
  ) {
    alert("Please fill in all required fields.");
    return;
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    return;
  }

  // Phone validation
  const phoneRegex = /^\d{10,15}$/;
  if (!phoneRegex.test(phone.replace(/[^0-9]/g, ""))) {
    alert("Please enter a valid phone number.");
    return;
  }

  // Create order object
  const orderNumber = generateOrderNumber();
  const order = {
    customer: {
      firstName,
      lastName,
      email,
      phone,
      address,
      city,
      state,
      country,
    },
    items: cart.items,
    subtotal: cart.subtotal,
    shipping: cart.shipping,
    tax: cart.tax,
    total: cart.total,
    orderDate: new Date().toISOString(),
    orderNumber: orderNumber,
  };

  // Show loading indicator
  showLoadingOverlay("Processing your order...");

  // Save order to database via AJAX
  fetch("save_order.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(order),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoadingOverlay();

      if (data.success) {
        // Save order to localStorage as backup
        const orders = JSON.parse(
          localStorage.getItem("techHubOrders") || "[]"
        );
        orders.push(order);
        localStorage.setItem("techHubOrders", JSON.stringify(orders));

        // Clear cart
        cart = {
          items: [],
          subtotal: 0,
          shipping: 0,
          tax: 0,
          total: 0,
        };
        saveCart();

        // Show success message
        showOrderConfirmation(orderNumber);
      } else {
        // Show error message
        alert("Error saving order: " + data.message);
      }
    })
    .catch((error) => {
      hideLoadingOverlay();
      console.error("Error:", error);
      alert("An error occurred while processing your order. Please try again.");
    });
}

// Show loading overlay
function showLoadingOverlay(message) {
  // Create overlay if it doesn't exist
  let overlay = document.getElementById("loading-overlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "loading-overlay";
    overlay.innerHTML = `
            <div class="loading-spinner"></div>
            <div class="loading-message">${message || "Loading..."}</div>
        `;
    document.body.appendChild(overlay);

    // Add styles
    const style = document.createElement("style");
    style.textContent = `
            #loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .loading-spinner {
                border: 5px solid #f3f3f3;
                border-top: 5px solid #1a237e;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin-bottom: 20px;
            }
            .loading-message {
                color: white;
                font-size: 18px;
                text-align: center;
                max-width: 80%;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
    document.head.appendChild(style);
  } else {
    overlay.querySelector(".loading-message").textContent =
      message || "Loading...";
    overlay.style.display = "flex";
  }
}

// Hide loading overlay
function hideLoadingOverlay() {
  const overlay = document.getElementById("loading-overlay");
  if (overlay) {
    overlay.style.display = "none";
  }
}

// Show order confirmation
function showOrderConfirmation(orderNumber) {
  // Create confirmation modal
  const modal = document.createElement("div");
  modal.className = "order-confirmation-modal";
  modal.innerHTML = `
        <div class="order-confirmation-content">
            <div class="order-confirmation-header">
                <i class="fas fa-check-circle"></i>
                <h2>Order Placed Successfully!</h2>
            </div>
            <div class="order-confirmation-body">
                <p>Thank you for your purchase!</p>
                <p>Your order number is: <strong>${orderNumber}</strong></p>
                <p>We've sent a confirmation email with your order details.</p>
            </div>
            <div class="order-confirmation-footer">
                <button class="continue-shopping-btn">Continue Shopping</button>
                <button class="view-order-btn">View Order</button>
            </div>
        </div>
    `;

  document.body.appendChild(modal);

  // Add styles
  const style = document.createElement("style");
  style.textContent = `
        .order-confirmation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .order-confirmation-content {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .order-confirmation-header {
            margin-bottom: 20px;
        }
        .order-confirmation-header i {
            color: #4CAF50;
            font-size: 60px;
            margin-bottom: 10px;
        }
        .order-confirmation-header h2 {
            color: #333;
            margin: 0;
        }
        .order-confirmation-body {
            margin-bottom: 30px;
        }
        .order-confirmation-body p {
            margin: 10px 0;
            color: #555;
        }
        .order-confirmation-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .continue-shopping-btn, .view-order-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .continue-shopping-btn {
            background-color: #f0f0f0;
            color: #333;
        }
        .view-order-btn {
            background-color: #1a237e;
            color: white;
        }
        .continue-shopping-btn:hover {
            background-color: #e0e0e0;
        }
        .view-order-btn:hover {
            background-color: #0e1859;
        }
    `;
  document.head.appendChild(style);

  // Add event listeners
  modal
    .querySelector(".continue-shopping-btn")
    .addEventListener("click", function () {
      document.body.removeChild(modal);
      window.location.href = "index.html";
    });

  modal.querySelector(".view-order-btn").addEventListener("click", function () {
    document.body.removeChild(modal);
    window.location.href = "order-confirmation.html?order=" + orderNumber;
  });
}

// Generate a random order number
function generateOrderNumber() {
  return "TH-" + Math.floor(100000 + Math.random() * 900000);
}

// Add CSS for the add-to-cart notification
const style = document.createElement("style");
style.textContent = `
    .add-to-cart-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #fff;
        border-left: 4px solid #27ae60;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 15px;
        z-index: 1000;
        display: flex;
        align-items: center;
        max-width: 350px;
        animation: slideIn 0.3s ease-out;
    }
    
    .notification-content {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .notification-content i {
        color: #27ae60;
        font-size: 1.2em;
        margin-bottom: 5px;
    }
    
    .notification-content p {
        margin: 0 0 10px 0;
    }
    
    .view-cart-btn {
        display: inline-block;
        background-color: #1a237e;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
        align-self: flex-start;
    }
    
    .close-notification {
        background: none;
        border: none;
        font-size: 1.2em;
        cursor: pointer;
        color: #666;
        margin-left: 10px;
    }
    
    .fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(style);
