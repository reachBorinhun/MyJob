<?php
session_start(); // Start the session at the very top
include("../conn.php"); // Your database connection

$success_message = '';
$error_message = '';
$form_data = ['name' => '', 'email' => '', 'message' => '']; // To repopulate form on error

// If user is logged in, prefill name and email (optional)
if (isset($_SESSION['id'])) {
    // You might want to fetch fName, lName if they are separate in your session/db for 'name'
    $form_data['name'] = isset($_SESSION['fName']) ? $_SESSION['fName'] . (isset($_SESSION['lName']) ? ' ' . $_SESSION['lName'] : '') : '';
    $form_data['email'] = isset($_SESSION['email']) ? $_SESSION['email'] : '';
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // Get and sanitize form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message_content = trim($_POST['message'] ?? '');
    $current_user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null; // Get logged-in user's ID if available

    // Store submitted data for repopulation in case of error
    $form_data = ['name' => $name, 'email' => $email, 'message' => $message_content];

    // Basic Validation
    if (empty($name) || empty($email) || empty($message_content)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } else {
        // Prepare and execute the SQL query to insert the message
        $sql = "INSERT INTO contact_messages (name, email, message, user_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $message_content, $current_user_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Your message has been sent successfully! We will get back to you shortly.";
                // Clear form data on success
                $form_data = ['name' => (isset($_SESSION['id']) ? $_SESSION['fName'] . (isset($_SESSION['lName']) ? ' ' . $_SESSION['lName'] : '') : ''), 'email' => (isset($_SESSION['id']) ? $_SESSION['email'] : ''), 'message' => ''];
            } else {
                $_SESSION['error_message'] = "Error sending message: " . htmlspecialchars(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error_message'] = "Error preparing statement: " . htmlspecialchars(mysqli_error($conn));
        }
    }
    // Redirect to the same page to prevent form resubmission on refresh (PRG pattern)
    // and to display session messages
    header("Location: " . $_SERVER['PHP_SELF']); // Or specific filename e.g., contact_us.php
    exit();
}

// Retrieve flash messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear message after displaying
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear message after displaying
}

// If there was an error and we redirected, form_data might be in session
// For simplicity in this example, we are re-initializing $form_data above.
// A more robust way would be to pass form_data through session on error.
// For now, the prefill logic at the top handles logged-in users or empty fields.


// mysqli_close($conn); // Close connection at the very end if appropriate
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us - WorkWise</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome (CDN for common icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS (ensure version 5.x if using v5 classes) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Your Custom CSS if any (can override Bootstrap or add specific styles) -->
    <link rel="stylesheet" href="../../CSS/index.css"> <!-- Make sure this path is correct -->

    <style>
        body {
            background-color: #f8f9fa; /* Light gray background for the page */
            color: #333; /* Default text color */
        }
        .contact-section {
            padding: 50px 0;
        }
        .contact-form-container {
            background: white;
            padding: 30px;
            border-radius: 8px; /* Softer corners */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        .contact-info-container {
            background: #ffffff; /* Or a slightly different shade like #f0f5ff for contrast */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            height: 100%; /* Make info box same height as form on larger screens */
        }
        .btn-custom-primary {
            background-color: rgb(55, 90, 196); /* Your main color */
            border-color: rgb(55, 90, 196);
            color: white;
        }
        .btn-custom-primary:hover {
            background-color: rgb(45, 80, 186); /* Darker shade on hover */
            border-color: rgb(45, 80, 186);
            color: white;
        }
        .info-icon {
            color: rgb(55, 90, 196); /* Main color for icons */
            font-size: 1.2rem; /* Slightly smaller for info section */
            margin-right: 10px;
        }
        .page-title {
            color: rgb(55, 90, 196); /* Main color for the title */
            font-weight: bold;
        }
        .social-icons a i {
            color: rgb(55, 90, 196);
            transition: color 0.3s ease;
        }
        .social-icons a:hover i {
            color: rgb(45, 80, 186); /* Darker shade on hover */
        }
    </style>
</head>

<body>

    <?php include_once('login_navbar.php') // Ensure this path is correct and navbar is styled for Bootstrap 5 ?>

    <section class="contact-section container">
        <div class="row mb-5 text-center">
            <div class="col">
                <h2 class="page-title">Contact Us</h2>
                <p class="text-muted">We'd love to hear from you! Whether you have a question or just want to say hi, reach out anytime.</p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 justify-content-center">
            <!-- Contact Form -->
            <div class="col-lg-6 col-md-8">
                <div class="contact-form-container">
                    <h4 class="mb-4 text-center">Send us a Message</h4>
                    <!-- IMPORTANT: action should point to this script itself, method="post" -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name"
                                   value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Type your message here..." required><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        </div>
                        <button type="submit" name="submit_contact" class="btn btn-custom-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-8 mt-4 mt-lg-0">
                <div class="contact-info-container">
                    <h5 class="mb-4 fw-semibold">Our Office</h5>
                    <p><i class="fas fa-map-marker-alt info-icon"></i> Phnom Penh, Cambodia</p>
                    <p><i class="fas fa-envelope info-icon"></i> support@workwise.com</p> <!-- Changed to workwise -->
                    <p><i class="fas fa-phone info-icon"></i> +855 12 345 678</p>

                    <hr class="my-4">

                    <h6 class="fw-semibold">Business Hours</h6>
                    <p>Mon - Fri: 9:00 AM - 5:00 PM</p>
                    <p>Sat - Sun: Closed</p>

                    <hr class="my-4">

                    <h6 class="fw-semibold">Follow Us</h6>
                    <div class="social-icons">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f fa-2x"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in fa-2x"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>