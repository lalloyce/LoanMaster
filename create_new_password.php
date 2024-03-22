<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to your database
   $configs = include('config.inc.php');

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Check if token exists
    $stmt = $mysqli->prepare("SELECT * FROM user_accounts WHERE reset_token = ?");
    $stmt->bind_param("s", $_GET['token']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Token doesn't exist, return an error message
        echo "Invalid token.";
    } else {
        // Token exists, update the user's password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE user_accounts SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $_GET['token']);
        $stmt->execute();

        echo "Password reset successfully.";
    }

    $stmt->close();
    $mysqli->close();
}
?>

<script>
    function checkPasswordStrength(password) {
        var strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        return strength >= 5;
    }

    document.getElementById('password').addEventListener('input', function(e) {
        if (checkPasswordStrength(e.target.value)) {
            // Password is strong
            document.getElementById('password_strength').textContent = 'Password is strong';
        } else {
            // Password is not strong
            document.getElementById('password_strength').textContent = 'Password is not strong enough';
        }
    });
</script>

<form method="post" action="create_new_password.php?token=<?php echo $_GET['token']; ?>">
    <label for="password">New Password:</label>
    <input type="password" id="password" name="password">
    <span id="password_strength"></span>
    <input type="submit" value="Reset Password">
</form>