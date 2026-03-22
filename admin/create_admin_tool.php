<?php
session_start();

require __DIR__ . '/../db_config.php';

if (!isset($_SESSION['user_id']) || 
    $_SESSION['user_role'] !== 'admin' || 
    $_SESSION['user_email'] !== 'admin@example.com') {
    http_response_code(403);
    die("<div style='background: #111216; color: #ff6b6b; padding: 2rem; font-family: sans-serif; text-align: center; margin-top: 50px;'>
            <h2>Unauthorized Access</h2>
            <p>Only the master administrator (admin@example.com) is permitted to use this tool.</p>
         </div>");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $message = "<div style='color: #ff6b6b; margin-bottom: 1rem;'>Please fill in all fields.</div>";
    } else {
        try {
            // 1. Check if the email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = "<div style='color: #ff6b6b; margin-bottom: 1rem;'>Error: A user with this email already exists.</div>";
            } else {
                // 2. Set the temporary password and hash it
                $temp_password = 'admin123';
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

                // 3. Insert the new admin into the database with force_password_reset = 1
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, hashed_password, role, force_password_reset) 
                    VALUES (?, ?, ?, ?, 'admin', 1)
                ");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password]);

                $message = "<div style='background: rgba(81,207,102,0.1); border: 1px solid #51cf66; color: #51cf66; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;'>
                                <strong>✅ Admin Created Successfully!</strong><br><br>
                                <strong>Name:</strong> " . htmlspecialchars($first_name . ' ' . $last_name) . "<br>
                                <strong>Email:</strong> " . htmlspecialchars($email) . "<br>
                                <strong>Temporary Password:</strong> $temp_password<br><br>
                                <em>They will be forced to change this password on their first login.</em>
                            </div>";
            }
        } catch (PDOException $e) {
            $message = "<div style='color: #ff6b6b; margin-bottom: 1rem;'>Database Error: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Tool - Create Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #020203;
            color: #f7f7f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: #111216;
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        h2 { margin-top: 0; color: #d6b372; }
        label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #868995; }
        input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.5);
            color: #fff;
            box-sizing: border-box;
            font-family: inherit;
        }
        input:focus { outline: none; border-color: #d6b372; }
        button {
            width: 100%;
            padding: 0.85rem;
            background: #d6b372;
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            text-transform: uppercase;
        }
        button:hover { background: #e0c28b; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #868995;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Create New Admin</h2>
        <p style="color: #868995; font-size: 0.85rem; margin-bottom: 1.5rem;">
            Use this tool to provision a new administrator account. They will be assigned a temporary password.
        </p>

        <?= $message ?>

        <form method="POST" action="">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Create Admin Account</button>
        </form>

        <a href="dashboard.html" class="back-link">← Back to Dashboard</a>
    </div>

</body>
</html>