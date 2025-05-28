<?php session_start(); ?>
<?php
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<?php include("../conn.php"); ?>
<?php $active2 = "active"; ?>
<?php
// --- Configuration: YOU MUST VERIFY THIS PATH ---
// This is the base URL path to your project folder from the web server's root.
// Example: If your project (where conn.php, image/uploads are) is at http://localhost/MyProject/, 
// then $project_url_path = '/MyProject';
// Example: If your project is at http://localhost/, then $project_url_path = ''; (empty string)
// The first code used '/WorkWise'. Adjust this to your second project's actual URL path.
$project_url_path = '/WorkWise'; // <<< --- CRITICAL: VERIFY AND CHANGE THIS IF NEEDED for your second project---

// Path to your placeholder image, relative to your project's root folder.
$default_placeholder_image_path_in_project = 'image/uploads/placeholder.png'; // Assumes 'image/uploads/' is at the root of $project_url_path
// --- End Configuration ---


// Variables for search form and heading, similar to first code for display purposes
$current_search_term_php = isset($_GET["search"]) ? $_GET["search"] : "";
$current_filter_php = isset($_GET["filter"]) ? $_GET["filter"] : "All Type";

$current_search_term_display = htmlspecialchars($current_search_term_php);
$current_filter_display = htmlspecialchars($current_filter_php);

// Existing backend logic from second code - DO NOT CHANGE
$note = ""; // General page note, will be updated by existing logic below
if (isset($_GET["search"]) || isset($_GET["filter"])) {
    $search1 = isset($_GET["search"]) ? $_GET["search"] : ""; 
    $filter_from_get = isset($_GET["filter"]) ? $_GET["filter"] : "All Type"; 
    
    $search = mysqli_real_escape_string($conn, $current_search_term_php);
    $filter_for_query = mysqli_real_escape_string($conn, $current_filter_php);


    if (!empty($search)) { 
        if ($filter_for_query == "All Type") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        } else if ($filter_for_query == "Full Time" || $filter_for_query == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%') AND jobType = '$filter_for_query'";
        } else {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        }
    } else { 
        if ($filter_for_query == "Full Time" || $filter_for_query == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE jobType = '$filter_for_query'";
        } else { 
            $sql = "SELECT * FROM jobtable";
        }
    }
} else {
    $sql = "SELECT * FROM jobtable";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    $note = "Error fetching data: " . mysqli_error($conn); 
} else if (mysqli_num_rows($result) == 0) {
    $note = "Data not found."; 
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
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
    <?php include_once("login_navbar.php"); ?>
    <?php include_once("login_ctg_bar.php"); ?>

    <div class="container mt-4">
        <form action="user.php" method="get" class="mb-4 search-filter-form">
            <div class="row justify-content-center g-3">
                <div class="col-md-5 col-lg-4">
                    <input class="form-control" type="search" name="search" 
                           placeholder="Search title, company..." 
                           value="<?php echo $current_search_term_display; ?>"
                           id="fsearch"> <!-- ID "fsearch" retained from original second code logic -->
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="filter" class="form-select" id="idcheck"> <!-- ID "idcheck" retained from original second code logic -->
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
                    
                    // --- Image Handling Logic (copied and adapted from first code) ---
                    $job_image_db_value = isset($row['job_image']) ? trim($row['job_image']) : '';
                    $placeholder_src_url = rtrim($project_url_path, '/') . '/' . ltrim($default_placeholder_image_path_in_project, '/');
                    $current_image_src_to_display = $placeholder_src_url; 
                    $display_actual_image = false;
                    $image_not_found_message_for_card = ""; 

                    if (!empty($job_image_db_value)) {
                        $actual_image_src_url = rtrim($project_url_path, '/') . '/' . ltrim(htmlspecialchars($job_image_db_value), '/');
                        $filesystem_path_to_check = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . rtrim($project_url_path, '/') . '/' . ltrim($job_image_db_value, '/');
                        
                        if (file_exists($filesystem_path_to_check)) {
                            $current_image_src_to_display = $actual_image_src_url;
                            $display_actual_image = true;
                        } else {
                            $image_not_found_message_for_card = "Image not found on server. Path: " . htmlspecialchars($job_image_db_value);
                        }
                    } else {
                        $image_not_found_message_for_card = "No image path specified in database for this job.";
                    }
                    // --- End Image Handling Logic ---
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
                         <div class="detail-item">
                            <i class="fas fa-calendar-times fa-fw"></i>
                            <span class="detail-label">Exit Day:</span>
                            <span class="detail-value"><span class="text-danger"><?php echo htmlspecialchars($row['exitDay']); ?></span></span>
                        </div>
                        
                        <a href="more_details.php?jobId=<?php echo $row['jobId']; ?>" class="btn btn-primary mt-auto w-100">
                            More Details <i class="fas fa-info-circle ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php
                endwhile;
            else: 
                $alert_message = "No jobs found matching your criteria."; 
                if (!empty($note)) {
                    if ($note === "Data not found.") { 
                        if (!empty($current_search_term_php) || $current_filter_php !== "All Type") {
                            $alert_message = "No jobs found matching your criteria.";
                        } else {
                            $alert_message = "No jobs currently available.";
                        }
                    } else {
                        $alert_message = $note; 
                    }
                } else if (empty($current_search_term_php) && $current_filter_php === "All Type") {
                     $alert_message = "No jobs currently available."; 
                }
            ?>
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <h2 class="alert-heading">No Results</h2>
                    <p><?php echo htmlspecialchars($alert_message); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div> 
    </div> 

    <?php 
    $show_footer = false;
    if ($result && mysqli_num_rows($result) > 0) {
        $show_footer = true;
    } else {
        if ($note !== 'Data not found.') { // Original second code condition
             $show_footer = true;
        }
    }
    
    if ($show_footer) {
         include_once('login_footer.php');
    }
    ?>

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>