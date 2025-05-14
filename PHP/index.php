<?php 
if (session_start()) {
    session_destroy(); 
} 
require_once('conn.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Portal - Find Freelancers or Jobs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Custom Styles -->
    <style>
        .hero-section {
    background-image: url('../Image/logo/bg-b.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 100vh;
    color: white;
}

        .hero-section h1 {
            font-size: 2.75rem;
            font-weight: 700;
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 30px;
        }
        .category-card img {
            height: 180px;
            object-fit: contain;
        }
        .work-section {
            background-color: rgb(55, 90, 196);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        .work-section a {
            margin-top: 20px;
        }
        a.card-title {
            text-decoration: none;
            color: #212529;
        }
        a.card-title:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <?php include_once('navbar.php') ?>

    <!-- Hero Section -->
<section class="hero-section text-white d-flex align-items-center position-relative py-5">
    <!-- Overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(rgba(55, 90, 196, 0.7), rgba(55, 90, 196, 0.7)), url('../Image/logo/bg-b.png') no-repeat center center / cover; z-index: 1;"></div>

    <!-- Main content container -->
    <div class="container position-relative z-2">
        <div class="row align-items-center">
            <!-- Left Column: Text Content -->
            <div class="col-md-6 text-start">
                <h1 class="display-4 fw-bold">Find the Best Freelancers Right Away</h1>
                <p class="lead">The #1 Site for Remote Jobs</p>
                <a href="find_job.php" class="btn btn-lg text-white" style="background-color: rgb(55, 90, 196);">Find Job</a>
            </div>

            <!-- Right Column: Image -->
            <div class="col-md-6 text-center">
                <img src="../Image/Home/back1.png" alt="Freelancer Woman" class="img-fluid" style="max-height: 500px;">
            </div>
        </div>
    </div>
</section>
    <!-- Popular Categories -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Popular Job Categories</h2>
        <div class="row g-4 justify-content-center">
            <?php
            $sql = "SELECT category, COUNT(category) AS category_count FROM jobtable GROUP BY category ORDER BY category_count DESC LIMIT 5";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ctg = $row['category'];
                    $c1 = match($ctg) {
                        "Graphics" => 'Graphics & Design',
                        "Programming" => 'Programming & Tech',
                        "Digital" => 'Digital Marketing',
                        "Video" => 'Video & Animation',
                        "Writing" => 'Writing & Translation',
                        "Music" => 'Music & Audio',
                        "Business" => 'Business',
                        "AI" => 'AI Services',
                        default => 'New Job Category'
                    };
                    echo "
                        <div class='col-md-4 col-lg-3'>
                            <div class='card category-card h-100 shadow-sm'>
                                <img src='../Image/FT/{$ctg}.png' class='card-img-top' alt='{$ctg}'>
                                <div class='card-body text-center'>
                                    <h5 class='card-title'>
                                        <a href='job_category.php?ctg={$ctg}' class='card-title'>{$c1}</a>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    ";
                }
            } else {
                echo "<p class='text-center'>No categories found.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Work Section -->
    <section class="work-section">
        <div class="container">
            <h2 class="fw-bold">Find Great Work</h2>
            <p class="mb-4">Meet clients you’re excited to work with and take your career or business to new heights.</p>
            <a href="find_job.php" class="btn btn-light btn-lg">Find Work</a>
        </div>
    </section>

    <?php include_once('footer.php') ?>
</body>
</html>
