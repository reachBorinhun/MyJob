<?php session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}
include("../conn.php");
$active2 = "active"; // Existing variable from second code

// --- Configuration: YOU MUST VERIFY THIS PATH (Copied from first code) ---
// This is the base URL path to your project folder from the web server's root.
// Example: If your project (where conn.php, image/uploads are) is at http://localhost/MyProject/, 
// then $project_url_path = '/MyProject';
// Example: If your project is at http://localhost/, then $project_url_path = ''; (empty string)
// The first code used '/WorkWise'. Adjust this to your second project's actual URL path.
$project_url_path = '/WorkWise'; // <<< --- CRITICAL: VERIFY AND CHANGE THIS IF NEEDED for your second project---

// Path to your placeholder image, relative to your project's root folder.
$default_placeholder_image_path_in_project = 'image/uploads/placeholder.png'; // Assumes 'image/uploads/' is at the root of $project_url_path
// --- End Configuration ---


// Existing PHP logic from second code - DO NOT CHANGE
$ctg = $_GET["ctg"];
$note = ""; // Initialized in second code, will be used for "no results" message
$search1_from_get = $_GET["search"] ?? ''; // Original search term from GET
$filter_from_get = $_GET["filter"] ?? 'All Type'; // Original filter from GET, default to All Type for display consistency

// Variables for search form values and heading display (similar to first code)
$current_search_term_php = $search1_from_get;
$current_filter_php = $filter_from_get;

$current_search_term_display = htmlspecialchars($current_search_term_php);
$current_filter_display = htmlspecialchars($current_filter_php);

// Existing backend logic from second code - DO NOT CHANGE
$search = mysqli_real_escape_string($conn, $current_search_term_php); // Use the prepared variable

if (!empty($current_search_term_php) || ($current_filter_php != 'All Type' && !empty($current_filter_php)) ) { // check if filter is not empty and not 'All Type'
    $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
    if (!empty($current_search_term_php)) {
        $sql .= " AND (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
    }
    if ($current_filter_php == "Full Time" || $current_filter_php == "Part Time") {
        $sql .= " AND jobType = '$current_filter_php'";
    }
} else {
    $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
}
$result = mysqli_query($conn, $sql);

// Determine category display names (from original second code)
$categoryMap = [
    'Graphics' => ['Graphics & Design', 'Designs to make you stand out'],
    'Programming' => ['Programming & Tech', 'You think it. A programmer develops it'],
    'Digital' => ['Digital Marketing', 'Build your brand. Grow your business.'],
    'Video' => ['Video & Animation', 'Bring your story to life with creative videos.'],
    'Writing' => ['Writing & Translation', 'Get your words across—in any language.'],
    'Music' => ['Music & Audio', 'Do not miss a beat. Bring your sound to life.'],
    'Business' => ['Business', 'Business to make you stand out'],
    'AI' => ['AI Services', 'AI to make you stand out'],
];
$c1_category_name = $categoryMap[$ctg][0] ?? htmlspecialchars($ctg); // Use ctg if not in map
$c2_category_tagline = $categoryMap[$ctg][1] ?? 'Explore opportunities in ' . htmlspecialchars($ctg);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - <?php echo htmlspecialchars($c1_category_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .search-filter-form .form-select { /* Styles already in BS, can be enhanced if needed */ }

        /* Existing styles from second code's category header */
        .category-header-banner {
             background-size: cover;
             background-position: center center; /* Added for better image positioning */
        }
    </style>
</head>
<body class="bg-light"> <!-- bg-light from second code, body bg from first code style will override this. Keep this for consistency if style block is removed. -->

<?php include_once("login_navbar.php"); ?>
<?php include_once("login_ctg_bar.php"); ?>

<div class="container mt-4">
    <!-- Existing category header from second code - RETAINED -->
    <div class="p-5 rounded bg-dark text-white text-center mb-4 category-header-banner" style="background-image: url('../../Image/Home/ctg2.jpg');">
        <h1 class="display-4"><?php echo htmlspecialchars($c1_category_name); ?></h1>
        <p class="lead fw-bold"><?php echo htmlspecialchars($c2_category_tagline); ?></p>
    </div>

    <!-- Search form styled like first code -->
    <form action="login_job_category.php" method="get" class="mb-4 search-filter-form">
        <div class="row justify-content-center g-3">
            <div class="col-md-5 col-lg-4">
                <input class="form-control" type="search" name="search" 
                       placeholder="Search title, company..." 
                       value="<?php echo $current_search_term_display; ?>"
                       id="fsearch"> <!-- ID from first code's structure -->
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="filter" class="form-select" id="idcheck"> <!-- ID from first code's structure -->
                    <option value="All Type" <?php echo ($current_filter_php == 'All Type' || $current_filter_php == '') ? 'selected' : ''; ?>>All Job Types</option>
                    <option value="Full Time" <?php echo ($current_filter_php == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                    <option value="Part Time" <?php echo ($current_filter_php == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                </select>
            </div>
            <input type="hidden" name="ctg" value="<?= htmlspecialchars($ctg) ?>"> <!-- Retained from second code -->
            <div class="col-md-auto">
                <button type="submit" name="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button> <!-- Name "submit" and icon from first code -->
            </div>
        </div>
    </form>

    <!-- Results heading styled like first code -->
    <h3 id="phpmg">
        <?php 
        $heading_text = "";
        if (!empty($current_search_term_display) && $current_filter_display !== 'All Type' && !empty($current_filter_display)) {
            $heading_text = "Results for \"" . $current_search_term_display . "\" (Type: " . $current_filter_display . ") in " . htmlspecialchars($c1_category_name);
        } else if (!empty($current_search_term_display)) {
            $heading_text = "Search Results for \"" . $current_search_term_display . "\" in " . htmlspecialchars($c1_category_name);
        } else if ($current_filter_display !== 'All Type' && !empty($current_filter_display)) {
            $heading_text = "Filtered by: " . $current_filter_display . " in " . htmlspecialchars($c1_category_name);
        } else {
            $heading_text = "All Available Jobs in " . htmlspecialchars($c1_category_name);
        }
        echo $heading_text;
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
                    // Construct absolute filesystem path for file_exists check
                    // Assuming DOCUMENT_ROOT is correctly set and $project_url_path starts with '/' if it's not at the web root.
                    // If $project_url_path is empty (project at web root), then $_SERVER['DOCUMENT_ROOT'] . '/' . $job_image_db_value
                    // If $project_url_path is /MyProject, then $_SERVER['DOCUMENT_ROOT'] . /MyProject . '/' . $job_image_db_value
                    $base_doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    $full_project_path_segment = rtrim($project_url_path, '/'); // Ensure no trailing slash if $project_url_path is not empty
                    $image_path_segment = ltrim($job_image_db_value, '/');
                    
                    $filesystem_path_to_check = $base_doc_root . $full_project_path_segment . '/' . $image_path_segment;
                    
                    if (file_exists($filesystem_path_to_check) && is_file($filesystem_path_to_check)) {
                        $current_image_src_to_display = $actual_image_src_url;
                        $display_actual_image = true;
                    } else {
                        $image_not_found_message_for_card = "Image not found. Path: " . htmlspecialchars($job_image_db_value);
                    }
                } else {
                    $image_not_found_message_for_card = "No image path for this job.";
                }
                // --- End Image Handling Logic ---
        ?>
        <div class="col-lg-4 col-md-6 job-card-wrapper">
            <div class="card job-card h-100 shadow-sm"> <!-- h-100 from original second code's card, shadow-sm from first code's card style -->
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
                    
                    <!-- Retaining form structure for "More Details" from second code, styled like first code's button -->
                    <form action="more_details.php" method="get" class="mt-auto w-100"> <!-- mt-auto w-100 from first code's button applied to form -->
                        <input type="hidden" name="jobId" value="<?= $row['jobId']; ?>">
                        <button class="btn btn-primary w-100" type="submit" name="apply"> <!-- apply name retained, styling from first code -->
                            More Details <i class="fas fa-info-circle ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        else: 
            // "No results" message styled like first code
            // The message content itself uses the second code's logic ($note or default)
            $no_results_message = $note ?: "No jobs found matching your criteria in this category.";
            if (mysqli_num_rows($result) == 0 && empty($current_search_term_php) && ($current_filter_php == 'All Type' || empty($current_filter_php))) {
                $no_results_message = "No jobs currently available in " . htmlspecialchars($c1_category_name) . ".";
            }
        ?>
        <div class="col-12">
            <div class="alert alert-warning text-center" role="alert">
                <h2 class="alert-heading">No Results</h2>
                <p><?php echo htmlspecialchars($no_results_message); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div> 
</div>

<?php 
// Retaining original footer include logic from second code.
// The first code's logic for $show_footer was slightly different,
// but sticking to the second code's original logic for this part.
if (($note ?? '') != "Data not found." || (isset($result) && mysqli_num_rows($result) > 0) ) { // Ensure footer shows if there are results, or if note isn't "Data not found"
    include_once("login_footer.php");
}
?>

<?php if ($conn) { mysqli_close($conn); } ?>
<!-- Font Awesome kit from second code. The custom CSS also includes a cdnjs link, one is sufficient. -->
<!-- <script src="https://kit.fontawesome.com/a076d05399.js"></script> --> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>