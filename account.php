<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'db_config.php';

$userId = (int) $_SESSION['user_id'];

$locationData = [
    'United Kingdom' => [
        'England' => ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Leeds', 'Bristol', 'Sheffield', 'Nottingham'],
        'Scotland' => ['Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee', 'Inverness'],
        'Wales' => ['Cardiff', 'Swansea', 'Newport', 'Wrexham'],
        'Northern Ireland' => ['Belfast', 'Derry', 'Lisburn', 'Newry']
    ],
    'United States' => [
        'California' => ['Los Angeles', 'San Diego', 'San Jose', 'San Francisco', 'Sacramento'],
        'New York' => ['New York City', 'Buffalo', 'Rochester', 'Albany', 'Syracuse'],
        'Texas' => ['Houston', 'Dallas', 'Austin', 'San Antonio', 'Fort Worth'],
        'Florida' => ['Miami', 'Orlando', 'Tampa', 'Jacksonville', 'Tallahassee']
    ],
    'Canada' => [
        'Ontario' => ['Toronto', 'Ottawa', 'Mississauga', 'Hamilton', 'London'],
        'British Columbia' => ['Vancouver', 'Victoria', 'Surrey', 'Burnaby', 'Kelowna'],
        'Alberta' => ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge'],
        'Quebec' => ['Montreal', 'Quebec City', 'Laval', 'Gatineau']
    ],
    'Australia' => [
        'New South Wales' => ['Sydney', 'Newcastle', 'Wollongong'],
        'Victoria' => ['Melbourne', 'Geelong', 'Ballarat'],
        'Queensland' => ['Brisbane', 'Gold Coast', 'Cairns'],
        'Western Australia' => ['Perth', 'Fremantle', 'Bunbury']
    ],
    'Ireland' => [
        'Leinster' => ['Dublin', 'Kilkenny', 'Wexford'],
        'Munster' => ['Cork', 'Limerick', 'Waterford'],
        'Connacht' => ['Galway', 'Sligo', 'Castlebar'],
        'Ulster' => ['Letterkenny', 'Cavan', 'Monaghan']
    ]
];

try {
    $stmt = $pdo->prepare('
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.created_at,
            a.address_id,
            a.address_line1,
            a.address_line2,
            a.city,
            a.state,
            a.postcode,
            a.country
        FROM users u
        LEFT JOIN addresses a 
            ON a.user_id = u.user_id
           AND (a.is_default_shipping = 1 OR a.is_default_billing = 1)
        WHERE u.user_id = ? 
          AND u.deleted_at IS NULL
        LIMIT 1
    ');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header('Location: login.html');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$currentCountry = $user['country'] ?? '';
$currentState   = $user['state'] ?? '';
$currentCity    = $user['city'] ?? '';

if ($currentCountry && !isset($locationData[$currentCountry])) {
    $locationData[$currentCountry] = [
        $currentState ?: 'Other' => [$currentCity ?: 'Other']
    ];
} elseif ($currentCountry && $currentState && !isset($locationData[$currentCountry][$currentState])) {
    $locationData[$currentCountry][$currentState] = [$currentCity ?: 'Other'];
} elseif ($currentCountry && $currentState && $currentCity && !in_array($currentCity, $locationData[$currentCountry][$currentState], true)) {
    $locationData[$currentCountry][$currentState][] = $currentCity;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account | Drip or Drown</title>
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
        --danger: #f44336;
        --success: #4caf50;
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

      .account-container {
        padding: 5rem 0 4rem;
      }

      .account-header {
        margin-bottom: 3rem;
      }

      .account-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
      }

      .account-header p {
        color: var(--muted);
        font-size: 1rem;
      }

      .account-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1.5rem;
      }

      .account-card {
        background: linear-gradient(145deg, var(--graphite), var(--deep-black));
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.65);
      }

      .account-card h2 {
        font-size: 1.3rem;
        margin-bottom: 0.4rem;
      }

      .account-card p {
        color: var(--muted);
        margin-bottom: 1.5rem;
      }

      .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.2rem;
      }

      .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
      }

      .form-group.full-width {
        grid-column: 1 / -1;
      }

      .form-group label {
        font-size: 0.85rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
      }

      .form-group input,
      .form-group select {
        width: 100%;
        padding: 0.95rem 1rem;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
        color: #f7f7f9;
        outline: none;
        font-family: inherit;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        appearance: none;
      }

      .form-group select {
        background-image:
          linear-gradient(45deg, transparent 50%, var(--accent) 50%),
          linear-gradient(135deg, var(--accent) 50%, transparent 50%);
        background-position:
          calc(100% - 22px) calc(1.2em + 2px),
          calc(100% - 16px) calc(1.2em + 2px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        padding-right: 3rem;
        cursor: pointer;
      }

      .form-group select option {
        background: var(--graphite);
        color: #f7f7f9;
      }

      .form-group input:focus,
      .form-group select:focus {
        border-color: rgba(214, 179, 114, 0.7);
        box-shadow: 0 0 0 3px rgba(214, 179, 114, 0.12);
      }

      .helper-text {
        font-size: 0.8rem;
        color: var(--muted);
        margin-top: 0.25rem;
      }

      .form-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
      }

      .primary-btn,
      .danger-btn {
        display: inline-block;
        padding: 0.9rem 1.5rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: transparent;
        color: #f7f7f9;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
      }

      .primary-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
      }

      .danger-btn {
        border-color: rgba(244, 67, 54, 0.35);
        color: #ffb3ad;
      }

      .danger-btn:hover {
        border-color: var(--danger);
        color: #fff;
        background: rgba(244, 67, 54, 0.12);
      }

      .info-list {
        display: grid;
        gap: 1rem;
      }

      .info-item {
        padding: 1rem 1.1rem;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
      }

      .info-label {
        display: block;
        font-size: 0.75rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 0.3rem;
      }

      .info-value {
        font-size: 1rem;
        font-weight: 500;
        word-break: break-word;
      }

      .message {
        margin-top: 1.25rem;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        display: none;
        font-size: 0.95rem;
      }

      .message.success {
        display: block;
        background: rgba(76, 175, 80, 0.12);
        border: 1px solid rgba(76, 175, 80, 0.3);
        color: #9be7a1;
      }

      .message.error {
        display: block;
        background: rgba(244, 67, 54, 0.12);
        border: 1px solid rgba(244, 67, 54, 0.3);
        color: #ffb3ad;
      }

      .password-checklist {
        margin-top: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
      }

      .password-checklist ul {
        margin-left: 1.2rem;
        color: var(--muted);
        font-size: 0.9rem;
      }

      .password-checklist li.valid {
        color: #9be7a1;
      }

      @media (max-width: 900px) {
        .account-grid {
          grid-template-columns: 1fr;
        }
      }

      @media (max-width: 720px) {
        header {
          position: static;
        }

        nav {
          margin-top: 1rem;
          flex-wrap: wrap;
        }

        .form-grid {
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
          <a href="account.php" class="nav-btn" style="border-color: rgba(214, 179, 114, 0.5); color: var(--accent);">Account</a>
          <span style="color: var(--accent); font-weight: 500;">
            Hi, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>
          </span>
          <a href="logout.php" class="nav-btn">Logout</a>
        </nav>
      </div>
    </header>

    <main class="page-shell account-container">
      <div class="account-header">
        <h1>My Account</h1>
        <p>View and manage your personal details</p>
      </div>

      <div class="account-grid">
        <section class="account-card">
          <h2>Account Details</h2>
          <p>Update the information linked to your customer account.</p>

          <form id="accountForm" novalidate>
            <div class="form-grid">
              <div class="form-group">
                <label for="first_name">First Name</label>
                <input
                  type="text"
                  id="first_name"
                  name="first_name"
                  maxlength="50"
                  value="<?= htmlspecialchars($user['first_name']) ?>"
                  required
                />
              </div>

              <div class="form-group">
                <label for="last_name">Last Name</label>
                <input
                  type="text"
                  id="last_name"
                  name="last_name"
                  maxlength="50"
                  value="<?= htmlspecialchars($user['last_name']) ?>"
                  required
                />
              </div>

              <div class="form-group full-width">
                <label for="email">Email Address</label>
                <input
                  type="email"
                  id="email"
                  name="email"
                  maxlength="255"
                  value="<?= htmlspecialchars($user['email']) ?>"
                  required
                />
              </div>

              <div class="form-group full-width">
                <label for="address_line1">Address Line 1</label>
                <input
                  type="text"
                  id="address_line1"
                  name="address_line1"
                  maxlength="255"
                  value="<?= htmlspecialchars($user['address_line1'] ?? '') ?>"
                />
                <div class="helper-text">Use your house/building number and street name.</div>
              </div>

              <div class="form-group full-width">
                <label for="address_line2">Address Line 2</label>
                <input
                  type="text"
                  id="address_line2"
                  name="address_line2"
                  maxlength="255"
                  value="<?= htmlspecialchars($user['address_line2'] ?? '') ?>"
                />
              </div>

              <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country">
                  <option value="">Select country</option>
                </select>
              </div>

              <div class="form-group">
                <label for="state">State / County</label>
                <select id="state" name="state" disabled>
                  <option value="">Select state / county</option>
                </select>
              </div>

              <div class="form-group">
                <label for="city">City</label>
                <select id="city" name="city" disabled>
                  <option value="">Select city</option>
                </select>
              </div>

              <div class="form-group">
                <label for="postcode">Postcode</label>
                <input
                  type="text"
                  id="postcode"
                  name="postcode"
                  maxlength="20"
                  value="<?= htmlspecialchars($user['postcode'] ?? '') ?>"
                />
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="primary-btn">Save Changes</button>
              <button type="button" id="deleteAccount" class="danger-btn">Delete My Account</button>
            </div>

            <div id="responseMsg" class="message"></div>
          </form>

          <hr style="margin: 2rem 0; border: 1px solid rgba(255,255,255,0.08);" />

          <h2>Change Password</h2>
          <p>Choose a stronger password to keep your account secure.</p>

          <form id="passwordForm" novalidate>
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required />
              </div>

              <div class="form-group full-width">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required />
              </div>

              <div class="form-group full-width">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required />
              </div>
            </div>

            <div class="password-checklist">
              <ul>
                <li id="pw-length">At least 8 characters</li>
                <li id="pw-upper">At least 1 uppercase letter</li>
                <li id="pw-lower">At least 1 lowercase letter</li>
                <li id="pw-number">At least 1 number</li>
                <li id="pw-match">Passwords match</li>
              </ul>
            </div>

            <div class="form-actions">
              <button type="submit" class="primary-btn">Update Password</button>
            </div>
          </form>
        </section>

        <aside class="account-card">
          <h2>Profile Summary</h2>
          <p>Your current customer details on file.</p>

          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Customer ID</span>
              <span class="info-value">#<?= htmlspecialchars($user['user_id']) ?></span>
            </div>

            <div class="info-item">
              <span class="info-label">Full Name</span>
              <span class="info-value">
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
              </span>
            </div>

            <div class="info-item">
              <span class="info-label">Email</span>
              <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>

            <div class="info-item">
              <span class="info-label">Address</span>
              <span class="info-value">
                <?=
                  htmlspecialchars(
                    trim(
                      implode(', ', array_filter([
                        $user['address_line1'] ?? '',
                        $user['address_line2'] ?? '',
                        $user['city'] ?? '',
                        $user['state'] ?? '',
                        $user['postcode'] ?? '',
                        $user['country'] ?? ''
                      ]))
                    ) ?: 'Not added'
                  )
                ?>
              </span>
            </div>

            <div class="info-item">
              <span class="info-label">Member Since</span>
              <span class="info-value">
                <?= !empty($user['created_at']) ? htmlspecialchars(date('F j, Y', strtotime($user['created_at']))) : 'N/A' ?>
              </span>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <script>
      const accountForm = document.getElementById("accountForm");
      const passwordForm = document.getElementById("passwordForm");
      const deleteBtn = document.getElementById("deleteAccount");
      const responseMsg = document.getElementById("responseMsg");

      const countrySelect = document.getElementById("country");
      const stateSelect = document.getElementById("state");
      const citySelect = document.getElementById("city");

      const currentCountry = <?= json_encode($currentCountry) ?>;
      const currentState = <?= json_encode($currentState) ?>;
      const currentCity = <?= json_encode($currentCity) ?>;

      const locationData = <?= json_encode($locationData, JSON_UNESCAPED_UNICODE) ?>;

      function showMessage(message, type) {
        responseMsg.textContent = message;
        responseMsg.className = "message " + type;
      }

      function populateCountries(selectedValue = "") {
        countrySelect.innerHTML = '<option value="">Select country</option>';

        Object.keys(locationData).forEach((country) => {
          const option = document.createElement("option");
          option.value = country;
          option.textContent = country;
          if (country === selectedValue) option.selected = true;
          countrySelect.appendChild(option);
        });
      }

      function populateStates(country, selectedValue = "") {
        stateSelect.innerHTML = '<option value="">Select state / county</option>';
        citySelect.innerHTML = '<option value="">Select city</option>';
        citySelect.disabled = true;

        if (!country || !locationData[country]) {
          stateSelect.disabled = true;
          return;
        }

        stateSelect.disabled = false;

        Object.keys(locationData[country]).forEach((state) => {
          const option = document.createElement("option");
          option.value = state;
          option.textContent = state;
          if (state === selectedValue) option.selected = true;
          stateSelect.appendChild(option);
        });
      }

      function populateCities(country, state, selectedValue = "") {
        citySelect.innerHTML = '<option value="">Select city</option>';

        if (!country || !state || !locationData[country] || !locationData[country][state]) {
          citySelect.disabled = true;
          return;
        }

        citySelect.disabled = false;

        locationData[country][state].forEach((city) => {
          const option = document.createElement("option");
          option.value = city;
          option.textContent = city;
          if (city === selectedValue) option.selected = true;
          citySelect.appendChild(option);
        });
      }

      function isValidName(value) {
        return /^[A-Za-zÀ-ÿ' -]{2,50}$/.test(value.trim());
      }

      function isValidAddressLine1(value) {
        return /^[A-Za-z0-9À-ÿ'.,\/# -]{5,255}$/.test(value.trim());
      }

      function isValidPostcodeByCountry(postcode, country) {
        const value = postcode.trim();

        const regexMap = {
          "United Kingdom": /^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i,
          "United States": /^\d{5}(-\d{4})?$/,
          "Canada": /^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i,
          "Australia": /^\d{4}$/,
          "Ireland": /^[A-Z0-9]{3}\s?[A-Z0-9]{4}$/i
        };

        if (!regexMap[country]) return value.length >= 3 && value.length <= 20;
        return regexMap[country].test(value);
      }

      function validateAccountForm() {
        const firstName = document.getElementById("first_name").value;
        const lastName = document.getElementById("last_name").value;
        const email = document.getElementById("email").value.trim();
        const address1 = document.getElementById("address_line1").value.trim();
        const country = countrySelect.value;
        const state = stateSelect.value;
        const city = citySelect.value;
        const postcode = document.getElementById("postcode").value.trim();

        if (!isValidName(firstName)) {
          showMessage("Please enter a valid first name.", "error");
          return false;
        }

        if (!isValidName(lastName)) {
          showMessage("Please enter a valid last name.", "error");
          return false;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
          showMessage("Please enter a valid email address.", "error");
          return false;
        }

        const hasAnyAddressField = address1 || country || state || city || postcode;

        if (hasAnyAddressField) {
          if (!isValidAddressLine1(address1)) {
            showMessage("Please enter a valid address line 1.", "error");
            return false;
          }

          if (!country || !locationData[country]) {
            showMessage("Please select a valid country.", "error");
            return false;
          }

          if (!state || !locationData[country][state]) {
            showMessage("Please select a valid state / county.", "error");
            return false;
          }

          if (!city || !locationData[country][state].includes(city)) {
            showMessage("Please select a valid city.", "error");
            return false;
          }

          if (!postcode || !isValidPostcodeByCountry(postcode, country)) {
            showMessage("Please enter a valid postcode.", "error");
            return false;
          }
        }

        return true;
      }

      function getPasswordChecks(password, confirmPassword) {
        return {
          length: password.length >= 8,
          upper: /[A-Z]/.test(password),
          lower: /[a-z]/.test(password),
          number: /\d/.test(password),
          match: password !== "" && password === confirmPassword
        };
      }

      function updatePasswordChecklist() {
        const password = document.getElementById("new_password").value;
        const confirmPassword = document.getElementById("confirm_password").value;

        const checks = getPasswordChecks(password, confirmPassword);

        document.getElementById("pw-length").classList.toggle("valid", checks.length);
        document.getElementById("pw-upper").classList.toggle("valid", checks.upper);
        document.getElementById("pw-lower").classList.toggle("valid", checks.lower);
        document.getElementById("pw-number").classList.toggle("valid", checks.number);
        document.getElementById("pw-match").classList.toggle("valid", checks.match);

        return checks;
      }

      populateCountries(currentCountry);
      populateStates(currentCountry, currentState);
      populateCities(currentCountry, currentState, currentCity);

      countrySelect.addEventListener("change", () => {
        populateStates(countrySelect.value);
      });

      stateSelect.addEventListener("change", () => {
        populateCities(countrySelect.value, stateSelect.value);
      });

      document.getElementById("new_password").addEventListener("input", updatePasswordChecklist);
      document.getElementById("confirm_password").addEventListener("input", updatePasswordChecklist);

      accountForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!validateAccountForm()) {
          return;
        }

        const formData = new FormData(accountForm);

        try {
          const res = await fetch("update_account.php", {
            method: "POST",
            body: formData
          });

          const data = await res.json();
          showMessage(data.message, data.success ? "success" : "error");

          if (data.success) {
            setTimeout(() => window.location.reload(), 900);
          }
        } catch (err) {
          showMessage("Something went wrong while updating your account.", "error");
        }
      });

      passwordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const checks = updatePasswordChecklist();

        if (!checks.length || !checks.upper || !checks.lower || !checks.number) {
          showMessage("Your new password must be at least 8 characters long and include uppercase, lowercase and a number.", "error");
          return;
        }

        if (!checks.match) {
          showMessage("New password and confirm password do not match.", "error");
          return;
        }

        const formData = new FormData(passwordForm);

        try {
          const res = await fetch("change_password.php", {
            method: "POST",
            body: formData
          });

          const data = await res.json();
          showMessage(data.message, data.success ? "success" : "error");

          if (data.success) {
            passwordForm.reset();
            updatePasswordChecklist();
          }
        } catch (err) {
          showMessage("Something went wrong while updating your password.", "error");
        }
      });


document.addEventListener("DOMContentLoaded", () => {

const deleteModal = document.getElementById("deleteModal");
const confirmDeleteBtn = document.getElementById("confirmDelete");
const cancelDeleteBtn = document.getElementById("cancelDelete");

/* open modal */
deleteBtn.addEventListener("click", (e) => {
  e.preventDefault();
  deleteModal.style.display = "flex";
});

/* cancel button */
cancelDeleteBtn.addEventListener("click", () => {
  deleteModal.style.display = "none";
});

/* click outside modal closes it */
deleteModal.addEventListener("click", (e) => {
  if (e.target === deleteModal) {
    deleteModal.style.display = "none";
  }
});

/* confirm delete */
confirmDeleteBtn.addEventListener("click", async () => {

  try {

    const res = await fetch("delete_account.php", {
      method: "POST"
    });

    const data = await res.json();

    if (data.success) {

      alert(data.message);
      window.location.href = "login.html";
      return;

    }

    showMessage(data.message || "Unable to delete account.", "error");

  } catch (err) {

    showMessage("Something went wrong while deleting your account.", "error");

  }

});

});

    </script>
    <div id="deleteModal" style="
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.8);
display:none;
align-items:center;
justify-content:center;
z-index:999;
">

<div style="
background:#111216;
padding:2rem;
border-radius:16px;
border:1px solid rgba(255,255,255,0.08);
max-width:420px;
width:90%;
text-align:center;
box-shadow:0 25px 70px rgba(0,0,0,0.7);
">

<h2 style="margin-bottom:1rem;">Delete Account</h2>

<p style="color:#868995;margin-bottom:2rem;">
Are you sure you want to delete your account?<br><br>
<strong>This action cannot be undone.</strong>
</p>

<div style="display:flex;gap:1rem;justify-content:center">

<button id="cancelDelete" class="primary-btn">
Cancel
</button>

<button id="confirmDelete" class="danger-btn">
Delete Account
</button>

</div>

</div>
</div>
  </body>
</html>