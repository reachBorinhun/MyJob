<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

include("../conn.php"); // Ensure $conn is established here
$active4 = "active"; // For your navbar
$userid = $_SESSION['id'];
$success_message = "";
$error_message = "";

// Handle flash messages from redirects
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// --- Configuration for Images (copied from first code, VERIFY PATHS) ---
$project_url_path = '/WorkWise'; // <<< --- CRITICAL: VERIFY AND CHANGE THIS IF NEEDED for your project---
$default_placeholder_image_path_in_project = 'image/uploads/placeholder.png';
// --- End Image Configuration ---

// --- HANDLE REMOVE ACTION (POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_bookmark_id'])) {
    $bookmark_id_to_remove = filter_input(INPUT_POST, 'remove_bookmark_id', FILTER_VALIDATE_INT);

    if ($bookmark_id_to_remove) {
        $sql_delete = "DELETE FROM bmjob WHERE id = ? AND userId = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "ii", $bookmark_id_to_remove, $userid);
            if (mysqli_stmt_execute($stmt_delete)) {
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    $_SESSION['success_message'] = "Bookmark removed successfully.";
                } else {
                    $_SESSION['error_message'] = "Could not remove bookmark or it was already removed.";
                }
            } else {
                $_SESSION['error_message'] = "Error removing bookmark: " . htmlspecialchars(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $_SESSION['error_message'] = "Error preparing delete statement: " . htmlspecialchars(mysqli_error($conn));
        }
    } else {
        $_SESSION['error_message'] = "Invalid bookmark ID for removal.";
    }
    header('Location: save_job.php'); // Redirect to clear POST data and show message
    exit();
}

// --- HANDLE SEARCH AND FILTER (GET request) ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$job_type_filter = isset($_GET['filter']) ? $_GET['filter'] : 'All Type';

// For display in heading and form
$current_search_term_display = htmlspecialchars($search_term);
$current_filter_display = htmlspecialchars($job_type_filter);


$sql_base = "SELECT bm.id as bookmark_id, job.*
             FROM bmjob bm
             JOIN jobtable job ON bm.jobId = job.jobId
             WHERE bm.userId = ?";

$conditions = [];
$params = [$userid];
$param_types = "i";

if (!empty($search_term)) {
    $conditions[] = "(job.title LIKE ? OR job.company LIKE ? OR job.location LIKE ?)";
    $like_search_term = "%" . $search_term . "%";
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $param_types .= "sss";
}

if ($job_type_filter !== 'All Type' && ($job_type_filter === 'Full Time' || $job_type_filter === 'Part Time')) {
    $conditions[] = "job.jobType = ?";
    $params[] = $job_type_filter;
    $param_types .= "s";
}

if (!empty($conditions)) {
    $sql_base .= " AND " . implode(" AND ", $conditions);
}
$sql_base .= " ORDER BY bm.id DESC";

$stmt_select = mysqli_prepare($conn, $sql_base);
if ($stmt_select) {
    if (count($params) > 0 && !empty($param_types)) {
        $bind_names = [$param_types];
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        $stmt_ref = $stmt_select; 
        array_unshift($bind_names, $stmt_ref); 
        call_user_func_array('mysqli_stmt_bind_param', $bind_names);
        array_shift($bind_names); 
    }
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    if (!$result && empty($error_message)) { 
        $error_message = "Error fetching saved jobs: " . htmlspecialchars(mysqli_stmt_error($stmt_select));
    }
} else {
    $error_message = "Error preparing search statement: " . htmlspecialchars(mysqli_error($conn));
    $result = false;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Jobs - WorkWise</title>
    <!-- Bootstrap CSS (from first code) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .job-card-wrapper { margin-bottom: 2rem; }
        .job-card {
            position: relative; /* For absolute positioning of elements like remove button */
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
        
        .remove-btn-container {
            position: absolute;
            top: 15px; 
            right: 15px; 
            z-index: 10; 
        }
        .remove-btn-container .btn-remove-bookmark {
             /* Custom styling for remove button if default Bootstrap is not enough */
        }
    </style>
</head>

<body>

    <?php include_once("login_navbar.php"); ?>

    <div class="container mt-4">

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message && !$result): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form action="save_job.php" method="get" class="mb-4 search-filter-form">
            <div class="row justify-content-center g-3">
                <div class="col-md-5 col-lg-4">
                    <input class="form-control" type="search" name="search" 
                           placeholder="Search title, company..." 
                           value="<?php echo htmlspecialchars($search_term); ?>"
                           id="search"> 
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="filter" class="form-select" id="filter"> 
                        <option value="All Type" <?php echo ($job_type_filter == 'All Type') ? 'selected' : ''; ?>>All Job Types</option>
                        <option value="Full Time" <?php echo ($job_type_filter == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo ($job_type_filter == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </div>
             <?php if (!empty($search_term) || $job_type_filter !== 'All Type'): ?>
                <div class="text-center mt-3"> 
                    <small>
                        Currently showing results for:
                        <?php if (!empty($search_term)) echo "<em>'" . htmlspecialchars($search_term) . "'</em> "; ?>
                        <?php if ($job_type_filter !== 'All Type') echo "Job Type: <em>" . htmlspecialchars($job_type_filter) . "</em>"; ?>
                        <a href="save_job.php" class="ms-2">Clear Filters</a>
                    </small>
                </div>
            <?php endif; ?>
        </form>

        <h3 id="phpmg" class="mb-4">
            <?php
            if ($current_filter_display !== 'All Type' && !empty($current_search_term_display)) {
                echo "Saved Jobs: Results for \"" . $current_search_term_display . "\" (Type: " . $current_filter_display . ")";
            } else if (!empty($current_search_term_display)) {
                echo "Saved Jobs: Search Results for \"" . $current_search_term_display . "\"";
            } else if ($current_filter_display !== 'All Type') {
                echo "Saved Jobs: Filtered by Type " . $current_filter_display;
            } else {
                echo "All My Saved Jobs";
            }
            ?>
        </h3>

        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
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
                         $image_not_found_message_for_card = "No image path specified for this job.";
                    }
                    ?>
                    <div class="col-lg-4 col-md-6 job-card-wrapper">
                        <div class="card job-card h-100 shadow-sm">
                            
                            <div class="remove-btn-container"> 
                                <form action="save_job.php" method="post" onsubmit="return confirm('Are you sure you want to remove this saved job?');">
                                    <input type="hidden" name="remove_bookmark_id" value="<?php echo htmlspecialchars($row['bookmark_id']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm btn-remove-bookmark" title="Remove Bookmark">
                                        <i class="fa fa-trash"></i> 
                                    </button>
                                </form>
                            </div>

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
                                <?php if (!empty($row['category'])): ?>
                                <div class="detail-item">
                                    <i class="fas fa-tags fa-fw"></i>
                                    <span class="detail-label">Category:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['category']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <i class="fas fa-dollar-sign fa-fw"></i>
                                    <span class="detail-label">Salary:</span>
                                    <span class="salary-badge ms-1">$<?php echo htmlspecialchars($row['price']); ?> / month</span>
                                </div>
                                
                                <a href="more_details.php?jobId=<?php echo htmlspecialchars($row['jobId']); ?>" class="btn btn-primary mt-auto w-100">
                                    More Details <i class="fas fa-info-circle ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else if ($result) { 
                 $alert_message = "";
                 if (!empty($search_term) || $job_type_filter !== 'All Type') {
                    $alert_message = "No saved jobs match your current filter/search criteria.";
                } else {
                    $alert_message = "You haven't saved any jobs yet. Browse jobs and save your favorites!";
                }
                ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center" role="alert"> 
                        <h2 class="alert-heading">No Saved Jobs Found</h2>
                        <p><?php echo $alert_message; ?></p>
                         <?php if (empty($search_term) && $job_type_filter === 'All Type'): ?>
                            <a href="user.php" class="btn btn-primary mt-2">Find Jobs</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
            
            if ($stmt_select) mysqli_stmt_close($stmt_select);
            ?>
        </div> 
    </div> 

    <?php
    $should_show_footer = false;
    if ($result) { 
        if (mysqli_num_rows($result) > 0) {
            $should_show_footer = true; 
        } else {
            if (!empty($search_term) || $job_type_filter !== 'All Type') {
                $should_show_footer = true; 
            } else {
                $should_show_footer = false;
            }
        }
    } else {
        if (!empty($error_message)) {
             $should_show_footer = true;
        }
    }
    
    if ($should_show_footer) {
         include_once('login_footer.php'); 
    }
    ?>

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>