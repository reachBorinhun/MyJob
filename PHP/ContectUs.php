<?php
session_start(); // Proper session start
require_once('conn.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us - Job Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .contact-section {
            padding: 60px 0;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .info-icon {
            color: #0d6efd;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <?php include_once('navbar.php'); ?>

    <section class="contact-section container">
        <div class="row mb-5 text-center">
            <div class="col">
                <h2 class="fw-bold">Contact Us</h2>
                <p class="text-muted">We'd love to hear from you! Whether you're a job seeker or employer, reach out anytime.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-md-6">
                <div class="contact-form">
                    <form method="post" action="#">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter your name" required <?php if (!isset($_SESSION['id'])) echo 'disabled'; ?>>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter your email" required <?php if (!isset($_SESSION['id'])) echo 'disabled'; ?>>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea class="form-control" id="message" rows="5" placeholder="Type your message here..." required <?php if (!isset($_SESSION['id'])) echo 'disabled'; ?>></textarea>
                        </div>
                        <?php if (isset($_SESSION['id'])): ?>
                            <button type="submit" class="btn btn-primary w-100">Send Message</button>
                        <?php else: ?>
                            <div class="alert alert-warning text-center" role="alert">
                                Please <a href="login.php">log in</a> to send us a message.
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-md-6">
                <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                    <h5 class="mb-4 fw-semibold">Our Office</h5>
                    <p><i class="fa fa-map-marker info-icon"></i> Phnom Penh, Cambodia</p>
                    <p><i class="fa fa-envelope info-icon"></i> support@jobportal.com</p>
                    <p><i class="fa fa-phone info-icon"></i> +855 12 345 678</p>

                    <hr>

                    <h6 class="fw-semibold">Business Hours</h6>
                    <p>Mon - Fri: 9:00 AM - 5:00 PM</p>
                    <p>Sat - Sun: Closed</p>

                    <hr>
                            
                    <h6 class="fw-semibold">Follow Us</h6>
                    <a href="#" class="me-2"><i class="fa fa-facebook-official fa-2x"></i></a>
                    <a href="#" class="me-2"><i class="fa fa-twitter fa-2x"></i></a>
                    <a href="#"><i class="fa fa-linkedin fa-2x"></i></a>
                </div>
            </div>
        </div>
    </section>

    <?php include_once('footer.php'); ?>
</body>

</html>
