<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

include("../conn.php"); // Ensure $conn is established here

// Initialize variables
$userid = $_SESSION['id'];
$jobId = null;
$row = null; // This will store job details
$is_bookmarked_for_style = false; // For bookmark button styling
$note_type = ''; // For Bootstrap alert class (success, danger, info)
$note_message = ''; // Message content

// --- Configuration for Images (copied from previous designs, VERIFY PATHS) ---
$project_url_path = '/WorkWise'; // <<< --- CRITICAL: VERIFY AND CHANGE THIS IF NEEDED for your project---
$default_placeholder_image_path_in_project = 'image/uploads/placeholder.png';
// --- End Image Configuration ---

// 1. Get Job ID from URL
if (isset($_GET['jobId'])) {
    $jobId = filter_var($_GET['jobId'], FILTER_VALIDATE_INT);
    if ($jobId === false) {
        $jobId = null;
        $note_type = 'danger';
        $note_message = "Error: Invalid Job ID format.";
    }
}

if (!$jobId && empty($note_message)) { // If jobId is still null and no other error set
    $note_type = 'danger';
    $note_message = "Error: Job ID not provided or invalid.";
}

// 2. Handle Bookmark Toggle Action (if any) - PRG Pattern
if ($jobId && isset($_GET['action']) && $_GET['action'] === 'toggle_bookmark') {
    // Check current bookmark status
    $sql_check_bm = "SELECT COUNT(*) as count FROM bmjob WHERE userId = ? AND jobId = ?";
    $stmt_check_bm = mysqli_prepare($conn, $sql_check_bm);
    mysqli_stmt_bind_param($stmt_check_bm, "ii", $userid, $jobId);
    mysqli_stmt_execute($stmt_check_bm);
    $result_check_bm = mysqli_stmt_get_result($stmt_check_bm);
    $is_currently_bookmarked = false;
    if ($result_check_bm && $row_check_bm = mysqli_fetch_assoc($result_check_bm)) {
        if ($row_check_bm['count'] > 0) {
            $is_currently_bookmarked = true;
        }
    }
    mysqli_stmt_close($stmt_check_bm);

    if ($is_currently_bookmarked) {
        $sql_toggle_bm = "DELETE FROM bmjob WHERE userId = ? AND jobId = ?";
        $status_message = "removed";
    } else {
        $sql_toggle_bm = "INSERT INTO bmjob (userId, jobId) VALUES (?, ?)";
        $status_message = "added";
    }

    $stmt_toggle_bm = mysqli_prepare($conn, $sql_toggle_bm);
    mysqli_stmt_bind_param($stmt_toggle_bm, "ii", $userid, $jobId);
    if (mysqli_stmt_execute($stmt_toggle_bm)) {
        header("Location: more_details.php?jobId=" . $jobId . "&bookmark_status=" . $status_message);
        exit();
    } else {
        $note_type = 'danger';
        $note_message = "Error updating bookmark: " . htmlspecialchars(mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_toggle_bm);
}

// Display bookmark status message if redirected
if (isset($_GET['bookmark_status'])) {
    if ($_GET['bookmark_status'] === 'added') {
        $note_type = 'success';
        $note_message = "Job bookmarked successfully!";
    } elseif ($_GET['bookmark_status'] === 'removed') {
        $note_type = 'info';
        $note_message = "Job bookmark removed.";
    }
}


// 3. Fetch Job Details (Always, if jobId is valid and no critical error yet)
if ($jobId && empty($note_type) || ($note_type !== 'danger' && $note_message !== "Error: Job ID not provided or invalid." && $note_message !== "Error: Invalid Job ID format." )) {
    $sql_job = "SELECT * FROM jobtable WHERE jobId = ?";
    $stmt_job = mysqli_prepare($conn, $sql_job);

    if ($stmt_job) {
        mysqli_stmt_bind_param($stmt_job, "i", $jobId);
        mysqli_stmt_execute($stmt_job);
        $result_job = mysqli_stmt_get_result($stmt_job);

        if ($result_job && mysqli_num_rows($result_job) > 0) {
            $row = mysqli_fetch_assoc($result_job); 
        } else {
            if (empty($note_message)) { // Don't overwrite existing notes like bookmark status
                $note_type = 'warning';
                $note_message = "Job details not found for the provided ID.";
            }
        }
        mysqli_stmt_close($stmt_job);
    } else {
        $note_type = 'danger';
        $note_message = "Error preparing job details query: " . htmlspecialchars(mysqli_error($conn));
    }
}

// 4. Fetch Current Bookmark Status (for button style)
if ($jobId && $userid && $row) { // Only if job details were successfully fetched
    $sql_bm_status = "SELECT COUNT(*) as count FROM bmjob WHERE userId = ? AND jobId = ?";
    $stmt_bm_status = mysqli_prepare($conn, $sql_bm_status);
    if ($stmt_bm_status) {
        mysqli_stmt_bind_param($stmt_bm_status, "ii", $userid, $jobId);
        mysqli_stmt_execute($stmt_bm_status);
        $result_bm_status = mysqli_stmt_get_result($stmt_bm_status);
        if ($result_bm_status && $row_bm_status = mysqli_fetch_assoc($result_bm_status)) {
            if ($row_bm_status['count'] > 0) {
                $is_bookmarked_for_style = true;
            }
        }
        mysqli_stmt_close($stmt_bm_status);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - <?php echo isset($row['title']) ? htmlspecialchars($row['title']) : 'WorkWise'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .job-details-card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-top: 2rem;
            margin-bottom: 2rem;
            overflow: hidden; 
        }
        .job-details-card .card-img-top-container {
            position: relative;
            padding: 0; 
            background-color: #f8f9fa; 
            height: 300px; 
            overflow: hidden; 
        }
        .job-details-card .card-img-top {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            border-radius: 0; 
        }
        .job-details-card .job-title-overlay {
            position: absolute;
            bottom: 1rem; 
            left: 1rem;   
            /* MODIFIED: Removed right: 1rem; */
            max-width: 75%; /* MODIFIED: Constrain the width of the overlay box */
            background-color: rgba(0, 0, 0, 0.6); 
            color: #fff;
            padding: 0.6rem 1rem; 
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.25rem; 
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            white-space: normal; /* Allows text inside to wrap */
            overflow: hidden; /* In case content within padding overflows */
        }
        .detail-item {
            display: flex;
            align-items: flex-start; 
            margin-bottom: 1rem; 
            font-size: 1rem; 
        }
        .detail-item i.fa-fw {
            color: #0d6efd; 
            margin-right: 12px;
            width: 1.5em; 
            text-align: center;
            margin-top: 0.15em; 
        }
        .detail-item .detail-label {
            color: #6c757d; 
            margin-right: 8px;
            font-weight: 500;
            min-width: 100px; 
        }
        .detail-item .detail-value {
            color: #212529; 
        }
        .salary-badge {
            background-color: #198754; 
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.95em;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: #343a40; 
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef; 
        }
        .responsibilities-list, .requirements-list {
            list-style: none; 
            padding-left: 0;
        }
        .responsibilities-list li, .requirements-list li {
            padding-left: 1.5em; 
            text-indent: -1.5em;
            margin-bottom: 0.5rem;
        }
        .responsibilities-list li::before, .requirements-list li::before {
            content: "\f00c"; 
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #0d6efd; 
            margin-right: 0.75em;
        }
        .btn-apply-now {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
        }
         .placeholder-text p {
            font-size: 0.8em; color: #c0392b; padding: 8px; text-align: center;
            background-color: #fdedec; border: 1px solid #fadbd8;
            border-radius: .25rem; margin-bottom: 0.75rem; margin-top:0;
        }
    </style>
</head>

<body>
    <?php include_once("login_navbar.php"); ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">

                <?php if (!empty($note_message)): ?>
                <div class="alert alert-<?php echo htmlspecialchars($note_type); ?> alert-dismissible fade show mt-3" role="alert">
                    <?php echo $note_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if ($row): ?>
                    <?php
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
                            $image_not_found_message_for_card = "Job image not found on server. Path: " . htmlspecialchars($job_image_db_value);
                        }
                    } else {
                        $image_not_found_message_for_card = "No image path specified in database for this job.";
                    }
                    ?>
                <div class="card job-details-card">
                     <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                        <a href="user.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Listings
                        </a>
                        <form action="more_details.php" method="get" class="mb-0">
                            <input type="hidden" name="jobId" value="<?php echo htmlspecialchars($jobId); ?>">
                            <input type="hidden" name="action" value="toggle_bookmark">
                            <button type="submit" class="btn <?php echo $is_bookmarked_for_style ? 'btn-primary' : 'btn-outline-primary'; ?> btn-md" title="<?php echo $is_bookmarked_for_style ? 'Remove Bookmark' : 'Add Bookmark'; ?>">
                                <i class="<?php echo $is_bookmarked_for_style ? 'fas' : 'far'; ?> fa-bookmark me-2"></i> 
                                <?php echo $is_bookmarked_for_style ? 'Bookmarked' : 'Bookmark'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <div class="card-img-top-container">
                        <img src="<?php echo $current_image_src_to_display; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title'] ?? 'Job Image'); ?>">
                        <div class="job-title-overlay">
                            <?php echo htmlspecialchars($row['title'] ?? 'Job Details'); ?>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (!$display_actual_image && !empty($image_not_found_message_for_card)): ?>
                            <div class="placeholder-text mb-3">
                                <p><small><i class="fas fa-image me-1"></i><?php echo htmlspecialchars($image_not_found_message_for_card); ?></small></p>
                            </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <i class="fas fa-briefcase fa-fw"></i>
                            <span class="detail-label">Job Type:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['jobType'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-building fa-fw"></i>
                            <span class="detail-label">Company:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['company'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['location'] ?? 'N/A'); ?></span>
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
                            <span class="detail-value">
                                <span class="salary-badge">$<?php echo htmlspecialchars($row['price'] ?? 'N/A'); ?> / month</span>
                            </span>
                        </div>
                         <div class="detail-item">
                            <i class="fas fa-calendar-times fa-fw"></i>
                            <span class="detail-label">Apply Before:</span>
                            <span class="detail-value text-danger fw-bold"><?php echo htmlspecialchars($row['exitDay'] ?? 'N/A'); ?></span>
                        </div>

                        <?php if (!empty($row['responsibilities'])): ?>
                        <h3 class="section-title">Responsibilities</h3>
                        <ul class="responsibilities-list">
                            <?php 
                            $responsibilities = preg_split('/(\r\n|\r|\n)/', $row['responsibilities']);
                            foreach ($responsibilities as $responsibility) {
                                if (!empty(trim($responsibility))) {
                                    echo '<li>' . htmlspecialchars(trim($responsibility)) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                        <?php endif; ?>

                        <?php if (!empty($row['requirements'])): ?>
                        <h3 class="section-title">Requirements</h3>
                        <ul class="requirements-list">
                             <?php 
                            $requirements = preg_split('/(\r\n|\r|\n)/', $row['requirements']);
                            foreach ($requirements as $requirement) {
                                if (!empty(trim($requirement))) {
                                    echo '<li>' . htmlspecialchars(trim($requirement)) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer text-center p-3">
                        <form action="Job Application Form.php" method="get" class="d-grid">
                            <input type="hidden" name="jobId" value="<?php echo htmlspecialchars($row['jobId'] ?? $jobId); ?>">
                            <button class="btn btn-success btn-apply-now" type="submit" name="apply_now_button">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </button>
                        </form>
                    </div>
                </div>

                <?php elseif (empty($note_message)): ?>
                    <div class="alert alert-warning text-center mt-4" role="alert">
                        <h4 class="alert-heading">Job Not Found</h4>
                        <p>The job details you are looking for could not be found. It might have been removed or the ID is incorrect.</p>
                        <hr>
                        <a href="user.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>View Other Jobs
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once('login_footer.php'); ?>

    <?php if ($conn) { mysqli_close($conn); } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>