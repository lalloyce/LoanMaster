<?php
if (isset($_GET['token'])) {
    // Connect to your database
    $configs = include('config.inc.php');

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Check if token exists
    $stmt = $mysqli->prepare("SELECT * FROM user_accounts WHERE token = ?");
    $stmt->bind_param("s", $_GET['token']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Token doesn't exist, return an error message
        echo "Invalid token.";
    } else {
        // Token exists, verify the user
        $stmt = $mysqli->prepare("UPDATE user_accounts SET verified = 1, token = NULL WHERE token = ?");
        $stmt->bind_param("s", $_GET['token']);
        $stmt->execute();

        echo "Email verified successfully.";
    }

    $stmt->close();
    $mysqli->close();
}
?>