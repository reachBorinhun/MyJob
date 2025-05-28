    <?php
    include("../conn.php");
    session_start(); // Ensure session_start() is at the very beginning
    ?>

    <?php $active2 = "active"; ?>

    <?php
    $note = "";
    $ctg = isset($_GET["ctg"]) ? mysqli_real_escape_string($conn, $_GET["ctg"]) : ''; // Sanitize ctg

    if (empty($ctg)) {
        // Handle missing category, redirect or show error
        // For now, let's assume ctg will always be provided.
        // If not, the page might not function as expected.
        // echo "Category not specified."; exit;
    }

    $current_search_term = isset($_GET["search"]) ? $_GET["search"] : '';
    $current_filter_type = isset($_GET["filter"]) ? $_GET["filter"] : "All Type"; // Default to "All Type" if empty or not set

    $search_safe = mysqli_real_escape_string($conn, $current_search_term);

    // Base SQL for the category
    $base_sql = "SELECT * FROM jobtable WHERE category='$ctg'";
    $conditions = [];

    if (!empty($search_safe)) {
        $conditions[] = "(jobId LIKE '%$search_safe%' OR title LIKE '%$search_safe%' OR company LIKE '%$search_safe%' OR location LIKE '%$search_safe%' OR price LIKE '%$search_safe%' OR exitDay LIKE '%$search_safe%')";
    }

    if ($current_filter_type === "Full Time" || $current_filter_type === "Part Time") {
        $conditions[] = "jobType = '" . mysqli_real_escape_string($conn, $current_filter_type) . "'";
    }

    if (!empty($conditions)) {
        $sql = $base_sql . " AND " . implode(" AND ", $conditions);
    } else {
        $sql = $base_sql;
    }

    $result_jobs = mysqli_query($conn, $sql);

    if ($result_jobs === false) {
        $note = "Error retrieving data: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result_jobs) == 0) {
        if (!empty($current_search_term) || ($current_filter_type !== "All Type" && !empty($current_filter_type))) {
            $note = "No jobs found matching your criteria in this category.";
        } else {
            $note = "No jobs found in this category.";
        }
    }

    // Hero banner content logic (UNCHANGED)
    $c1 = 'Category'; // Default
    $c2 = 'Explore jobs in this category.'; // Default
    if ($ctg == 'Graphics') {
        $c1 = 'Graphics & Design';
        $c2 = 'Designs to make you stand out';
    } elseif ($ctg == 'Programming') {
        $c1 = 'Programming & Tech';
        $c2 = 'You think it. A programmer develops it';
    } else if ($ctg == 'Digital') {
        $c1 = 'Digital Marketing';
        $c2 = 'Build your brand. Grow your business.';
    } elseif ($ctg == 'Video') {
        $c1 = 'Video & Animation';
        $c2 = 'Bring your story to life with creative videos.';
    } elseif ($ctg == 'Writing') {
        $c1 = 'Writing & Translation';
        $c2 = 'Get your words across—in any language.';
    } elseif ($ctg == 'Music') {
        $c1 = 'Music & Audio';
        $c2 = 'Do not miss a beat. Bring your sound to life.';
    } elseif ($ctg == 'Business') {
        $c1 = 'Business';
        $c2 = 'Business to make you stand out';
    } elseif ($ctg == 'AI') {
        $c1 = 'AI Services';
        $c2 = 'AI to make you stand out';
    } else {
        // Use the raw category name if not in the predefined list
        $c1 = htmlspecialchars(ucfirst($ctg));
        $c2 = 'Discover opportunities in ' . htmlspecialchars(ucfirst($ctg)) . '.';
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($c1); ?> Jobs</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body { background-color: #f8f9fa; }
            .hero-banner {
                /* Retain existing hero banner style, maybe add a default bg if image fails */
                background-image: url('../../Image/Home/ctg2.jpg'); 
                background-size: cover;
                background-position: center center;
                color: white; /* Ensure text is readable */
            }
            .hero-banner h1, .hero-banner p {
                text-shadow: 1px 1px 3px rgba(0,0,0,0.5); /* Improve readability on varied backgrounds */
            }

            .job-card-wrapper { margin-bottom: 2rem; }
            .job-card {
                border-radius: 15px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                overflow: hidden; 
                background-color: #fff;
                height: 100%; 
                display: flex;
                flex-direction: column;
            }
            .job-card .card-img-top-container { 
                position: relative; 
                padding: 1.25rem; 
                background-color: #f8f9fa; 
                height: 220px; 
            }
            .job-card .card-img-top {
                width: 100%;
                height: 100%; 
                object-fit: cover;
                border-radius: 8px; 
            }
            
            .job-card .job-title-overlay {
                position: absolute;
                bottom: 1.25rem; 
                left: 1.25rem;   
                background-color: rgba(255, 255, 255, 0.92); color: #0d6efd;
                padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold;
                font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.15);
                max-width: calc(100% - (2 * 1.25rem)); 
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .job-card .card-body {
                padding: 1.25rem; flex-grow: 1; display: flex; flex-direction: column;
            }
            .job-card .detail-item {
                display: flex; align-items: center; margin-bottom: 0.8rem; font-size: 0.9rem;
            }
            .job-card .detail-item i.fa-fw {
                color: #6c757d; margin-right: 10px; width: 1.28571429em; text-align: center;
            }
            .job-card .detail-item .detail-label { color: #6c757d; margin-right: 5px; }
            .job-card .detail-item .detail-value { color: #212529; font-weight: 500; }
            .job-card .salary-badge {
                background-color: #e9ecef; padding: 5px 10px; border-radius: 6px;
                font-weight: bold; color: #212529; font-size: 0.9em;
            }
            .job-card .btn-more-details {
                border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; margin-top: auto; 
            }
            .job-card .btn-more-details i { margin-left: 5px; }
            
            .job-card .placeholder-text p {
                font-size: 0.8em; color: red; padding: 10px; text-align: center;
                background-color: #fff3cd; border: 1px solid #ffeeba;
                border-radius: .25rem; margin-top: 10px; margin-bottom: 10px;
            }

            #phpmg { text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; color: #333; }
            
            .search-filter-form .form-control, 
            .search-filter-form .form-select {
                /* Bootstrap 5 defaults are usually fine */
            }
        </style>
    </head>

    <body>

        <?php include_once("admin_navbar.php"); ?>
        <?php include_once("admin_ctg_bar.php"); ?>

        <div class="container-fluid p-0"> <!-- Use container-fluid for full-width hero -->
            <div class="p-5 rounded-0 bg-dark text-white text-center mb-4 hero-banner">
                <h1 class="display-4"><?php echo htmlspecialchars($c1); ?></h1>
                <p class="lead fw-bold"><?php echo htmlspecialchars($c2); ?></p>
            </div>
        </div>

        <div class="container mt-4">
            <form class="mb-4 search-filter-form" method="get" action="admin_job_category.php">
                <div class="row justify-content-center g-3">
                    <div class="col-md-5 col-lg-4">
                        <input type="search" name="search" value="<?= htmlspecialchars($current_search_term) ?>" class="form-control" placeholder="Search jobs in <?php echo htmlspecialchars($c1); ?>...">
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <select name="filter" class="form-select">
                            <option value="All Type" <?= ($current_filter_type == "All Type" || $current_filter_type == "") ? 'selected' : '' ?>>All Job Types</option>
                            <option value="Full Time" <?= $current_filter_type == "Full Time" ? 'selected' : '' ?>>Full Time</option>
                            <option value="Part Time" <?= $current_filter_type == "Part Time" ? 'selected' : '' ?>>Part Time</option>
                        </select>
                    </div>
                    <input type="hidden" name="ctg" value="<?= htmlspecialchars($ctg) ?>">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                    </div>
                </div>
            </form>
            
            <h3 id="phpmg">
                <?php
                if (!empty($current_search_term) || ($current_filter_type !== 'All Type' && !empty($current_filter_type))) {
                    $status_msg = "Showing results";
                    if (!empty($current_search_term)) $status_msg .= " for \"" . htmlspecialchars($current_search_term) . "\"";
                    if ($current_filter_type !== 'All Type' && !empty($current_filter_type)) $status_msg .= " (Type: " . htmlspecialchars($current_filter_type) . ")";
                    echo $status_msg . " in " . htmlspecialchars($c1);
                } else {
                    echo "All Jobs in " . htmlspecialchars($c1);
                }
                ?>
            </h3>

            <div class="row">
                <?php
                if ($result_jobs && mysqli_num_rows($result_jobs) > 0) :
                    while ($row = mysqli_fetch_assoc($result_jobs)) :
                        $image_base_path = "../../"; 
                        $default_placeholder_url = $image_base_path . "image/uploads/placeholder.png";
                        $full_image_url = $default_placeholder_url;
                        $job_image_db_path_part = ""; 
                        $display_image = false;
                        $image_not_found_message = "";

                        if (!empty($row['job_image'])) {
                            $job_image_db_path_part = htmlspecialchars($row['job_image']);
                            $candidate_full_image_url = $image_base_path . $job_image_db_path_part;
                            $server_path_to_check_display = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $job_image_db_path_part; // Adjust '/WorkWise/' if needed
                            
                            if (file_exists($server_path_to_check_display)) {
                                $full_image_url = $candidate_full_image_url;
                                $display_image = true;
                            } else {
                                $image_not_found_message = "Image file not found on server: " . $job_image_db_path_part;
                            }
                        } else {
                            $image_not_found_message = "No image provided for this job.";
                        }
                ?>
                        <div class="col-lg-4 col-md-6 job-card-wrapper">
                            <div class="card job-card h-100">
                                <div class="card-img-top-container">
                                    <img src="<?php echo $full_image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?> image">
                                    <div class="job-title-overlay">
                                        <?php echo htmlspecialchars($row["title"]); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (!$display_image && !empty($image_not_found_message)): ?>
                                        <div class="placeholder-text">
                                            <p><small><?php echo htmlspecialchars($image_not_found_message); ?></small></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="detail-item">
                                        <i class="fas fa-briefcase fa-fw"></i>
                                        <span class="detail-label">Job type:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['jobType']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-building fa-fw"></i>
                                        <span class="detail-label">Company:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['company']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt fa-fw"></i>
                                        <span class="detail-label">Location:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['location']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-dollar-sign fa-fw"></i>
                                        <span class="detail-label">Salary:</span>
                                        <span class="salary-badge ms-1">$<?php echo htmlspecialchars($row['price']); ?> / month</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-times fa-fw"></i>
                                        <span class="detail-label">Exit Day:</span>
                                        <span class="detail-value text-danger"><?php echo htmlspecialchars($row['exitDay']); ?></span>
                                    </div>
                                    
                                    <!-- Form for "More Details" button -->
                                    <form action="more_details.php" method="get" class="mt-auto w-100">
                                        <input type="hidden" name="jobId" value="<?= htmlspecialchars($row['jobId']); ?>">
                                        <button class="btn btn-outline-primary w-100 btn-more-details" type="submit" name="apply">
                                            More Details <i class="fas fa-info-circle"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center" role="alert">
                            <h4 class="alert-heading">No Results</h4>
                            <p><?php echo htmlspecialchars(!empty($note) ? $note : "No jobs found in this category or matching your criteria."); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($conn) { mysqli_close($conn); } ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>