<?php 
include("conn.php"); 
?>
<?php $active2 = "active"; ?>

<?php
$note = ""; // General page note
$current_search_term_php = isset($_GET["search"]) ? $_GET["search"] : "";
$current_filter_php = isset($_GET["filter"]) ? $_GET["filter"] : "All Type";

$search_sql_safe = mysqli_real_escape_string($conn, $current_search_term_php);

$sql = "SELECT * FROM jobtable"; 
$conditions = [];

if (!empty($search_sql_safe)) {
    $conditions[] = "(title LIKE '%$search_sql_safe%' OR company LIKE '%$search_sql_safe%' OR location LIKE '%$search_sql_safe%' OR price LIKE '%$search_sql_safe%' OR exitDay LIKE '%$search_sql_safe%')";
}

if ($current_filter_php === "Full Time" || $current_filter_php === "Part Time") {
    $conditions[] = "jobType = '" . mysqli_real_escape_string($conn, $current_filter_php) . "'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$result = mysqli_query($conn, $sql);
if (!$result) {
    $note = "Error fetching data: " . mysqli_error($conn); 
} elseif (mysqli_num_rows($result) == 0) {
    if (!empty($current_search_term_php) || $current_filter_php !== "All Type") {
        $note = "No jobs found matching your criteria.";
    } else {
        $note = "No jobs currently available.";
    }
}

$current_search_term_display = htmlspecialchars($current_search_term_php);
$current_filter_display = htmlspecialchars($current_filter_php);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; }
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
            height: 200px; 
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
        .job-card .placeholder-text p {
            font-size: 0.8em; color: #c0392b; padding: 8px; text-align: center;
            background-color: #fdedec; border: 1px solid #fadbd8;
            border-radius: .25rem; margin-bottom: 0.75rem; margin-top:0;
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
        #phpmg { text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; color: #333; }
        
        .search-filter-form .form-control, 
        .search-filter-form .form-select { }
    </style>
</head>

<body>
    <?php include_once("navbar.php"); ?>
    <?php include_once("ctg_bar.php"); ?>

    <div class="container mt-4">
        <form action="find_job.php" method="get" class="mb-4 search-filter-form">
            <div class="row justify-content-center g-3">
                <div class="col-md-5 col-lg-4">
                    <input class="form-control" type="search" name="search" 
                           placeholder="Search title, company..." 
                           value="<?php echo $current_search_term_display; ?>">
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="filter" class="form-select">
                        <option value="All Type" <?php echo ($current_filter_php == 'All Type') ? 'selected' : ''; ?>>All Job Types</option>
                        <option value="Full Time" <?php echo ($current_filter_php == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo ($current_filter_php == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" name="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </div>
        </form>

        <h3 id="phpmg">
            <?php 
            if ($current_filter_display !== 'All Type' && !empty($current_search_term_display)) {
                echo "Results for \"" . $current_search_term_display . "\" (Type: " . $current_filter_display . ")";
            } else if (!empty($current_search_term_display)) {
                echo "Search Results for \"" . $current_search_term_display . "\"";
            } else if ($current_filter_display !== 'All Type') {
                echo "Filtered by: " . $current_filter_display;
            } else {
                echo "All Available Jobs";
            }
            ?>
        </h3>
    
        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    
                    // --- Configuration: YOU MUST VERIFY THIS PATH ---
                    // This is the base URL path to your project folder from the web server's root.
                    // Example: If your project is at http://localhost/WorkWise/find_job.php, then $project_url_path = '/WorkWise';
                    // Example: If your project is at http://localhost/find_job.php, then $project_url_path = ''; (empty string)
                    $project_url_path = '/WorkWise'; // <<< --- VERIFY AND CHANGE THIS IF NEEDED ---
                    
                    // Path to your placeholder image, relative to your project's root folder.
                    $default_placeholder_image_path_in_project = 'image/uploads/placeholder.png';
                    // --- End Configuration ---

                    $job_image_db_value = isset($row['job_image']) ? trim($row['job_image']) : '';

                    // Construct full web URL for the placeholder image
                    $placeholder_src_url = rtrim($project_url_path, '/') . '/' . ltrim($default_placeholder_image_path_in_project, '/');

                    $current_image_src_to_display = $placeholder_src_url; // Default to placeholder
                    $display_actual_image = false;
                    $image_not_found_message_for_card = ""; // Message for this specific card

                    if (!empty($job_image_db_value)) {
                        // Construct full web URL for the actual job image (for the <img> src attribute)
                        // Assumes $job_image_db_value is like 'image/uploads/actual.jpg'
                        $actual_image_src_url = rtrim($project_url_path, '/') . '/' . ltrim(htmlspecialchars($job_image_db_value), '/');

                        // Construct absolute filesystem path for the file_exists() check
                        // $_SERVER['DOCUMENT_ROOT'] is the web server's root (e.g., /var/www/html)
                        // $project_url_path helps locate the project folder within DOCUMENT_ROOT
                        $filesystem_path_to_check = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . rtrim($project_url_path, '/') . '/' . ltrim($job_image_db_value, '/');
                        
                        // ---- START DEBUGGING (Uncomment to see path details in HTML source for each card) ----
                        /*
                        echo "\n<!-- Card Debug - Job ID: " . htmlspecialchars(isset($row['id']) ? $row['id'] : 'N/A') . " -->\n";
                        echo "<!-- DB job_image Value: '" . htmlspecialchars($job_image_db_value) . "' -->\n";
                        echo "<!-- Placeholder SRC URL: '" . htmlspecialchars($placeholder_src_url) . "' -->\n";
                        echo "<!-- Actual Image SRC URL: '" . htmlspecialchars($actual_image_src_url) . "' -->\n";
                        echo "<!-- Filesystem Path for Check: '" . htmlspecialchars($filesystem_path_to_check) . "' -->\n";
                        echo "<!-- file_exists Result: " . (file_exists($filesystem_path_to_check) ? 'TRUE' : 'FALSE') . " -->\n";
                        echo "<!-- DOCUMENT_ROOT: '" . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "' -->\n";
                        echo "<!-- Project URL Path Config: '" . htmlspecialchars($project_url_path) . "' -->\n";
                        */
                        // ---- END DEBUGGING ----

                        if (file_exists($filesystem_path_to_check)) {
                            $current_image_src_to_display = $actual_image_src_url;
                            $display_actual_image = true;
                        } else {
                            $image_not_found_message_for_card = "Image not found on server. Path checked: " . htmlspecialchars($filesystem_path_to_check);
                        }
                    } else {
                        $image_not_found_message_for_card = "No image path specified in database for this job.";
                    }
            ?>
            <div class="col-lg-4 col-md-6 job-card-wrapper">
                <div class="card job-card h-100 shadow-sm">
                    <div class="card-img-top-container">
                        <img src="<?php echo $current_image_src_to_display; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?> image">
                        <div class="job-title-overlay">
                            <?php echo htmlspecialchars($row["title"]); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!$display_actual_image && !empty($image_not_found_message_for_card)): ?>
                            <div class="placeholder-text">
                                <p><small><?php echo htmlspecialchars($image_not_found_message_for_card); ?></small></p>
                            </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <i class="fas fa-tags fa-fw"></i>
                            <span class="detail-label">Category:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['category']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-briefcase fa-fw"></i>
                            <span class="detail-label">Type:</span>
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
                    </div>
                </div>
            </div>
            <?php
                endwhile;
            else: 
            ?>
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <h2 class="alert-heading">No Results</h2>
                    <p><?php echo htmlspecialchars($note ?: "No jobs found matching your criteria."); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div> 
    </div> 

    <?php 
    // Condition for showing footer based on original file logic
    if (($result && mysqli_num_rows($result) > 0) || $note !== "Data not found.") {
         include_once("footer.php");
    }
    ?>

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>