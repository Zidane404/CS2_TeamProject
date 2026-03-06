<?php
session_start();
header('Content-Type: application/json');

require_once 'db_config.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in.'
    ]);
    exit;
}

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

$firstName    = trim($_POST['first_name'] ?? '');
$lastName     = trim($_POST['last_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$addressLine1 = trim($_POST['address_line1'] ?? '');
$addressLine2 = trim($_POST['address_line2'] ?? '');
$city         = trim($_POST['city'] ?? '');
$state        = trim($_POST['state'] ?? '');
$postcode     = trim($_POST['postcode'] ?? '');
$country      = trim($_POST['country'] ?? '');

function isValidNameValue(string $value): bool
{
    return (bool) preg_match("/^[A-Za-zÀ-ÿ' -]{2,50}$/u", $value);
}

function isValidAddressLine1Value(string $value): bool
{
    return (bool) preg_match("/^[A-Za-z0-9À-ÿ'.,\/# -]{5,255}$/u", $value);
}

function isValidPostcodeByCountry(string $postcode, string $country): bool
{
    $patterns = [
        'United Kingdom' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
        'United States'  => '/^\d{5}(-\d{4})?$/',
        'Canada'         => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i',
        'Australia'      => '/^\d{4}$/',
        'Ireland'        => '/^[A-Z0-9]{3}\s?[A-Z0-9]{4}$/i'
    ];

    if (!isset($patterns[$country])) {
        return strlen($postcode) >= 3 && strlen($postcode) <= 20;
    }

    return (bool) preg_match($patterns[$country], $postcode);
}

if (!isValidNameValue($firstName)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid first name.'
    ]);
    exit;
}

if (!isValidNameValue($lastName)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid last name.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

$hasAnyAddressData = (
    $addressLine1 !== '' ||
    $addressLine2 !== '' ||
    $city !== '' ||
    $state !== '' ||
    $postcode !== '' ||
    $country !== ''
);

if ($hasAnyAddressData) {
    if (!isValidAddressLine1Value($addressLine1)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid address line 1.'
        ]);
        exit;
    }

    if (!isset($locationData[$country])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid country.'
        ]);
        exit;
    }

    if (!isset($locationData[$country][$state])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid state / county.'
        ]);
        exit;
    }

    if (!in_array($city, $locationData[$country][$state], true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please select a valid city.'
        ]);
        exit;
    }

    if (!isValidPostcodeByCountry($postcode, $country)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid postcode.'
        ]);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE email = ?
          AND user_id <> ?
        LIMIT 1
    ");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'That email address is already in use.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE users
        SET first_name = ?,
            last_name = ?,
            email = ?,
            updated_at = NOW()
        WHERE user_id = ?
          AND deleted_at IS NULL
    ");
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $userId
    ]);

    $stmt = $pdo->prepare("
        SELECT address_id
        FROM addresses
        WHERE user_id = ?
          AND (is_default_shipping = 1 OR is_default_billing = 1)
        ORDER BY address_id ASC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAddress && $hasAnyAddressData) {
        $stmt = $pdo->prepare("
            UPDATE addresses
            SET address_line1 = ?,
                address_line2 = ?,
                city = ?,
                state = ?,
                postcode = ?,
                country = ?
            WHERE address_id = ?
        ");
        $stmt->execute([
            $addressLine1,
            $addressLine2 !== '' ? $addressLine2 : null,
            $city,
            $state,
            $postcode,
            $country,
            $existingAddress['address_id']
        ]);
    } elseif (!$existingAddress && $hasAnyAddressData) {
        $stmt = $pdo->prepare("
            INSERT INTO addresses (
                user_id,
                label,
                address_line1,
                address_line2,
                city,
                state,
                postcode,
                country,
                is_default_shipping,
                is_default_billing,
                created_at
            ) VALUES (?, 'Home', ?, ?, ?, ?, ?, ?, 1, 1, NOW())
        ");
        $stmt->execute([
            $userId,
            $addressLine1,
            $addressLine2 !== '' ? $addressLine2 : null,
            $city,
            $state,
            $postcode,
            $country
        ]);
    } elseif ($existingAddress && !$hasAnyAddressData) {
        $stmt = $pdo->prepare("
            DELETE FROM addresses
            WHERE address_id = ?
        ");
        $stmt->execute([$existingAddress['address_id']]);
    }

    $_SESSION['first_name'] = $firstName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Account updated successfully.'
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}