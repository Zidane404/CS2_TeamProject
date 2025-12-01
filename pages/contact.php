<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name && $email && $message) {

        // Use your Aston hosting credentials
        $db_host = "localhost";
        $db_user = "cs2team8";
        $db_pass = "F3lCvksLmJqDqmsyllNrjsF8R";
        $db_name = "cs2team8_db";

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            $response = "Database connection failed: " . $conn->connect_error;
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $name, $email, $message);

            if ($stmt->execute()) {
                $response = "Message successfully sent! 🎉";
            } else {
                $response = "Database error: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }

    } else {
        $response = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact • Drip or Drown</title>

    <!-- Fonts -->
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

      * { box-sizing: border-box; margin: 0; padding: 0; }

      body {
        font-family: "Poppins", sans-serif;
        background: radial-gradient(circle at top, #191a20 0%, var(--deep-black) 45%);
        color: #f7f7f9;
        min-height: 100vh;
      }

      a { text-decoration: none; color: inherit; }

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

      nav a:hover { color: #fff; }

      .contact-section {
        padding: 6rem 0 4rem;
      }

      .section-title {
        text-transform: uppercase;
        letter-spacing: 0.4em;
        font-size: 0.85rem;
        color: var(--muted);
        margin-bottom: 1rem;
      }

      .contact-card {
        background: rgba(11, 11, 13, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.04);
        border-radius: 28px;
        padding: 2.5rem;
        box-shadow: 0 25px 100px rgba(0, 0, 0, 0.45);
        max-width: 700px;
        margin: auto;
      }

      .contact-card h2 {
        font-size: clamp(2rem, 4vw, 2.6rem);
        text-align: center;
        margin-bottom: 1.4rem;
      }

      .contact-card p {
        text-align: center;
        color: var(--muted);
        margin-bottom: 2rem;
      }

      form {
        display: grid;
        gap: 1.5rem;
      }

      input,
      textarea {
        width: 100%;
        padding: 1rem 1.2rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        font-size: 1rem;
        color: #fff;
      }

      textarea { height: 150px; resize: none; }

      button {
        padding: 1rem 1.4rem;
        border-radius: 999px;
        border: none;
        background: linear-gradient(120deg, var(--accent), #f6e7c1);
        color: #151515;
        font-weight: 600;
        letter-spacing: 0.15em;
        cursor: pointer;
        transition: transform 180ms ease;
      }

      button:hover {
        transform: translateY(-3px);
      }

      footer {
        text-align: center;
        padding: 2rem 0;
        color: var(--muted);
        font-size: 0.85rem;
      }
    </style>
  </head>

  <body>
    <header>
      <div class="page-shell" style="display: flex; align-items: center; gap: 2rem">
        <div class="brand">
          <img src="../images/Logo.png" alt="Drip or Drown logo" class="brand-logo" />
          Drip or Drown
        </div>
        <nav>
          <a href="index.html">Home</a>
          <a href="login.html">Login</a>
          <a href="register.html">Register</a>
          <a href="cart.html">Cart</a>
          <a href="contact.php" style="color:#fff;">Contact</a>
        </nav>
      </div>
    </header>

    <main class="page-shell">
      <section class="contact-section">
        <div class="section-title">Contact</div>

        <div class="contact-card">
          <h2>Get in Touch</h2>
          <p>Have a question, issue, or want to reach the team? Send us a message.</p>

          <!-- RESPONSE MESSAGE -->
          <?php if (!empty($response)): ?>
            <div style="color:#d6b372; text-align:center; margin-bottom:1rem;">
              <?= htmlspecialchars($response) ?>
            </div>
          <?php endif; ?>

          <form action="contact.php" method="POST">
            <input type="text" name="name" placeholder="Your Name" required />
            <input type="email" name="email" placeholder="Your Email" required />
            <textarea name="message" placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
          </form>
        </div>
      </section>
    </main>

    <footer>© 2025 Drip or Drown — All Rights Reserved</footer>
  </body>
</html>
