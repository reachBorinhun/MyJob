<?php $active4 = "active"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITJob</title>

    <!-- ✅ Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Font Awesome (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .disc {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .logo {
            width: 150px;
            height: auto;
            margin: 15px 0;
        }

        h2 {
            color: #007bff;
            margin-top: 20px;
        }

        ul {
            list-style-type: disc;
            padding-left: 20px;
        }

        ul li {
            margin-bottom: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 700px) {
            .disc {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <?php include_once("navbar.php") ?>

    <div class="disc">
        <h2>Why Choose ITJob?</h2>
        <img src="../Image/logo/ITJobs.png" alt="ITJob Logo" class="logo">

        <ul>
            <li><strong>Diverse Job Listings:</strong> Explore a wide variety of opportunities in tech and beyond.</li>
            <li><strong>User-Friendly Interface:</strong> Designed to make your job search easy and efficient.</li>
            <li><strong>Personalized Recommendations:</strong> Let us help you find the right job, faster.</li>
            <li><strong>Connect with Employers:</strong> Chat directly and professionally within the platform.</li>
            <li><strong>Skill Development:</strong> Access resources to grow your capabilities and stay competitive.</li>
            <li><strong>Mobile Friendly:</strong> Job hunt anytime, anywhere from your phone.</li>
        </ul>

        <h2>Join Our Thriving Community</h2>
        <p>ITJob isn’t just a platform — it’s a network of tech professionals growing and succeeding together. Share your knowledge, ask questions, and grow your career in a supportive environment.</p>

        <p>Ready to level up your career? <a href="signup.php">Create your ITJob account</a> today and unlock new possibilities!</p>
    </div>

    <?php include_once("footer.php") ?>

    <!-- ✅ Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 <!-- #region -->   