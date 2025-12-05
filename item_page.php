<?php
session_start();

require_once 'db_config.php';

try {
    // FETCH ITEM ID FROM URL!! (Redirect from shop page)
    $id = $_GET['id'] ?? 1;

    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) die("Product not found. Check your URL ID.");


    $stmt = $pdo->prepare("
        SELECT r.*, COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Guest') as author 
        FROM reviews r 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id = ? AND r.approved = 1
    ");
    $stmt->execute([$id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $reviewCount = count($reviews);
    $ratingSum = array_sum(array_column($reviews, 'rating'));
    $avgRating = $reviewCount > 0 ? round($ratingSum / $reviewCount, 1) : 0;

} catch (PDOException $e) {
    die("Connection to Database failed: " . $e->getMessage());
}
?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Drip or Drown</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
    <style>
      :root {
        color-scheme: dark;
        --deep-black: #020203;
        --onyx: #0b0b0d;
        --graphite: #111216;
        --accent: #d6b372;
        --muted: #868995;
      }

      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        font-family: "Poppins", sans-serif;
        background: radial-gradient(circle at top, #191a20 0%, var(--deep-black) 45%);
        color: #f7f7f9;
        min-height: 100vh;
        line-height: 1.6;
      }

      a {
        text-decoration: none;
        color: inherit;
      }

      img {
        max-width: 100%;
        display: block;
      }

      .page-shell {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem 4rem;
      }

      header {
        position: sticky;
        top: 0;
        z-index: 100; 
        background: rgba(0, 0, 0, 0.85); 
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        padding: 1.25rem 0;
      }

      .brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: #fff;
      }

      .brand-logo {
        width: 60px; 
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        padding: 0.35rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
      }

      nav {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 2rem;
        font-size: 0.95rem;
        color: var(--muted);
      }

      nav > a, .dropbtn {
        transition: color 0.2s ease;
        cursor: pointer;
        font-weight: 500;
        letter-spacing: 0.05em;
      }

      nav > a:hover, .dropdown:hover .dropbtn {
        color: var(--accent);
      }

      .dropdown {
        position: relative;
        display: inline-block;
      }

      .dropdown-content {
        display: none;
        position: absolute;
        background-color: var(--graphite);
        min-width: 220px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.5);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        z-index: 1;
        top: 100%; 
        padding: 0.5rem 0;
        margin-top: 10px;
      }
      
      .dropdown-content::before {
        content: "";
        position: absolute;
        top: -20px; 
        left: 0;
        width: 100%;
        height: 20px;
      }

      .dropdown-content a {
        color: var(--muted);
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 0.85rem;
        transition: background 0.2s;
      }

      .dropdown-content a:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: var(--accent);
      }

      .dropdown:hover .dropdown-content {
        display: block;
      }

      
      .nav-btn {
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1.2rem;
        border-radius: 99px;
        font-size: 0.85rem;
      }
      
      .nav-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
      }
      
      
      .purchase-actions {
        display: flex;
        gap: 1rem;
        margin: 2rem 0;
      }

      
      .purchase-actions button {
        background: transparent;
        color: inherit;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1.2rem;
        border-radius: 99px;
        font-size: 0.85rem;
        font-family: inherit;
        cursor: pointer;
        transition: color 0.2s ease, border-color 0.2s ease;
      }

      .purchase-actions button:hover {
        border-color: var(--accent);
        color: var(--accent);
      }

      .hero {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2.5rem;
        align-items: center;
        padding: 6rem 0 5rem;
      }

      .hero h1 {
        font-size: clamp(2.4rem, 5vw, 3.8rem);
        margin-bottom: 1.5rem;
      }

      .hero p {
        color: var(--muted);
        margin-bottom: 2rem;
        max-width: 460px;
      }

      .hero-cta {
        display: flex;
        gap: 1rem;
      }

      .hero-cta a {
        padding: 0.9rem 1.8rem;
        border-radius: 999px;
        font-weight: 500;
        transition: transform 180ms ease, box-shadow 180ms ease;
      }

      .hero-cta a.primary {
        background: linear-gradient(120deg, var(--accent), #f6e7c1);
        color: #151515;
        box-shadow: 0 8px 35px rgba(214, 179, 114, 0.35);
      }

      .hero-cta a.secondary {
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
      }

      .hero-cta a:hover {
        transform: translateY(-2px);
      }

      .hero-media {
        background: linear-gradient(145deg, var(--graphite), var(--deep-black));
        border-radius: 32px;
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.06);
        box-shadow: 0 35px 120px rgba(0, 0, 0, 0.55);
      }

      .hero-media img {
        border-radius: 24px;
        object-fit: cover;
      }

      .section-title {
        text-transform: uppercase;
        letter-spacing: 0.4em;
        font-size: 0.85rem;
        color: var(--muted);
        margin-bottom: 1rem;
      }

      .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.75rem;
        padding: 3rem 0 4rem;
      }

      .feature-card {
        background: rgba(11, 11, 13, 0.7);
        border-radius: 20px;
        padding: 1.75rem;
        border: 1px solid rgba(255, 255, 255, 0.04);
        transition: transform 180ms ease, border-color 180ms ease;
      }

      .feature-card:hover {
        transform: translateY(-6px);
        border-color: rgba(214, 179, 114, 0.4);
      }

      .feature-card h3 {
        margin-bottom: 0.6rem;
      }

      .feature-card p {
        color: var(--muted);
      }

      .gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-bottom: 4rem;
      }

      .gallery figure {
        position: relative;
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.04);
      }

      .gallery figcaption {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 1.25rem;
        background: linear-gradient(180deg, transparent, rgba(0, 0, 0, 0.85));
        font-size: 0.9rem;
        letter-spacing: 0.12em;
      }

      .cta {
        background: linear-gradient(90deg, rgba(214, 179, 114, 0.15), transparent);
        border: 1px solid rgba(214, 179, 114, 0.25);
        border-radius: 28px;
        padding: 3rem;
        text-align: center;
      }

      .cta h2 {
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        margin-bottom: 1rem;
      }

      .cta p {
        color: var(--muted);
        margin-bottom: 1.8rem;
      }

      .cta a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.95rem 2.4rem;
        border-radius: 999px;
        background: #fff;
        color: var(--deep-black);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
      }

      footer {
        text-align: center;
        padding: 2rem 0 1rem;
        color: var(--muted);
        font-size: 0.85rem;
      }

      .image-container {
        width: 200px;
        height: 300px;
        border: 1px solid black;
        margin-bottom: 25px;
      }

      @media (max-width: 720px) {
        header {
          position: static;
        }

        nav {
          margin-top: 1rem;
          flex-wrap: wrap;
        }

        .hero-cta {
          flex-direction: column;
        }

      }
     
    </style>

    



    </head>
      <body>
        <header>
          <div class="page-shell" style="display: flex; align-items: center; gap: 2rem">
            <div class="brand">
              <a href="index.html" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none; color:inherit;">
                <img src="images/Logo-2.png" alt="Drip or Drown logo" class="brand-logo" />
                Drip or Drown
              </a>
            </div>
            
            <nav>
                <a href="index.html">Home</a>
                <div class="dropdown">
                    <a href="collection.html" class="dropbtn">Shop ▾</a>
                    <div class="dropdown-content">
                        <a href="collection.html#category-1">GOLD CHAINS</a>
                        <a href="collection.html#category-2">RINGS</a>
                        <a href="collection.html#category-3">ANKLETS</a>
                        <a href="collection.html#category-4">BELLY BUTTON PIERCINGS</a>
                        <a href="collection.html#category-5">EARRINGS</a>
                    </div>
                </div>
                <a href="AboutUs.html">About</a>
                <a href="contact.php">Contact</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                  <span style="color: var(--accent); font-weight: 500;">
                    Hi, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>
                  </span>
                  <a href="orders.php" class="nav-btn">Orders</a>
                  <a href="logout.php" class="nav-btn">Logout</a>
                <?php else: ?>
                  <a href="login.html" class="nav-btn">Login</a>
                  <a href="register.html" class="nav-btn">Register</a>
                <?php endif; ?>

                <a href="cart.html" class="nav-btn">Cart</a>
            </nav>
          </div>
        </header>

        <main class="page-shell">
          <section class="product-itemn-details">
            <div class="product-media">
              <div class="main-image-container">
                <img
                  id="product-main-image"
                  src="<?= htmlspecialchars($product['main_image']) ?>"
                  alt="<?= htmlspecialchars($product['name']) ?>"
                  style="border-radius: 20px;"
                />
              </div>
            </div>

            <div class="product-info">
              <h1 id="product-name" class="item-name"><?= htmlspecialchars($product['name']) ?></h1>
              <p id="product-sku" class="item-id" style="color: var(--muted)">SKU: <?= htmlspecialchars($product['sku']) ?></p>

              <div class="price-rating">
                <span id="product-price" class="price">£<?= number_format($product['price'], 2) ?></span>
                
                <div id="product-rating" class="rating">
                    <span style="color: var(--accent);"><?= str_repeat('★', round($avgRating)) ?></span> 
                    <span style="color: var(--muted);"> (<?= $avgRating ?> Stars, <?= $reviewCount ?> Reviews)</span>
                </div>
              </div>

              <div class="purchase-actions">
                <form onsubmit="return false;" style="display:flex; gap:1rem; align-items:center;">
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.7rem; color:var(--muted); margin-left:10px;">Qty</label>
                        <input
                          type="number"
                          id="item-qty-<?= $product['product_id'] ?>"
                          value="1"
                          min="1"
                          max="10"
                          style="width: 60px; padding: 0.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: transparent; color: white; text-align: center;"
                        >
                    </div>

                    <button
                      type="button"
                      class="primary add-to-cart-btn"
                      onclick="addToCart(<?= (int)$product['product_id'] ?>, document.getElementById('item-qty-<?= $product['product_id'] ?>').value)"
                    >
                      Add to Cart
                    </button>
                </form>

              </div>

              <p id="product-description" class="description">
                <?= nl2br(htmlspecialchars($product['long_description'] ?? $product['short_description'])) ?>
              </p>

            </div>
          </section>

          <hr style="border-color: rgba(255, 255, 255, 0.05); margin: 3rem 0;"/>
          
          <div id="product-reviews">
            <h2 style="font-size: 2rem; margin-bottom: 1.5rem;">Customer Reviews</h2>
            
            <?php if ($reviewCount > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card" style="margin-bottom: 1.5rem; padding: 1.5rem; border-radius: 12px; background: var(--onyx);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <p style="font-weight: 600;"><?= htmlspecialchars($review['author']) ?></p>
                            <span style="color: var(--accent);"><?= str_repeat('★', $review['rating']) ?></span>
                        </div>
                        <p style="color: var(--muted);"><?= htmlspecialchars($review['body']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--muted);">No reviews yet for this item.</p>
            <?php endif; ?>
          </div>

        </main>
        
        <script>
          function addToCart(productId, quantity) {
            if (!productId) {
              alert('Invalid product');
              return;
            }
            
            const qty = quantity && Number(quantity) > 0 ? Number(quantity) : 1;
            
            fetch('check_login.php')
              .then(res => res.json())
              .then(data => {
                if (!data.logged_in) {
                  showErrorPopup("To purchase you need to be logged in or registered. Please go to the login/register page.");
                  return;
                }
                
                return fetch('add_to_cart_function.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: 'product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(qty)
                });
              })
              .then(res => {
                if (!res) return; 
                return res.json();
              })
              .then(data => {
                if (!data) return;
                
                if (data.error) {
                  showErrorPopup(data.error || 'An error occurred. Please try again.');
                } else if (data.ok) {
                  alert('Item added to cart successfully!');
                }
              })
              .catch(err => {
                console.error('Error:', err);
                showErrorPopup('An error occurred. Please try again.');
              });
          }

          function showErrorPopup(message) {
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
            button.onclick = () => {
              popup.remove();
              overlay.remove();
            };
            
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
        </script>
      </body>
    </html>