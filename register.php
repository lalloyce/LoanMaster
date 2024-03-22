<?php
// register.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to your database
    $configs = include('config.inc.php');

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Check if email already exists
    $stmt = $mysqli->prepare("SELECT * FROM user_accounts WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already exists, return an error message
        echo "Email already exists.";
    } else {
        // Check password strength
        $password = $_POST['password'];
        if (strlen($password) < 8) {
            echo "Password should be at least 8 characters.";
        } else if (!preg_match('/[A-Z]/', $password)) {
            echo "Password should contain at least one uppercase letter.";
        } else if (!preg_match('/[a-z]/', $password)) {
            echo "Password should contain at least one lowercase letter.";
        } else if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            echo "Password should contain at least one special character.";
        } else {
            // Password is strong, insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(50)); // Generate a verification token
            $stmt = $mysqli->prepare("INSERT INTO user_accounts (email, password, token) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_POST['email'], $hashed_password, $token);
            $stmt->execute();

            // Send the verification link to the user's email
            $verification_link = "localhost/verify.php?token=" . $token;
            mail($_POST['email'], "Email Verification", "Click this link to verify your email: " . $verification_link);

            echo "User registered successfully. A verification link has been sent to your email.";
        }
    }

    $stmt->close();
    $mysqli->close();
}
?>