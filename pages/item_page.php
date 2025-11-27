<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cs2team8_DATA;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //FETCH ITEM ID FROM URL!! (Redirect from shop page)
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
        z-index: 10;
        background: rgba(0, 0, 0, 0.75);
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
      }

      .brand-logo {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        padding: 0.55rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
      }

      nav {
        margin-left: auto;
        display: flex;
        gap: 1.5rem;
        font-size: 0.95rem;
        color: var(--muted);
      }

      nav a:hover {
        color: #fff;
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

      .image-container img

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
              <img src="images/Logo.png" alt="Drip or Drown logo" class="brand-logo" />
              Drip or Drown
            </div>
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
                <button class="primary add-to-cart-btn">Add to Cart</button>
                <button class="secondary buy-now-btn">Buy Now</button>
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
      </body>

    </html>
