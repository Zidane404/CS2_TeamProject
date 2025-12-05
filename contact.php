<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$emailStatus = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $to_email = "dripordrowninfo2025@gmail.com"; 
    $subject_prefix = "[Drip or Drown Contact] "; 


    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST["message"]));

    if (!empty($name) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $email_subject = $subject_prefix . "New message from $name";
        
        $email_content = "You have received a new message from your website contact form.\n\n";
        $email_content .= "Name: $name\n";
        $email_content .= "Email: $email\n\n";
        $email_content .= "Message:\n$message\n";

        $headers = "From: Website Contact Form <noreply@" . $_SERVER['SERVER_NAME'] . ">\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to_email, $email_subject, $email_content, $headers)) {
            $emailStatus = "success";
        } else {
            $emailStatus = "error";
            echo "<div style='background:red; color:white; padding:10px; text-align:center;'>Server Error: The mail() function failed.</div>";
        }
    } else {
        $emailStatus = "validation_error";
        if(empty($name)) echo "";
        if(empty($message)) echo "";
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) echo "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact • Drip or Drown</title>

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
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.9rem;
      }

      nav a {
        color: var(--muted);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.1em;
      }

      nav a:hover,
      nav a.active {
        color: var(--accent);
      }

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
        padding: 0.9rem 1.2rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: #0a0a0a;
        color: #f7f7f9;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
        width: 100%;
      }

      button:hover {
        transform: translateY(-1px);
        background: #151515;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
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
      <div class="page-shell" style="display: flex; align-items: center; justify-content: space-between; gap: 2rem">
        <div class="brand">
          <a href="index.html" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
            <img src="images/Logo-2.png" alt="Drip or Drown logo" class="brand-logo" />
            Drip or Drown
          </a>
        </div>
        <nav>
          <a href="index.html">Home</a>
          <a href="collection.html">Shop ▾</a>
          <a href="AboutUs.html">About</a>
          <a href="contact.php">Contact</a>
          <a href="cart.html" class="nav-btn">Cart</a>
          <a href="login.html" class="nav-btn">Login</a>
        </nav>
      </div>
    </header>

    <main class="page-shell">
      <section class="contact-section">
        <div class="section-title">Contact</div>

        <div class="contact-card">
          <h2>Get in Touch</h2>
          <p>Have a question, issue, or want to reach the team? Send us a message.</p>

          <form action="" method="POST" id="contact-form">
            <input type="text" name="name" placeholder="Your Name" required />
            <input type="email" name="email" placeholder="Your Email" required />
            <textarea name="message" placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
          </form>
        </div>
      </section>
    </main>

    <footer>© 2025 Drip or Drown — All Rights Reserved</footer>
    <script src="app.js"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // We read the PHP status printed into a JS variable
        const emailStatus = "<?php echo $emailStatus; ?>";

        if (emailStatus === "success") {
            showPopup("Your message has been sent.<br>We will get back to you soon.");
        } else if (emailStatus === "error") {
            showPopup("Server Error: Message could not be sent.");
        } else if (emailStatus === "validation_error") {
            showPopup("Please fill in all fields correctly.");
        }

        function showPopup(message) {
            const popup = document.createElement('div');
            popup.id = 'msg-sent-popup';
            popup.style.cssText = `
              position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
              background: #0a0a0a; border: 1px solid rgba(255, 255, 255, 0.2);
              border-radius: 12px; padding: 2rem; z-index: 10000;
              max-width: 500px; color: #f7f7f9; font-size: 1.1rem;
              text-align: center; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.8);`;
            
            popup.innerHTML = `
              <p style='margin-bottom:1.2rem;'>${message}</p>
              <button id="close-popup" style='background: #1a1a1a; color: #f7f7f9; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 0.75rem 2rem; cursor: pointer; font-weight: 600; width:100%;'>OK</button>`;
            
            const overlay = document.createElement('div');
            overlay.style.cssText = `position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); z-index: 9999;`;
            
            const closeFunc = () => { popup.remove(); overlay.remove(); };
            overlay.onclick = closeFunc;
            document.body.appendChild(overlay);
            document.body.appendChild(popup);
            document.getElementById('close-popup').onclick = closeFunc;
            
            // Clear URL history so refresh doesn't resend
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        }
      });
    </script>
  </body>
</html>