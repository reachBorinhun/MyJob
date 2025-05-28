<?php
include("../conn.php");
session_start();
?>
<?php $active4 = "active"; ?>
<?php
// General security: Sanitize GET inputs early
if (isset($_GET['id'])) {
    $_GET['id'] = mysqli_real_escape_string($conn, $_GET['id']);
}

if (isset($_GET['remove']) || isset($_GET['add'])) {
    $id = $_GET['id']; 
    if (isset($_GET['remove'])) {
        // --- Optional Image Deletion ---
        // $sql_get_image_path = "SELECT job_image FROM unapproved_job WHERE id = '$id'";
        // $res_get_image_path = mysqli_query($conn, $sql_get_image_path);
        // if ($row_image_path = mysqli_fetch_assoc($res_get_image_path)) {
        //     if (!empty($row_image_path['job_image'])) {
        //         // IMPORTANT: Adjust '/WorkWise/' if your project DOCUMENT_ROOT structure is different.
        //         $image_file_to_delete = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $row_image_path['job_image'];
        //         if (file_exists($image_file_to_delete)) {
        //             unlink($image_file_to_delete);
        //         }
        //     }
        // }
        // --- End Optional Image Deletion ---

        $sql_delete = "DELETE FROM unapproved_job WHERE `unapproved_job`.`id` = '$id';";
        $result_del = mysqli_query($conn, $sql_delete);
        if ($result_del) {
            echo '<script> alert("Delete successful."); window.location.href="unapproved_job.php";</script>';
        } else {
            echo '<script> alert("Error deleting job: ' . mysqli_error($conn) . '"); window.location.href="unapproved_job.php";</script>';
        }
        exit();
    } else { // Add (Approve)
        $sql_getdata = "SELECT * FROM unapproved_job WHERE `unapproved_job`.`id`='$id';";
        $result_getdata = mysqli_query($conn, $sql_getdata);
        $row_getdata = mysqli_fetch_assoc($result_getdata);

        if ($row_getdata) {
            // Sanitize all data before insertion
            $userId = mysqli_real_escape_string($conn, $row_getdata['userid']);
            $ctg = mysqli_real_escape_string($conn, $row_getdata['category']);
            $title = mysqli_real_escape_string($conn, $row_getdata['title']);
            $jobType = mysqli_real_escape_string($conn, $row_getdata['job_type']);
            $company = mysqli_real_escape_string($conn, $row_getdata['company']);
            $location = mysqli_real_escape_string($conn, $row_getdata['location']);
            $price = mysqli_real_escape_string($conn, $row_getdata['price']);
            $exitDay = mysqli_real_escape_string($conn, $row_getdata['exit_day']);
            $responsibilities = mysqli_real_escape_string($conn, $row_getdata['responsibilities']);
            $requirements = mysqli_real_escape_string($conn, $row_getdata['requirement']);
            $job_image_filename = mysqli_real_escape_string($conn, $row_getdata['job_image']);

            $sql_setdata = "INSERT INTO `jobtable` (`userId`,`category`, `title`, `jobType`, `company`, `location`, `price`, `exitDay`, `responsibilities`, `requirements`, `job_image`)
                           VALUES ('$userId','$ctg', '$title', '$jobType', '$company', '$location', '$price', '$exitDay', '$responsibilities', '$requirements', '$job_image_filename')";
            $result_setdata = mysqli_query($conn, $sql_setdata);
            
            if ($result_setdata) {
                $sql_delete_unapproved = "DELETE FROM unapproved_job WHERE `unapproved_job`.`id` = '$id'";
                $result_delete_unapproved = mysqli_query($conn, $sql_delete_unapproved);
                if ($result_delete_unapproved) {
                    echo '<script> alert("Job approved successfully."); window.location.href="unapproved_job.php";</script>';
                } else {
                    echo '<script> alert("Job approved, but failed to remove from unapproved list: ' . mysqli_error($conn) . '"); window.location.href="unapproved_job.php";</script>';
                }
            } else {
                echo '<script> alert("Error approving job: ' . mysqli_error($conn) . '"); window.location.href="unapproved_job.php";</script>';
            }
        } else {
            echo '<script> alert("Error: Could not retrieve job data for approval."); window.location.href="unapproved_job.php";</script>';
        }
        exit();
    }
}
?>
<?php
$note = "";
$base_sql = "SELECT * FROM unapproved_job"; 
$current_search_term = isset($_GET["search"]) ? htmlspecialchars($_GET["search"]) : ""; // htmlspecialchars for display
$current_filter = isset($_GET["filter"]) ? $_GET["filter"] : "All Type";

// Use mysqli_real_escape_string for values going into SQL
$search_sql_safe = isset($_GET["search"]) ? mysqli_real_escape_string($conn, $_GET["search"]) : "";
$filter_sql_safe = mysqli_real_escape_string($conn, $current_filter);


if (!empty($search_sql_safe) || $current_filter !== "All Type") {
    $conditions = [];
    if (!empty($search_sql_safe)) {
        $conditions[] = "(title LIKE '%$search_sql_safe%' OR company LIKE '%$search_sql_safe%' OR location LIKE '%$search_sql_safe%' OR price LIKE '%$search_sql_safe%' OR exitDay LIKE '%$search_sql_safe%')";
    }
    if ($current_filter === "Full Time" || $current_filter === "Part Time") {
        // $filter_sql_safe is already escaped if needed, but we know the specific values here.
        $conditions[] = "job_type = '" . $filter_sql_safe . "'";
    }
    if (!empty($conditions)) {
        $sql = $base_sql . " WHERE " . implode(" AND ", $conditions);
    } else {
        // This case might not be reached if the outer if condition implies at least one filter is active.
        // However, for completeness, if $current_filter was something other than "All Type", "Full Time", or "Part Time"
        // and $search_sql_safe was empty, $conditions would be empty.
        $sql = $base_sql; 
    }
} else {
    $sql = $base_sql;
}
$result_jobs = mysqli_query($conn, $sql); 
if (!$result_jobs) {
    $note = "Error fetching data: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unapproved Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/job_list.css"> 
    <link rel="stylesheet" href="../../CSS/header.css"> 

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
        /* Image container with padding */
        .job-card .card-img-top-container { 
            position: relative; 
            padding: 1.25rem; /* This creates the "padding" effect around the image */
            background-color: #f8f9fa; /* Light background for the padded area, or match card if preferred */
            height: 220px; /* Adjust height of container if padding makes image too small */
        }
        .job-card .card-img-top {
            width: 100%;
            height: 100%; /* Image fills the padded container's inner dimensions */
            object-fit: cover;
            border-radius: 8px; /* Optional: slight rounding for the image itself */
        }
        
        /* Admin Buttons Styling - positioned relative to card-img-top-container */
        .job-card .admin-actions {
            position: absolute;
            top: 1.25rem;    /* Aligns with the container's padding */
            left: 1.25rem;   /* Aligns with the container's padding */
            right: 1.25rem;  /* Aligns with the container's padding */
            z-index: 10;
            display: flex;
            justify-content: space-between; 
        }
        .job-card .admin-actions button {
            padding: 6px 10px; border: none; border-radius: 5px; cursor: pointer;
            font-size: 0.9rem; line-height: 1; transition: opacity 0.2s ease-in-out;
        }
        .job-card .admin-actions button:hover { opacity: 0.85; }
        .job-card .admin-actions button.action-remove { background-color: #dc3545; color: white; }
        .job-card .admin-actions button.action-approve { background-color: #198754; color: white; }
        .job-card .admin-actions button i { color: white !important; font-size: 1em; }

        /* Job Title Overlay - positioned relative to card-img-top-container */
        .job-card .job-title-overlay {
            position: absolute;
            bottom: 1.25rem; /* Aligns with the container's padding */
            left: 1.25rem;   /* Aligns with the container's padding */
            background-color: rgba(255, 255, 255, 0.92); color: #0d6efd;
            padding: 0.5rem 1rem; border-radius: 8px; font-weight: bold;
            font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            max-width: calc(100% - (2 * 1.25rem)); /* Max width considering left/right insets */
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
        .job-card .stats {
            margin-left: auto; display: flex; align-items: center;
            font-size: 0.8rem; color: #6c757d;
        }
        .job-card .stats .stat-item { margin-left: 10px; display: flex; align-items: center; }
        .job-card .stats .stat-item i { margin-right: 4px; }
        .job-card .btn-more-details {
            border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; margin-top: auto; 
        }
        .job-card .btn-more-details i { margin-left: 5px; }
        .job-card .placeholder-text p {
            font-size: 0.8em; color: red; padding: 10px; text-align: center;
            background-color: #fff3cd; border: 1px solid #ffeeba;
            border-radius: .25rem; margin-top: 10px;
        }
        #phpmg { text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem; color: #333; }
        
        /* Search Bar Styling */
        .search-filter-form .form-control, 
        .search-filter-form .form-select {
            /* You can add custom heights or styles if needed, but Bootstrap defaults are usually fine */
        }
    </style>
</head>
<body>

    <?php include_once("admin_navbar.php"); ?>

    <div class="container mt-4">
        <!-- Search form (commented out as in original) -->
        <!-- 
        <form action="unapproved_job.php" method="get" class="mb-4 search-filter-form">
            <div class="row justify-content-center g-3">
                <div class="col-md-5 col-lg-4">
                    <input class="form-control" type="search" name="search" 
                           placeholder="Search title, company..." 
                           value="<?php echo $current_search_term; // This is htmlspecialchars'd version ?>">
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="filter" class="form-select">
                        <option value="All Type" <?php echo ($current_filter == 'All Type') ? 'selected' : ''; ?>>All Job Types</option>
                        <option value="Full Time" <?php echo ($current_filter == 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo ($current_filter == 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </div>
        </form> 
        -->

        <h3 id="phpmg">
            <?php 
            if ($current_filter !== 'All Type' && !empty($current_search_term)) {
                echo "Results for \"" . $current_search_term . "\" (Type: " . htmlspecialchars($current_filter) . ")";
            } else if (!empty($current_search_term)) {
                echo "Search Results for \"" . $current_search_term . "\"";
            } else if ($current_filter !== 'All Type') {
                echo "Filtered by: " . htmlspecialchars($current_filter);
            } else {
                echo "All Unapproved Jobs";
            }
            ?>
        </h3>
    
        <div class="row">
            <?php
            if ($result_jobs && mysqli_num_rows($result_jobs) > 0) {
                while ($row = mysqli_fetch_assoc($result_jobs)) {
                    $image_base_path = "../../"; 
                    $default_placeholder_url = $image_base_path . "image/uploads/placeholder.png"; // Ensure this placeholder exists
                    $full_image_url = $default_placeholder_url;
                    $job_image_db_path_part = ""; 
                    $server_path_to_check_display = "";
                    $display_image = false;
                    $image_not_found_message = "";

                    if (!empty($row['job_image'])) {
                        $job_image_db_path_part = htmlspecialchars($row['job_image']);
                        $candidate_full_image_url = $image_base_path . $job_image_db_path_part;
                        // IMPORTANT: Adjust '/WorkWise/' if your project DOCUMENT_ROOT structure is different.
                        $server_path_to_check_display = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $job_image_db_path_part;
                        
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
                        <form action="unapproved_job.php" method="get" class="h-100">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            
                            <div class="card job-card h-100">
                                <div class="card-img-top-container">
                                    <img src="<?php echo $full_image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?> image">
                                    
                                    <div class="admin-actions">
                                        <button type="submit" name="remove" value="remove" class="action-remove" onclick="return confirm('Are you sure you want to remove this job?');" title="Remove Job">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button type="submit" name="add" value="add" class="action-approve" onclick="return confirm('Are you sure you want to approve this job?');" title="Approve Job">
                                            <i class="fas fa-check"></i>
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
                                        <i class="fas fa-briefcase fa-fw"></i>
                                        <span class="detail-label">Job type:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['job_type']); ?></span>
                                        <div class="stats">
                                            <span class="stat-item"><i class="fas fa-eye"></i> 102</span>
                                            <span class="stat-item"><i class="fas fa-envelope"></i> 56</span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-building fa-fw"></i>
                                        <span class="detail-label">Company:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($row['company']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-user-tie fa-fw"></i>
                                        <span class="detail-label">Exp:</span>
                                        <span class="detail-value">2 years+</span>
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

                                    <a href="more_details.php?jobId=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-outline-primary w-100 mt-3 btn-more-details">
                                        More Details <i class="fas fa-ellipsis-h"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php
                } // end while
            } else {
                 if (empty($note)) { $note = "No unapproved jobs found matching your criteria."; }
                 echo '<div class="col-12"><div class="alert alert-warning text-center" role="alert"><h2 class="alert-heading">No Results</h2><p>' . htmlspecialchars($note) . '</p></div></div>';
            }
            ?>
        </div> 
    </div> 

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>