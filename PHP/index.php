<?php if (session_start()) { session_destroy(); } ?>
<?php require_once('conn.php') ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Job Portal - Find Freelancers or Jobs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include_once('navbar.php') ?>

    <!-- Hero Section -->
    <div class="container py-5 text-center bg-light">
        <h1 class="display-4 fw-bold">Find the Best Freelancers Right Away</h1>
        <p class="lead">The #1 Site for Remote Jobs</p>
        <a href="find_job.php" class="btn btn-success btn-lg">Find Job</a>
    </div>

    <!-- Popular Categories -->
    <div class="container my-5">
        <h2 class="mb-4 text-center">Popular Job Categories</h2>
        <div class="row g-4">
            <?php
            $sql = "SELECT category, count(category) AS category_count FROM jobtable GROUP BY category ORDER BY category_count DESC LIMIT 5";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
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
                        <div class='col-md-4'>
                            <div class='card h-100 text-center'>
                                <img src='../Image/FT/{$ctg}.png' class='card-img-top' alt='{$ctg}' style='height:200px; object-fit:contain;'>
                                <div class='card-body'>
                                    <h5 class='card-title'><a href='job_category.php?ctg={$ctg}'>{$c1}</a></h5>
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
    </div>

    <!-- Work Section -->
    <div class="container-fluid bg-secondary text-white text-center py-5">
        <h2>Find Great Work</h2>
        <p class="mb-4">Meet clients you’re excited to work with and take your career or business to new heights.</p>
        <a href="find_job.php" class="btn btn-light">Find Work</a>
    </div>

    <!-- Talent Section -->
    <div class="container-fluid bg-light text-center py-5">
        <h2>Find Talent Your Way</h2>
        <p class="mb-4">Work with the largest network of independent professionals and get things done from quick turnarounds to big transformations.</p>
        <a href="find_freelancer.Notlogin.php" class="btn btn-primary">Post Your Job</a>
    </div>

    <?php include_once('footer.php') ?>
</body>
</html>
