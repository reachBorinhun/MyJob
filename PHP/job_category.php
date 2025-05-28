<?php 
include("conn.php"); 
$active2 = "active"; // Or whichever is appropriate for this page

// Sanitize GET inputs early
$_GET['ctg'] = isset($_GET['ctg']) ? mysqli_real_escape_string($conn, $_GET['ctg']) : '';
$_GET['search'] = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$_GET['filter'] = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'All Type';

$note = "";
$ctg = $_GET["ctg"]; // Already sanitized
$search = $_GET["search"]; // Already sanitized
$filter = $_GET["filter"]; // Already sanitized

// --- Original PHP Logic for SQL Query Construction ---
if (empty($ctg)) {
    // Handle missing category - redirect or show error
    // For now, let's assume ctg is always provided for this page.
    // If not, the queries below might fail or return unintended results.
    // Consider adding: die("Category not specified."); 
}

$sql_base = "SELECT * FROM jobtable WHERE category='$ctg'";
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
}

if ($filter == "Full Time" || $filter == "Part Time") {
    $conditions[] = "jobType = '$filter'";
}

if (!empty($conditions)) {
    $sql = $sql_base . " AND " . implode(" AND ", $conditions);
} else {
    $sql = $sql_base;
}
// --- End Original PHP Logic ---

$result = mysqli_query($conn, $sql);

// For display purposes, use htmlspecialchars
$ctg_display = htmlspecialchars($ctg);
$search_display = htmlspecialchars($search); // Use this for echoing in form value
$filter_display = htmlspecialchars($filter);


if (!$result) {
    $note = "Error fetching data: " . mysqli_error($conn); 
} elseif (mysqli_num_rows($result) == 0) {
    if (!empty($search) || $filter !== "All Type") {
        $note = "No jobs found matching your criteria in this category.";
    } else {
        $note = "No jobs currently available in this category.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs in <?php echo $ctg_display; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5.3.0-alpha1 (matching File 1 for design consistency) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.0.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Styles from "File 1" / previous successful solution */
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

        /* Styles for the category search div */
        #seardiv {
            /* background-image: url(../Image/Home/ctg4.png); -- Path might need adjustment if this file is in a different directory level than 'Image' folder */
            /* For now, let's use a fallback or assume path is correct relative to this file's location */
            background-image: url('../Image/Home/ctg4.png'); /* Common relative path */
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            width: 100%; /* Make it full width of its container */
            max-width: 1140px; /* Limit width similar to Bootstrap container */
            min-height: 220px; /* Ensure enough height for content */
            margin: 20px auto; /* Center it */
            border-radius: 15px; /* Consistent rounding */
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            padding: 25px 30px; /* More padding */
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        #ctg_name {
            font-size: 2.5rem; /* Responsive font size */
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        #ctg_ds {
            font-size: 1.25rem; /* Responsive font size */
            font-style: italic;
            font-weight: 500;
            margin-bottom: 1.5rem; /* Space before form */
            text-shadow: 1px 1px 2px rgba(0,0,0,0.4);
        }
        #seardiv .form-control, #seardiv .form-select {
            /* Ensure inputs inside seardiv are styled well, potentially with higher contrast if needed on background */
            /* Bootstrap defaults might be fine */
        }
        #seardiv .btn-primary { /* Using btn-primary for consistency, or keep custom if preferred */
             /* background-color: rgb(85, 182, 243); */ /* Original custom color */
             /* border-color: rgb(85, 182, 243); */
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">

<?php include_once("navbar.php"); ?>
<?php include_once("ctg_bar.php"); ?>

<div class="container-fluid px-0"> <!-- Full-width container for seardiv -->
    <div id="seardiv">
        <?php 
        // Logic for category display text (preserved)
        $c1_display = $ctg_display; // Default to the raw category name
        $c2_display = 'Explore jobs in ' . $ctg_display; // Default description

        // Use a mapping for prettier names and descriptions if available
        $category_details = [
            'Graphics' => ['Graphics & Design', 'Designs to make you stand out'],
            'Programming' => ['Programming & Tech', 'You think it. A programmer develops it'],
            'Digital' => ['Digital Marketing', 'Build your brand. Grow your business.'],
            'Video' => ['Video & Animation', 'Bring your story to life with creative videos.'],
            'Writing' => ['Writing & Translation', 'Get your words across—in any language.'],
            'Music' => ['Music & Audio', 'Do not miss a beat. Bring your sound to life.'],
            'Business' => ['Business', 'Business to make you stand out'],
            'AI' => ['AI Services', 'AI to make you stand out']
            // Add 'New' or other categories if they have specific display text
        ];
        if (array_key_exists($ctg, $category_details)) {
            $c1_display = htmlspecialchars($category_details[$ctg][0]);
            $c2_display = htmlspecialchars($category_details[$ctg][1]);
        } else {
            $c1_display = htmlspecialchars(ucfirst($ctg)); // Capitalize if no specific mapping
            $c2_display = 'Explore opportunities in ' . htmlspecialchars(ucfirst($ctg));
        }
        ?>
        <h1 id="ctg_name"><?php echo $c1_display; ?></h1>
        <h3 id="ctg_ds"><?php echo $c2_display; ?></h3>

        <form action="job_category.php" method="get" class="mt-3">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6 col-md-12">
                    <input type="search" name="search" class="form-control form-control-lg"
                           placeholder="Search jobs in <?php echo $c1_display; ?>..." 
                           value="<?php echo $search_display; // Use htmlspecialchars'd version for value ?>">
                </div>
                <div class="col-lg-3 col-md-6">
                    <select name="filter" class="form-select form-select-lg">
                        <option value="All Type" <?php if($filter == 'All Type') echo 'selected'; ?>>All Job Types</option>
                        <option value="Full Time" <?php if($filter == 'Full Time') echo 'selected'; ?>>Full Time</option>
                        <option value="Part Time" <?php if($filter == 'Part Time') echo 'selected'; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </div>
            <input type="hidden" name="ctg" value="<?php echo $ctg_display; // Already htmlspecialchars'd via $ctg GET processing ?>">
        </form>
    </div>
</div>


<div class="container mt-4 mb-5">
    <h3 id="phpmg">
        <?php 
        $page_title_text = "Jobs in " . $c1_display;
        if (!empty($search_display)) {
            $page_title_text = "Search Results for \"" . $search_display . "\" in " . $c1_display;
        }
        if ($filter_display !== 'All Type') {
            $page_title_text .= " (Type: " . $filter_display . ")";
        }
        echo $page_title_text;
        ?>
    </h3>

    <div class="row">
        <?php
        if ($result && mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                
                // --- Configuration: YOU MUST VERIFY THIS PATH ---
                $project_url_path = '/WorkWise'; // <<< --- VERIFY AND CHANGE THIS IF NEEDED ---
                $default_placeholder_image_path_in_project = 'image/uploads/placeholder.png';
                // --- End Configuration ---

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
                        $image_not_found_message_for_card = "Image file not found. Expected at: " . htmlspecialchars($job_image_db_value);
                    }
                } else {
                    $image_not_found_message_for_card = "No image path specified for this job.";
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
                            <p><small><?php echo $image_not_found_message_for_card; ?></small></p>
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
            <div class="alert alert-warning text-center mt-4" role="alert">
                <h2 class="alert-heading">No Results</h2>
                <p><?php echo htmlspecialchars($note); // $note is already set if no results or error ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Preserved footer logic
$current_note_for_footer = $note ?? ''; // PHP 7+ null coalescing operator
if ($current_note_for_footer != "Data not found.") { // Original condition from your file
    include_once("footer.php");
} elseif ($result && mysqli_num_rows($result) > 0) { // Ensure footer shows if there are results
    include_once("footer.php");
}
?>

<?php if ($conn) { $conn->close(); } ?>
<!-- Bootstrap JS Bundle (Popper.js included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>