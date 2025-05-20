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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(55, 90, 196, 0.75), rgba(55, 90, 196, 0.75)), url('../Image/logo/bg-b.png') center/cover no-repeat;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
        }
        .hero-section h1 {
            font-size: 3rem;
        }
        .category-card img {
            height: 160px;
            object-fit: contain;
        }
        .work-section {
            background-color: rgb(55, 90, 196);
            color: white;
            padding: 60px 20px;
        }
        .how-it-works i {
            font-size: 2.5rem;
            color: rgb(55, 90, 196);
        }
        .testimonial {
            background-color: #f8f9fa;
            padding: 60px 20px;
        }
        .testimonial .card {
            border: none;
            background: transparent;
        }
    </style>
</head>
<body>

<?php include_once('navbar.php') ?>

<!-- Hero Section -->
<section class="hero-section py-5" style="background-color: #2C3E50; color: #fff;">
    <div class="container">
        <div class="row align-items-center">
            <!-- Text Content -->
            <div class="col-md-6 text-start">
                <h1 class="fw-bold mb-4" style="font-size: 2.75rem;">Discover Your Dream Job or the Right Talent</h1>
                <p class="lead mb-4">Join thousands who are hiring or getting hired. Verified freelancers, secure processes, and endless opportunities.</p>
                <a href="find_job.php" class="btn btn-lg px-5 py-1" style="background-color: #F1C40F; color: #2C3E50; font-weight: bold; border: none; border-radius: 8px;">
                    Find a Job
                </a>
            </div>

            <!-- Image -->
            <div class="col-md-6 text-center">
                <img src="../Image/Home/back1.png" alt="Freelancer" class="img-fluid" style="max-height: 480px;">
            </div>
        </div>
    </div>
</section>


<!-- How It Works -->
<section class="py-5 text-center how-it-works">
    <div class="container">
        <h2 class="fw-bold mb-5">How It Works</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <i class="fas fa-user-plus"></i>
                <h5 class="mt-3">1. Create Your Account</h5>
                <p>Sign up as a job seeker or employer in just a few minutes.</p>
            </div>
            <div class="col-md-4">
                <i class="fas fa-briefcase"></i>
                <h5 class="mt-3">2. Post or Apply for Jobs</h5>
                <p>Employers post jobs. Freelancers browse and apply to what suits them best.</p>
            </div>
            <div class="col-md-4">
                <i class="fas fa-handshake"></i>
                <h5 class="mt-3">3. Get Hired or Hire</h5>
                <p>Work with trusted people and get the job done successfully.</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Categories -->
<section class="container my-5">
    <h2 class="text-center mb-4">Popular Job Categories</h2>
    <div class="row g-4 justify-content-center">
        <?php
        $sql = "SELECT category, COUNT(category) AS category_count FROM jobtable GROUP BY category ORDER BY category_count DESC LIMIT 6";
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
                                    <a href='job_category.php?ctg={$ctg}' class='card-title text-decoration-none'>{$c1}</a>
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

<!-- Featured Jobs -->
<section class="container my-5">
    <h2 class="text-center mb-4">Featured Jobs</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Front-End Developer</h5>
                    <p class="card-text">Looking for a skilled front-end developer with React.js experience.</p>
                    <a href="find_job.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Logo Designer</h5>
                    <p class="card-text">Need a modern, minimalist logo for a startup brand.</p>
                    <a href="find_job.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Content Writer</h5>
                    <p class="card-text">Require a blog writer for tech-related articles (SEO-friendly).</p>
                    <a href="find_job.php" class="btn btn-sm btn-outline-primary">Apply Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonial">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">What Users Say</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 text-center">
            <div class="col">
                <div class="card p-3">
                    <p>"I found my first freelance gig here within days. Simple, fast, and professional!"</p>
                    <small>- Sothy, Freelancer</small>
                </div>
            </div>
            <div class="col">
                <div class="card p-3">
                    <p>"As an employer, I hired a graphic designer in under 48 hours. Highly recommend."</p>
                    <small>- Dara, Business Owner</small>
                </div>
            </div>
            <div class="col">
                <div class="card p-3">
                    <p>"The platform is intuitive and full of opportunities. I visit it daily!"</p>
                    <small>- Lina, Content Writer</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Work Section -->
<section class="work-section text-center">
    <div class="container">
        <h2 class="fw-bold">Start Your Journey Today</h2>
        <p>Whether you're hiring or job-hunting, we’re here to help you succeed.</p>
        <a href="signup.php" class="btn btn-light btn-lg">Join Now</a>
    </div>
</section>

<?php include_once('footer.php') ?>

</body>
</html>
