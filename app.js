
(function () {
  // --- Shop / collection: load products and enable "Add to Cart" ---
  const categorySectionsEl = document.getElementById("category-sections");
  const grid = document.querySelector(".product-grid");

  // Check login status
  let isLoggedIn = false;
  let userFirstName = '';

  // Show/hide Login / Register / Logout links based on auth state
function updateAuthLinks() {
    const loginLink = document.getElementById("nav-login");
    const registerLink = document.getElementById("nav-register");
    const logoutLink = document.getElementById("nav-logout");
    // 1. Get the orders link
    const ordersLink = document.getElementById("nav-orders");

    if (isLoggedIn) {
      if (registerLink) registerLink.style.display = "none";
      if (loginLink) loginLink.style.display = "none";
      if (logoutLink) logoutLink.style.display = "inline-block";
      
      // 2. Show orders link when logged in
      if (ordersLink) ordersLink.style.display = "inline-block"; 
    } else {
      if (registerLink) registerLink.style.display = "inline-block";
      if (loginLink) loginLink.style.display = "inline-block";
      if (logoutLink) logoutLink.style.display = "none";
      
      // 3. Hide orders link when logged out
      if (ordersLink) ordersLink.style.display = "none";
    }
  }

  function checkLoginStatus() {
    return fetch("check_login.php")
      .then((res) => res.json())
      .then((data) => {
        isLoggedIn = data.logged_in || false;
        userFirstName = data.first_name || '';
        displayWelcomeMessage();
        updateAuthLinks();
        return data;
      })
      .catch(() => {
        isLoggedIn = false;
        updateAuthLinks();
        return { logged_in: false };
      });
  }

  // Display welcome message when logged in
  function displayWelcomeMessage() {
    const welcomeEl = document.getElementById('welcome-message');
    if (welcomeEl && isLoggedIn && userFirstName) {
      welcomeEl.textContent = `Welcome, ${userFirstName}`;
      welcomeEl.style.display = 'block';
    } else if (welcomeEl) {
      welcomeEl.style.display = 'none';
    }
  }

  // Check login status on page load
  checkLoginStatus();

  // Show error popup
  function showErrorPopup(message) {
    // Remove existing popup if any
    const existing = document.getElementById('login-error-popup');
    if (existing) existing.remove();

    const popup = document.createElement('div');
    popup.id = 'login-error-popup';
    popup.style.cssText = `
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #0a0a0a;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      padding: 2rem;
      z-index: 10000;
      max-width: 500px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);
    `;
    
    const messageEl = document.createElement('p');
    messageEl.textContent = message;
    messageEl.style.cssText = `
      color: #f7f7f9;
      margin: 0 0 1.5rem 0;
      font-size: 1rem;
      line-height: 1.5;
    `;
    
    const button = document.createElement('button');
    button.textContent = 'OK';
    button.style.cssText = `
      background: #1a1a1a;
      color: #f7f7f9;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      padding: 0.75rem 2rem;
      cursor: pointer;
      font-weight: 600;
      width: 100%;
    `;
    button.onclick = () => popup.remove();
    
    const overlay = document.createElement('div');
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
    `;
    overlay.onclick = () => {
      popup.remove();
      overlay.remove();
    };
    
    popup.appendChild(messageEl);
    popup.appendChild(button);
    document.body.appendChild(overlay);
    document.body.appendChild(popup);
  }

  function addToCart(productId, quantity) {
    if (!productId) return;
    const qty = quantity && Number(quantity) > 0 ? Number(quantity) : 1;
    
    // Check login status first
    checkLoginStatus().then(() => {
      if (!isLoggedIn) {
        showErrorPopup("To purchase you need to be logged in or registered. Please go to the login/register page to do the following.");
        return;
      }

      fetch("add_to_cart_function.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:
          "product_id=" +
          encodeURIComponent(productId) +
          "&quantity=" +
          encodeURIComponent(qty),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.error) {
            if (data.error === "Not logged in") {
              showErrorPopup(
                "To purchase you need to be logged in or registered. Please go to the login/register page to do the following."
              );
            } else {
              showErrorPopup(data.error || "An error occurred. Please try again.");
            }
          } else if (data.ok) {
            // Item added successfully; keep user on the page so they can continue shopping
            // You could replace this alert with a nicer toast-style message if desired.
            alert("Item added to cart.");
          }
        })
        .catch(() => {
          showErrorPopup("An error occurred. Please try again.");
        });
    });
  }

  function createProductCard(item) {
    const card = document.createElement("div");
    card.className = "product-card";

    const img = document.createElement("img");
    img.src = item.image_url || "images/diamond-cross.jpg";
    img.alt = item.name || "Product image";

    const title = document.createElement("div");
    title.className = "product-title";
    title.textContent = item.name || "Untitled";

    const price = document.createElement("div");
    price.className = "product-price";
    price.textContent =
      item.price != null && item.price !== ""
        ? "£" + item.price
        : "";

    const btn = document.createElement("a");
    btn.href = "item_page.php?id=" + encodeURIComponent(item.id);
    btn.className = "btn";
    btn.textContent = "View";
    btn.style.display = "inline-block";
    btn.style.textAlign = "center";
    btn.style.textDecoration = "none";

    card.appendChild(img);
    card.appendChild(title);
    card.appendChild(price);
    card.appendChild(btn);
    return card;
  }

  // Category mapping
  const categoryNames = {
    1: "Chains",
    2: "Rings",
    3: "Anklets",
    4: "Belly Bars",
    5: "Earrings"
  };

  if (categorySectionsEl) {
    fetch("fetch_products.php")
      .then((res) => res.json())
      .then((data) => {
        if (!data || !Array.isArray(data.items)) return;
        
        // Group products by category_id
        const grouped = {};
        data.items.forEach((item) => {
          const catId = item.category_id || 0;
          if (!grouped[catId]) {
            grouped[catId] = [];
          }
          grouped[catId].push(item);
        });

        categorySectionsEl.innerHTML = "";

        // Render each category section with anchors for nav
        [1, 2, 3, 4, 5].forEach((catId) => {
          if (!grouped[catId] || grouped[catId].length === 0) return;

          const section = document.createElement("section");
          section.className = "category-section";
          section.id = "category-" + catId;

          const heading = document.createElement("h2");
          heading.textContent = categoryNames[catId] || `Category ${catId}`;
          section.appendChild(heading);

          const grid = document.createElement("div");
          grid.className = "product-grid";

          grouped[catId].forEach((item) => {
            grid.appendChild(createProductCard(item));
          });

          section.appendChild(grid);
          categorySectionsEl.appendChild(section);
        });
      })
      .catch(() => {
        // fail silently on the front-end
      });
  } else if (grid) {
    // Fallback for pages that still use the old .product-grid structure
    fetch("fetch_products.php")
      .then((res) => res.json())
      .then((data) => {
        if (!data || !Array.isArray(data.items)) return;
        grid.innerHTML = "";
        data.items.forEach((item) => {
          grid.appendChild(createProductCard(item));
        });
      })
      .catch(() => {
        // fail silently on the front-end
      });
  }

  // --- Cart page: load cart for the currently logged in user ---
  const cartItemsEl = document.getElementById("cart-items");
  const subtotalEl = document.getElementById("cart-subtotal");
  const vatEl = document.getElementById("cart-vat");
  const totalEl = document.getElementById("cart-total");

  if (cartItemsEl) {
    fetch("fetch_cart.php")
      .then((res) => res.json())
      .then((data) => {
        if (!data || !Array.isArray(data.items)) return;

        cartItemsEl.innerHTML = "";
        let subtotal = 0;

        data.items.forEach((item) => {
          const price = parseFloat(item.price) || 0;
          const qty = parseInt(item.quantity, 10) || 1;
          subtotal += price * qty;

          const row = document.createElement("article");
          row.className = "cart-item";

          const img = document.createElement("img");
          img.src = item.image_url || "images/diamond-cross.jpg";
          img.alt = item.name || "Product image";

          const info = document.createElement("div");
          const title = document.createElement("div");
          title.className = "cart-item-title";
          title.textContent = item.name || "Untitled";
          const meta = document.createElement("div");
          meta.className = "cart-item-meta";
          meta.textContent = `Qty ${qty} · £${price.toFixed(2)}`;
          info.appendChild(title);
          info.appendChild(meta);

          const priceEl = document.createElement("div");
          priceEl.textContent = "£" + (price * qty).toFixed(2);

          row.appendChild(img);
          row.appendChild(info);
          row.appendChild(priceEl);
          cartItemsEl.appendChild(row);
        });

        if (typeof subtotalEl !== "undefined" && subtotalEl) {
          subtotalEl.textContent = "£" + subtotal.toFixed(2);
        }
        if (typeof vatEl !== "undefined" && vatEl) {
          const vat = subtotal * 0.2;
          vatEl.textContent = "£" + vat.toFixed(2);
        }
        if (typeof totalEl !== "undefined" && totalEl) {
          const vat = subtotal * 0.2;
          totalEl.textContent = "£" + (subtotal + vat).toFixed(2);
        }
      })
      .catch(() => {
        // fail silently on the front-end
      });
  }

  // Expose addToCart globally so inline onclick handlers (e.g. on item_page.php)
  // can call it.
  window.addToCart = addToCart;

  document.addEventListener('DOMContentLoaded', function() {
    var checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
      checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Remove existing popup if any
        var existing = document.getElementById('order-complete-popup');
        if (existing) existing.remove();
        var popup = document.createElement('div');
        popup.id = 'order-complete-popup';
        popup.style.cssText = ` 
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background: #0a0a0a;
          border: 1px solid rgba(255,255,255,0.2);
          border-radius: 12px;
          padding: 2rem;
          z-index: 10000;
          max-width: 500px;
          color: #f7f7f9;
          font-size: 1.1rem;
          text-align: center;
          box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);`;
        popup.innerHTML = `<p style='margin-bottom:1.2rem;'>Your order has been complete.</p><button style='background: #1a1a1a; color: #f7f7f9; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 0.75rem 2rem; cursor: pointer; font-weight: 600; width:100%;'>OK</button>`;
        var overlay = document.createElement('div');
        overlay.style.cssText = `position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); z-index: 9999;`;
        overlay.onclick = function() { popup.remove(); overlay.remove(); window.location.href = 'index.html'; };
        popup.querySelector('button').onclick = function() { popup.remove(); overlay.remove(); window.location.href = 'index.html'; };
        document.body.appendChild(overlay);
        document.body.appendChild(popup);
        // Optionally clear the form fields:
        checkoutForm.reset();
      });
    }
  });
})();
