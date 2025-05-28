<?php
include("../conn.php");
session_start();
?>
<?php $active4 = "active"; ?>
<?php
// General security: Sanitize GET inputs.
if (isset($_GET['id'])) {
    // Assuming 'id' for deletion should be an integer.
    // No need to filter_var if mysqli_real_escape_string is used and query is quoted,
    // but for strictness, you could add integer validation.
    $_GET['id'] = mysqli_real_escape_string($conn, $_GET['id']);
}

if (isset($_GET['remove'])) {
    $jobid = $_GET['id']; 
    // $sql_get_image = "SELECT job_image FROM jobtable WHERE jobId = '$jobid'";
    // ... (optional image deletion logic) ...
    $sql = "DELETE FROM jobtable WHERE `jobtable`.`jobId` = '$jobid'";
    $result_del = mysqli_query($conn, $sql); // Changed variable name for clarity from $result
    if ($result_del) {
        echo '<script> alert("Delete successful."); window.location.href="approved_job.php";</script>';
        exit(); 
    } else {
        echo '<script> alert("Error deleting job: ' . mysqli_error($conn) . '"); window.location.href="approved_job.php";</script>';
        exit();
    }
}
?>
<?php
$note = "";
$sql = ""; // Initialize $sql

// Fetch and sanitize GET parameters for filtering
$current_search_term = isset($_GET["search"]) ? $_GET["search"] : "";
$current_filter_type = isset($_GET["filter"]) ? $_GET["filter"] : "All Type"; // Default to "All Type"
$current_id_check = isset($_GET["idcheck"]) ? $_GET["idcheck"] : "";

$search_safe = mysqli_real_escape_string($conn, $current_search_term);
// $filter_safe is $current_filter_type, already sanitized by ensuring it's one of the expected values or "All Type"
// Or, if it could be arbitrary, then:
$filter_type_safe_for_sql = mysqli_real_escape_string($conn, $current_filter_type);
$id_check_safe = mysqli_real_escape_string($conn, $current_id_check);

// --- Re-implementing the original SQL query logic structure for approved_job.php ---
// This logic prioritizes search/filter over idcheck if both are present.
// Condition for entering the first main block: if there's a search term, OR if there's an active filter (not "All Type" and not empty)
if (!empty($current_search_term) || ($current_filter_type != "All Type" && !empty($current_filter_type))) {
    if (!empty($current_search_term)) {
        // Search term is present
        $search_condition_sql = "(title LIKE '%$search_safe%' OR company LIKE '%$search_safe%' OR location LIKE '%$search_safe%' OR price LIKE '%$search_safe%' OR exitDay LIKE '%$search_safe%')";
        
        if ($current_filter_type == "All Type" || empty($current_filter_type)) {
            // Search term present, filter is "All Type" or empty
            $sql = "SELECT * FROM jobtable WHERE $search_condition_sql";
        } else if ($current_filter_type == "Full Time" || $current_filter_type == "Part Time") {
            // Search term present, and filter is "Full Time" or "Part Time"
            $sql = "SELECT * FROM jobtable WHERE $search_condition_sql AND jobType = '$filter_type_safe_for_sql'";
        } else { 
            // Fallback: search term present, but filter is unrecognized (but not empty and not "All Type")
            // Original code effectively used only the search condition here.
            $sql = "SELECT * FROM jobtable WHERE $search_condition_sql";
        }
    } else {
        // Search term is empty, but filter is "Full Time" or "Part Time" (due to the outer 'if' condition)
        // This implies $current_filter_type must be "Full Time" or "Part Time" to reach here.
        $sql = "SELECT * FROM jobtable WHERE jobType = '$filter_type_safe_for_sql'";
    }
} else if (!empty($current_id_check)) { 
    // Only JOB ID search is active (no search term, and filter is "All Type" or empty)
    $sql = "SELECT * FROM jobtable WHERE jobId = '$id_check_safe'";
} else {
    // No search term, filter is "All Type" or empty, and no JOB ID search
    $sql = "SELECT * FROM jobtable";
}
// --- End of re-implemented original SQL query logic ---

$result_jobs = mysqli_query($conn, $sql);

// Determine if any filter was actually attempted by the user to refine "No results" message
$any_filter_applied_by_user = !empty($current_search_term) || 
                              ($current_filter_type != "All Type" && !empty($current_filter_type)) || 
                              !empty($current_id_check);

if ($result_jobs) {
    if (mysqli_num_rows($result_jobs) == 0 && $any_filter_applied_by_user) {
        $note = "Data not found matching your criteria.";
    }
    // If mysqli_num_rows is 0 and no filters were applied, $note remains empty,
    // and the generic "No approved jobs found." message will be shown by the HTML.
} else { // Query itself failed
    $note = "Error fetching data: " . mysqli_error($conn);
    // Optional: Log the error and SQL for debugging
    // error_log("SQL Error in approved_job.php: " . mysqli_error($conn) . " | Query: " . $sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Jobs</title>
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
            height: 220px; 
        }
        .job-card .card-img-top {
            width: 100%;
            height: 100%; 
            object-fit: cover;
            border-radius: 8px; 
        }
        
        .job-card .admin-actions {
            position: absolute;
            top: 1.25rem;    
            left: 1.25rem;   
            right: 1.25rem;  
            z-index: 10;
            display: flex;
            justify-content: flex-end; 
        }
        .job-card .admin-actions button {
            padding: 6px 10px; border: none; border-radius: 5px; cursor: pointer;
            font-size: 0.9rem; line-height: 1; transition: opacity 0.2s ease-in-out;
        }
        .job-card .admin-actions button:hover { opacity: 0.85; }
        .job-card .admin-actions button.action-remove { background-color: #dc3545; color: white; }
        .job-card .admin-actions button i { color: white !important; font-size: 1em; }

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

    <div class="container mt-4">
        <form action="approved_job.php" method="get" class="mb-4 search-filter-form">
            <div class="row justify-content-center g-3">
                <div class="col-md-4 col-lg-4">
                    <input class="form-control" type="search" name="search" 
                           placeholder="Search title, company..." 
                           value="<?php echo htmlspecialchars($current_search_term); ?>">
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="filter" class="form-select">
                        <option value="All Type" <?php echo ($current_filter_type == 'All Type') ? 'selected' : ''; ?>>All Job Types</option>
                        <option value="Full Time" <?php echo ($current_filter_type == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo ($current_filter_type == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <input type="text" name="idcheck" class="form-control" 
                           placeholder="JOB ID" 
                           value="<?php echo htmlspecialchars($current_id_check); ?>">
                </div>
                <div class="col-md-2 col-lg-auto">
                    <button type="submit" name="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </div>
        </form>

        <h3 id="phpmg" class="mt-3 mb-3">
            <?php
            $message_parts = [];
            if (!empty($current_search_term)) {
                $message_parts[] = "search for \"" . htmlspecialchars($current_search_term) . "\"";
            }
            // Only add type to message if it's an active filter
            if ($current_filter_type !== 'All Type' && !empty($current_filter_type)) { 
                $message_parts[] = "type: " . htmlspecialchars($current_filter_type);
            }
            if (!empty($current_id_check)) {
                $message_parts[] = "ID: " . htmlspecialchars($current_id_check);
            }

            if (!empty($message_parts)) {
                // Consider how message should reflect the original logic (e.g. if idcheck is ignored due to search/filter)
                // For simplicity, we'll list all active-looking inputs. The query logic itself is now original.
                echo "Filtered Results: " . implode(" & ", $message_parts);
            } else {
                echo "All Approved Jobs";
            }
            ?>
        </h3>
    
        <div class="row">
            <?php
            if ($result_jobs && mysqli_num_rows($result_jobs) > 0) {
                while ($row = mysqli_fetch_assoc($result_jobs)) {
                    $image_base_path = "../../"; 
                    $default_placeholder_url = $image_base_path . "image/uploads/placeholder.png"; 
                    $full_image_url = $default_placeholder_url; 
                    $job_image_db_path_part = ""; 
                    $display_image = false;
                    $image_not_found_message = "";

                    if (!empty($row['job_image'])) {
                        $job_image_db_path_part = htmlspecialchars($row['job_image']);
                        $candidate_full_image_url = $image_base_path . $job_image_db_path_part;
                        $server_path_to_check_display = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $job_image_db_path_part; // Adjust /WorkWise/ if needed
                        
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
                        <form action="approved_job.php" method="get" class="h-100">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['jobId']); ?>">
                            
                            <div class="card job-card h-100">
                                <div class="card-img-top-container">
                                    <img src="<?php echo $full_image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?> image">
                                    
                                    <div class="admin-actions">
                                        <button type="submit" name="remove" value="remove" class="action-remove" onclick="return confirm('Are you sure you want to remove this job?');" title="Remove Job">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
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
                                        <i class="fas fa-tags fa-fw"></i>
                                        <span class="detail-label">Category:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['category']); ?></span>
                                    </div>
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
                                    <?php if (isset($row['exitDay']) && !empty($row['exitDay'])): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-times fa-fw"></i>
                                        <span class="detail-label">Exit Day:</span>
                                        <span class="detail-value text-danger"><?php echo htmlspecialchars($row['exitDay']); ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <a href="more_details.php?jobId=<?php echo htmlspecialchars($row['jobId']); ?>" class="btn btn-outline-primary w-100 mt-auto btn-more-details">
                                        More Details <i class="fas fa-ellipsis-h"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php
                } // end while
            } else { // No results block
                 if (empty($note)) { // If $note wasn't set by specific "Data not found" or "Error"
                     $note = "No approved jobs found."; // Default message if no jobs and no filters
                 }
                 // Use alert structure from the first code for consistency
                 echo '<div class="col-12"><div class="alert alert-warning text-center" role="alert"><h2 class="alert-heading">No Results</h2><p>' . htmlspecialchars($note) . '</p></div></div>';
            }
            ?>
        </div> 
    </div> 

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>