<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'db_config.php';

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? $_SESSION['first_name'] . ' ' . ($_SESSION['last_name'] ?? '');
$userEmail = $_SESSION['user_email'] ?? '';

try {
    // Fetch user's orders with details
    $stmt = $pdo->prepare('
        SELECT 
            o.order_id,
            o.order_total,
            o.order_status,
            o.payment_status,
            o.placed_at,
            o.currency
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.placed_at DESC
    ');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Orders | Drip or Drown</title>
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

      nav > a,
      .dropbtn {
        transition: color 0.2s ease;
        cursor: pointer;
        font-weight: 500;
        letter-spacing: 0.05em;
      }

      nav > a:hover,
      .dropdown:hover .dropbtn {
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
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.5);
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

      .orders-container {
        padding: 5rem 0 4rem;
      }

      .orders-header {
        margin-bottom: 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
      }

      .orders-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
      }

      .orders-header p {
        color: var(--muted);
        font-size: 1rem;
      }

      .return-global-btn {
        background: transparent;
        border: 1px solid var(--accent);
        color: var(--accent);
        padding: 0.6rem 1.5rem;
        border-radius: 999px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .return-global-btn:hover {
        background: rgba(214, 179, 114, 0.1);
      }

      .orders-list {
        display: grid;
        gap: 1.5rem;
      }

      .order-card {
        background: linear-gradient(145deg, var(--graphite), var(--deep-black));
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.65);
        transition: transform 0.2s ease;
      }

      .order-card:hover {
        transform: translateY(-2px);
      }

      .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
      }

      .order-id {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--accent);
      }

      .order-date {
        color: var(--muted);
        font-size: 0.9rem;
      }

      .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
      }

      .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
      }

      .detail-label {
        font-size: 0.8rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
      }

      .detail-value {
        font-size: 1.1rem;
        font-weight: 500;
      }

      .status-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      }

      .status-pending {
        background: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
      }

      .status-processing {
        background: rgba(33, 150, 243, 0.15);
        color: #2196f3;
        border: 1px solid rgba(33, 150, 243, 0.3);
      }

      .status-shipped {
        background: rgba(156, 39, 176, 0.15);
        color: #9c27b0;
        border: 1px solid rgba(156, 39, 176, 0.3);
      }

      .status-delivered {
        background: rgba(76, 175, 80, 0.15);
        color: #4caf50;
        border: 1px solid rgba(76, 175, 80, 0.3);
      }

      .status-cancelled {
        background: rgba(244, 67, 54, 0.15);
        color: #f44336;
        border: 1px solid rgba(244, 67, 54, 0.3);
      }

      .status-returned {
        background: rgba(255, 152, 0, 0.15);
        color: #ff9800;
        border: 1px solid rgba(255, 152, 0, 0.3);
      }

      .order-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
      }

      .return-btn {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #f7f7f9;
        padding: 0.6rem 1.5rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .return-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
      }

      .view-details-btn {
        display: inline-block;
        padding: 0.6rem 1.5rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: transparent;
        color: #f7f7f9;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .view-details-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
      }

      .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: rgba(11, 11, 13, 0.7);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.04);
      }

      .empty-state h2 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
      }

      .empty-state p {
        color: var(--muted);
        margin-bottom: 2rem;
      }

      .empty-state a {
        display: inline-block;
        padding: 0.9rem 2rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: #0a0a0a;
        color: #f7f7f9;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        transition: all 0.2s ease;
      }

      .empty-state a:hover {
        transform: translateY(-1px);
        background: #151515;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
      }

      /* Modal styles */
      .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
      }

      .modal-content {
        background: linear-gradient(145deg, var(--graphite), var(--deep-black));
        margin: 5% auto;
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 24px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 28px 80px rgba(0,0,0,0.9);
        color: #f7f7f9;
      }

      .close {
        color: var(--muted);
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
      }

      .close:hover {
        color: var(--accent);
      }

      .modal h2 {
        margin-bottom: 1.5rem;
        color: var(--accent);
      }

      .modal .form-group {
        margin-bottom: 1.2rem;
      }

      .modal label {
        display: block;
        margin-bottom: 0.3rem;
        font-size: 0.9rem;
        color: var(--muted);
      }

      .modal input,
      .modal textarea,
      .modal select {
        width: 100%;
        padding: 0.7rem 1rem;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(5,5,8,0.9);
        color: #f7f7f9;
        font-family: inherit;
      }

      .modal input:focus,
      .modal textarea:focus,
      .modal select:focus {
        outline: none;
        border-color: var(--accent);
      }

      .modal button[type="submit"] {
        background: var(--accent);
        color: #151515;
        border: none;
        padding: 0.9rem 1.6rem;
        border-radius: 999px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        cursor: pointer;
        width: 100%;
        margin-top: 1rem;
        transition: transform 0.2s ease;
      }

      .modal button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(214, 179, 114, 0.45);
      }

      #return-status {
        margin-top: 1rem;
        padding: 0.8rem;
        border-radius: 8px;
        display: none;
      }

      .status-success {
        background: rgba(76, 175, 80, 0.2);
        color: #4caf50;
        border: 1px solid rgba(76, 175, 80, 0.3);
      }

      .status-error {
        background: rgba(244, 67, 54, 0.2);
        color: #f44336;
        border: 1px solid rgba(244, 67, 54, 0.3);
      }

      @media (max-width: 720px) {
        header {
          position: static;
        }

        nav {
          margin-top: 1rem;
          flex-wrap: wrap;
        }

        .order-header {
          flex-direction: column;
        }

        .order-details {
          grid-template-columns: 1fr;
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
          <a href="cart.html" class="nav-btn">Cart</a>
          <a href="orders.php" class="nav-btn">Orders</a>
          
          <span style="color: var(--accent); font-weight: 500;">
            Hi, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>
          </span>
          <a href="logout.php" class="nav-btn">Logout</a>
        </nav>
      </div>
    </header>

    <main class="page-shell orders-container">
      <div class="orders-header">
        <div>
          <h1>My Orders</h1>
          <p>View and track all your orders</p>
        </div>
        <button class="return-global-btn" onclick="openReturnModal()">Request Return</button>
      </div>

      <?php if (empty($orders)): ?>
        <div class="empty-state">
          <h2>No Orders Yet</h2>
          <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
          <a href="collection.html">Browse Collection</a>
        </div>
      <?php else: ?>
        <div class="orders-list">
          <?php foreach ($orders as $order): ?>
            <?php
              $statusClass = 'status-' . strtolower($order['order_status']);
              $orderDate = date('F j, Y', strtotime($order['placed_at']));
              $orderTime = date('g:i A', strtotime($order['placed_at']));
            ?>
            <div class="order-card">
              <div class="order-header">
                <div>
                  <div class="order-id">Order #<?= htmlspecialchars($order['order_id']) ?></div>
                  <div class="order-date">Placed on <?= htmlspecialchars($orderDate) ?> at <?= htmlspecialchars($orderTime) ?></div>
                </div>
                <span class="status-badge <?= $statusClass ?>">
                  <?= htmlspecialchars(ucfirst($order['order_status'])) ?>
                </span>
              </div>

              <div class="order-details">
                <div class="detail-item">
                  <span class="detail-label">Order Total</span>
                  <span class="detail-value">
                    <?= htmlspecialchars($order['currency']) ?> £<?= number_format($order['order_total'], 2) ?>
                  </span>
                </div>

                <div class="detail-item">
                  <span class="detail-label">Payment Status</span>
                  <span class="detail-value" style="color: <?= $order['payment_status'] === 'paid' ? '#4caf50' : '#ff9800' ?>">
                    <?= htmlspecialchars(ucfirst($order['payment_status'])) ?>
                  </span>
                </div>

                <div class="detail-item">
                  <span class="detail-label">Order ID</span>
                  <span class="detail-value">#<?= htmlspecialchars($order['order_id']) ?></span>
                </div>
              </div>

              <div class="order-actions">
                <button 
                  class="view-details-btn" 
                  onclick="viewOrderDetails(<?= (int)$order['order_id'] ?>)"
                >
                  View Details
                </button>
                <button 
                  class="return-btn" 
                  onclick="openReturnModal(<?= (int)$order['order_id'] ?>, '<?= htmlspecialchars($orderDate) ?>')"
                >
                  Request Return
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>

    <!-- Return Request Modal -->
    <div id="returnModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeReturnModal()">&times;</span>
        <h2>Request a Return</h2>
        <form id="return-form">
          <div class="form-group">
            <label for="return-name">Full Name *</label>
            <input type="text" id="return-name" name="name" value="<?= htmlspecialchars($userName) ?>" required>
          </div>
          <div class="form-group">
            <label for="return-email">Email *</label>
            <input type="email" id="return-email" name="email" value="<?= htmlspecialchars($userEmail) ?>" required>
          </div>
          <div class="form-group">
            <label for="return-order">Order Number *</label>
            <input type="text" id="return-order" name="order_id" required>
          </div>
          <div class="form-group">
            <label for="return-date">Order Date (approx.)</label>
            <input type="text" id="return-date" name="order_date" placeholder="e.g., December 2025">
          </div>
          <div class="form-group">
            <label for="return-reason">Reason for Return *</label>
            <select id="return-reason" name="reason" required>
              <option value="">-- Select a reason --</option>
              <option value="wrong_item">Wrong item received</option>
              <option value="defective">Defective / not working</option>
              <option value="damaged">Damaged in shipping</option>
              <option value="size_fit">Size / fit issue</option>
              <option value="changed_mind">Changed my mind</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="return-message">Additional Details</label>
            <textarea id="return-message" name="message" rows="3" placeholder="Please provide any extra information..."></textarea>
          </div>
          <button type="submit">Submit Return Request</button>
          <div id="return-status"></div>
        </form>
      </div>
    </div>

    <script>
      // Modal functions
      const modal = document.getElementById('returnModal');
      const orderInput = document.getElementById('return-order');
      const dateInput = document.getElementById('return-date');

      function openReturnModal(orderId = '', orderDate = '') {
        if (orderId) orderInput.value = orderId;
        if (orderDate) dateInput.value = orderDate;
        modal.style.display = 'block';
      }

      function closeReturnModal() {
        modal.style.display = 'none';
        // Clear form and status
        document.getElementById('return-form').reset();
        document.getElementById('return-status').style.display = 'none';
      }

      // Close when clicking outside modal content
      window.onclick = function(event) {
        if (event.target == modal) {
          closeReturnModal();
        }
      }

      // Handle form submission
      document.getElementById('return-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const statusDiv = document.getElementById('return-status');
        statusDiv.style.display = 'none';
        statusDiv.className = '';

        const formData = new FormData(this);

        try {
          const response = await fetch('return_request.php', {
            method: 'POST',
            body: formData
          });
          const result = await response.json();

          if (response.ok && result.success) {
            statusDiv.textContent = 'Return request submitted successfully. We will contact you soon.';
            statusDiv.className = 'status-success';
            statusDiv.style.display = 'block';
            this.reset();
            setTimeout(closeReturnModal, 2000);
          } else {
            statusDiv.textContent = result.error || 'An error occurred. Please try again.';
            statusDiv.className = 'status-error';
            statusDiv.style.display = 'block';
          }
        } catch (err) {
          statusDiv.textContent = 'Network error. Please try again.';
          statusDiv.className = 'status-error';
          statusDiv.style.display = 'block';
        }
      });

      // Placeholder for view details
      function viewOrderDetails(orderId) {
        alert('Order details for Order #' + orderId + '\n\nThis feature can be expanded to show order items, shipping info, etc.');
      }
    </script>
  </body>
</html>