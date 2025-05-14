<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Categories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .custom-navbar {
            background-color: rgb(85, 182, 243) !important;
        }
        .nav-link{
            color: aliceblue;
        }
    </style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Job Categories</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#categoryNavbar" aria-controls="categoryNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="categoryNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Graphics">Graphics & Design</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Programming">Programming & Tech</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Digital">Digital Marketing</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Video">Video & Animation</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Writing">Writing & Translation</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Music">Music & Audio</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=Business">Business</a></li>
                <li class="nav-item"><a class="nav-link" href="login_job_category.php?ctg=AI">AI Services</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="login_job_category.php?ctg=New">New*</a></li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
