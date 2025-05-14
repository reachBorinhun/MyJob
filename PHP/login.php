<?php
include("conn.php");
session_start(); // Start the session here only once

$error_message = ""; // Initialize for displaying errors in HTML

// Check if the form is submitted
if (isset($_POST["submit"])) {
    $email = $_POST["email"]; // Consider converting to lowercase: strtolower($_POST["email"]);
    $password = $_POST["password"];

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM users WHERE email = ?"; // Consider WHERE LOWER(email) = ? if storing emails lowercase
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        // Handle prepare error (important for debugging)
        error_log("MySQLi Prepare Error: " . mysqli_error($conn));
        $error_message = "An error occurred. Please try again later.";
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $row = mysqli_fetch_assoc($result);

            if ($row) { // User found
                if (password_verify($password, $row["password"])) { // Password matches
                    // --- SUCCESSFUL LOGIN ---
                    // Set session variables
                    $_SESSION['id'] = $row['userid'];
                    $_SESSION['fName'] = $row['fName'];
                    $_SESSION['lName'] = $row['lName'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['image'] = $row['image']; // Ensure this path/data is appropriate for session
                    $_SESSION['role'] = $row['role'];

                    // Role-based redirection
                    if ($row["role"] == 'admin') {
                        mysqli_close($conn); // Close connection before redirect
                        // *** THIS IS THE LINE YOU WANT CHANGED ***
                        header('location:admin/admin_dashbord.php'); // Redirect admin to admin_dashboard.php
                        exit();
                    } elseif ($row["role"] == 'user') {
                        mysqli_close($conn); // Close connection before redirect
                        header('location:user/home.php'); // Redirect user to user/home.php
                        exit();
                    } else {
                        // Handle unknown or unassigned role
                        mysqli_close($conn);
                        error_log("Login attempt with unhandled role: '" . $row["role"] . "' for user ID: " . $row['userid']);
                        $error_message = "Access denied for your account type.";
                    }
                } else {
                    // Incorrect password
                    $error_message = "Invalid email or password.";
                }
            } else {
                // User not found (email does not exist)
                $error_message = "Invalid email or password.";
            }
        } else {
            // Query execution failed
            error_log("MySQLi Get Result Error: " . mysqli_error($conn));
            $error_message = "An error occurred during login. Please try again.";
        }
        mysqli_stmt_close($stmt); // Close statement here after getting result
    }
}
// Only close connection here if not closed already (e.g., on error before redirection or if form not submitted)
// If the script exits due to redirection, this line might not be reached.
if ($conn && (!isset($_POST["submit"]) || !empty($error_message))) {
     mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../CSS/login.css"> <!-- Make sure this path is correct -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Login Container -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container p-4 shadow rounded bg-white" style="width: 100%; max-width: 400px; position: relative;">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="index.php"> <!-- Make sure index.php is in the same directory or adjust path -->
                    <img src="../Image/logo/ITJobs.png" alt="logo" style="max-height: 40px;">
                </a>
                <a href="index.php"> <!-- Make sure index.php is in the same directory or adjust path -->
                    <img src="../Image/logo/X.png" alt="close" style="width: 20px; height: 20px;">
                </a>
            </div>

            <div class="text-center mb-3">
                <h5 class="text-muted">Login to ITJobs</h5>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger text-center p-2" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form (action should point to this login script) -->
            <form class="loginform" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">Email</label> <!-- Changed id from username to email -->
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="mb-2 text-start">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <div class="mb-3 text-end">
                    <a href="froget_password.php" class="small text-decoration-none">Forgot password?</a> <!-- Ensure this file exists or adjust path -->
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="submit" class="btn btn-primary">Login</button>
                </div>
            </form>

            <p class="small text-danger text-center">
                -- Don't you have an ITJobs account?
                <a href="signup.php" class="text-decoration-underline">Sign Up</a> <!-- Ensure this file exists or adjust path -->
            </p>
        </div>
    </div>
</body>
</html>