<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php'); // Redirect to login if not logged in
    exit();
}

include("../conn.php"); // Database connection

// --- Configuration ---
$cv_upload_dir = "uploads/cv/"; // Relative to this script's location. Ensure this directory exists and is writable.
// --- End Configuration ---


$userid = (int)$_SESSION['id']; // Ensure userId is an integer

// Auto-populate from session
$default_name = '';
if (isset($_SESSION['fName'])) {
    $default_name .= $_SESSION['fName'];
    if (isset($_SESSION['lName'])) {
        $default_name .= ' ' . $_SESSION['lName'];
    }
}
$default_name = htmlspecialchars(trim($default_name));
$default_email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '';

$jobId = null;
$jobTitle = "Unknown Job";
$note_type = '';
$note_message = '';

// Initialize form_data with defaults (sticky form)
$form_data = [
    'name' => $default_name,
    'email' => $default_email,
    'phone' => '',
    'cover_letter' => ''
];
$errors = [];

// 2. Get Job ID from URL and Fetch Job Title
if (isset($_GET['jobId'])) {
    $jobId_from_get = filter_var($_GET['jobId'], FILTER_VALIDATE_INT);
    if ($jobId_from_get) {
        $jobId = $jobId_from_get; // Assign to $jobId if valid
        // Ensure $conn is valid before using it
        if ($conn) {
            $stmt_job = mysqli_prepare($conn, "SELECT title FROM jobtable WHERE jobId = ?");
            if ($stmt_job) {
                mysqli_stmt_bind_param($stmt_job, "i", $jobId);
                mysqli_stmt_execute($stmt_job);
                $result_job = mysqli_stmt_get_result($stmt_job);
                if ($result_job && mysqli_num_rows($result_job) > 0) {
                    $job_row = mysqli_fetch_assoc($result_job);
                    $jobTitle = htmlspecialchars($job_row['title']);
                } else {
                    $note_type = 'danger';
                    $note_message = "Error: The specified job (ID: " . htmlspecialchars($jobId) . ") could not be found in jobtable.";
                    $jobId = null; // Invalidate jobId if not found
                }
                mysqli_stmt_close($stmt_job);
            } else {
                $note_type = 'danger';
                $note_message = "Error: Could not prepare statement to fetch job title. " . mysqli_error($conn);
                $jobId = null; // Invalidate
            }
        } else {
            $note_type = 'danger';
            $note_message = "Error: Database connection failed. Cannot fetch job details.";
            $jobId = null; // Invalidate
        }
    } else {
        $note_type = 'danger';
        $note_message = "Error: Invalid Job ID provided in URL.";
    }
} else {
    $note_type = 'danger';
    $note_message = "Error: No Job ID specified in URL.";
}

// Display status message from redirection (PRG pattern)
if (isset($_SESSION['application_status_type']) && isset($_SESSION['application_status_message'])) {
    $note_type = $_SESSION['application_status_type'];
    $note_message = $_SESSION['application_status_message'];
    unset($_SESSION['application_status_type'], $_SESSION['application_status_message']);
}


// 3. Handle Form Submission (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $jobId && $conn) { // Also check $conn here
    // Repopulate form_data with POST data for sticky form
    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['cover_letter'] = trim($_POST['cover_letter'] ?? '');

    // Validation
    if (empty($form_data['name'])) {
        $errors['name'] = "Full name is required.";
    }
    if (empty($form_data['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($form_data['phone'])) {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9\s\-\+\(\)]+$/', $form_data['phone'])) { // Basic phone validation
        $errors['phone'] = "Invalid phone number format.";
    }

    // CV File Upload Validation
    if (!isset($_FILES['cvfile']) || $_FILES['cvfile']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors['cvfile'] = "CV/Resume is required.";
    } elseif ($_FILES['cvfile']['error'] != UPLOAD_ERR_OK) {
        $php_upload_errors = array(
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        );
        $err_code = $_FILES['cvfile']['error'];
        $errors['cvfile'] = "Error uploading CV: " . ($php_upload_errors[$err_code] ?? "Unknown error code $err_code");
    } else {
        $allowed_mime_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_mime_type = mime_content_type($_FILES['cvfile']['tmp_name']);
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file_mime_type, $allowed_mime_types)) {
            $errors['cvfile'] = "Invalid file type. Only PDF, DOC, DOCX are allowed.";
        }
        if ($_FILES['cvfile']['size'] > $max_file_size) {
            $errors['cvfile'] = "File is too large. Maximum size is 5MB.";
        }
    }

    if (empty($errors)) {
        // Ensure the CV upload directory exists and is writable
        if (!is_dir($cv_upload_dir)) {
            // Attempt to create it recursively
            if (!mkdir($cv_upload_dir, 0755, true)) { 
                $_SESSION['application_status_type'] = 'danger';
                $_SESSION['application_status_message'] = 'Error: Cannot create CV upload directory. Please check server permissions or contact support.';
                header("Location: Job Application Form.php?jobId=" . urlencode($jobId));
                exit();
            }
        }
        if (!is_writable($cv_upload_dir)){
            $_SESSION['application_status_type'] = 'danger';
            $_SESSION['application_status_message'] = 'Error: The CV upload directory is not writable. Please check server permissions or contact support.';
            header("Location: Job Application Form.php?jobId=" . urlencode($jobId));
            exit();
        }

        $original_cv_filename = basename($_FILES['cvfile']['name']);
        $safe_cv_basename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", pathinfo($original_cv_filename, PATHINFO_FILENAME));
        $cv_extension = strtolower(pathinfo($original_cv_filename, PATHINFO_EXTENSION));
        $cv_filename = "cv_" . $userid . "_" . time() . "_" . $safe_cv_basename . "." . $cv_extension;
        $cv_destination_path_on_server = $cv_upload_dir . $cv_filename;
        

        if (move_uploaded_file($_FILES['cvfile']['tmp_name'], $cv_destination_path_on_server)) {
            // Path to store in DB - relative path from the web root or a specific uploads directory accessible via web
            // If $cv_upload_dir is "uploads/cv/", then $cv_path_for_db will be "uploads/cv/your_file.pdf"
            $cv_path_for_db = $cv_upload_dir . $cv_filename; 
            // If $cv_upload_dir is an absolute path outside webroot, you'd store only $cv_filename and have a script to serve it.
            // For simplicity, assuming $cv_upload_dir is web-accessible.

            $sql_insert = "INSERT INTO applications (jobId, userId, applicant_name, applicant_email, applicant_phone, cover_letter, cv_path, application_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            
            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "iisssss", $jobId, $userid, $form_data['name'], $form_data['email'], $form_data['phone'], $form_data['cover_letter'], $cv_path_for_db);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $_SESSION['application_status_type'] = 'success';
                    $_SESSION['application_status_message'] = 'Your application for "' . $jobTitle . '" has been submitted successfully!';
                    // Clear form data on success
                    $form_data = [ 
                        'name' => $default_name, 
                        'email' => $default_email, 
                        'phone' => '',
                        'cover_letter' => ''
                    ];
                } else {
                    $_SESSION['application_status_type'] = 'danger';
                    $_SESSION['application_status_message'] = "Database error: Could not submit application. Error: " . mysqli_stmt_error($stmt_insert);
                    // Attempt to delete the uploaded CV if DB insert fails
                    if (file_exists($cv_destination_path_on_server)) {
                        unlink($cv_destination_path_on_server);
                    }
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                 $_SESSION['application_status_type'] = 'danger';
                 $_SESSION['application_status_message'] = "Database error: Could not prepare application statement. Error: " . mysqli_error($conn);
                 if (file_exists($cv_destination_path_on_server)) {
                        unlink($cv_destination_path_on_server);
                 }
            }
        } else {
            $_SESSION['application_status_type'] = 'danger';
            $_SESSION['application_status_message'] = "File system error: Could not move uploaded CV to destination. Check folder permissions for '{$cv_upload_dir}'.";
        }
        header("Location: Job Application Form.php?jobId=" . urlencode($jobId)); // Use urlencode for safety
        exit();

    } else { 
        // Errors exist, so set note_type and note_message to display them
        $note_type = 'danger';
        // $note_message = "Please correct the errors highlighted below and try again."; // General message
        // Specific errors will be listed below the general message via the $errors array in the HTML
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$conn) {
    $note_type = 'danger';
    $note_message = "Critical Error: Database connection is not available. Cannot process application.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo $jobTitle; ?> - WorkWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .application-form-card {
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .card-header {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .card-header-title { 
            font-size: 1.25rem; 
            font-weight: 600; 
        }
        .form-label.fw-bold {
            color: #333;
            margin-bottom: 0.3rem;
        }
        .form-control {
            border-radius: 0.375rem; /* Standard Bootstrap radius */
            padding: 0.5rem 0.75rem; /* Adjust padding if needed */
        }
        .form-control.is-invalid {
            border-color: #dc3545; 
            background-image: none; /* Remove Bootstrap's default invalid icon if you prefer cleaner look */
        }
        .invalid-feedback { 
            display: block; 
            font-size: 0.875em;
        } 
        .form-control:focus {
            border-color: #0d6efd; /* Bootstrap primary blue */
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
         .btn-submit-application {
            font-size: 1rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        /* --- Icon Spacing Adjustments --- */
        .card-header-title > .fas,
        .card-header-title > .far {
            margin-right: 0.5rem; /* Space between icon and text in header */
            vertical-align: middle; 
        }

        .form-label > .fas,
        .form-label > .far {
            margin-right: 0.4rem; /* Space between icon and text in labels */
            vertical-align: middle; 
        }

        /* This existing rule is good for buttons */
        .btn .fas, .btn .far { 
            margin-right: 0.5rem;
            vertical-align: text-bottom; /* Or middle, or -0.125em as Bootstrap often uses */
        }
        /* --- End Icon Spacing Adjustments --- */

        .alert ul {
            padding-left: 1.2rem; /* Indent list items in alert */
        }
        .alert li small {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php 
    // Ensure login_navbar.php path is correct
    // Assuming it's in the same directory as this script
    if (file_exists("login_navbar.php")) {
        include_once("login_navbar.php"); 
    } else {
        echo "<p style='color:red; text-align:center; background:yellow; padding:5px;'>Error: login_navbar.php not found at expected location.</p>";
    }
    ?>

    <div class="container mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card application-form-card">
                    <div class="card-header bg-primary text-white py-3"> 
                        <h5 class="mb-0 card-header-title text-center"> 
                            <i class="fas fa-file-alt"></i>Apply for: <?php echo $jobTitle; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($note_message)): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($note_type); ?> alert-dismissible fade show" role="alert">
                            <?php echo $note_message; // This is already HTML escaped where it's set for jobTitle, or it's a system message ?>
                            <?php if ($note_type === 'danger' && !empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST"): ?>
                                <hr class="my-2">
                                <p class="mb-1 fw-bold">Please correct the following issues:</p>
                                <ul class="mb-0">
                                <?php foreach ($errors as $field => $specific_error): ?>
                                    <li><small><strong><?php echo htmlspecialchars(ucfirst($field)); // e.g., Name, Email ?>:</strong> <?php echo htmlspecialchars($specific_error); ?></small></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <?php 
                        $show_form = false;
                        // Logic to show form:
                        // 1. Must have a valid jobId.
                        // 2. If it's a successful submission, don't show form.
                        // 3. If it's a POST request (meaning a submission attempt), always show form (to show errors or if it was a non-critical GET error before POST).
                        // 4. If it's a GET request and there was a critical error fetching job details, don't show.
                        if ($jobId) { 
                            if ($note_type === 'success' && isset($_SESSION['application_status_type'])) { // Check session flag to confirm it was THIS request's success
                                // Form successfully submitted, message shown, don't reshow form
                                $show_form = false;
                            } else {
                                $show_form = true;
                            }
                        }
                        ?>

                        <?php if ($show_form): ?>
                        <form action="Job Application Form.php?jobId=<?php echo htmlspecialchars($jobId); ?>" method="post" enctype="multipart/form-data" novalidate>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold"><i class="fas fa-user text-primary"></i>Full Name</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold"><i class="fas fa-envelope text-primary"></i>Email Address</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label fw-bold"><i class="fas fa-phone text-primary"></i>Phone Number</label>
                                <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>" required>
                                <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="cover_letter" class="form-label fw-bold"><i class="fas fa-file-signature text-primary"></i>Cover Letter</label>
                                <textarea class="form-control <?php echo isset($errors['cover_letter']) ? 'is-invalid' : ''; ?>" id="cover_letter" name="cover_letter" rows="5" placeholder="Briefly tell us why you're a good fit for this role..."><?php echo htmlspecialchars($form_data['cover_letter']); ?></textarea>
                                <?php if (isset($errors['cover_letter'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['cover_letter']); ?></div><?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="cvfile" class="form-label fw-bold"><i class="fas fa-paperclip text-primary"></i>Upload CV/Resume</label>
                                <input type="file" class="form-control <?php echo isset($errors['cvfile']) ? 'is-invalid' : ''; ?>" id="cvfile" name="cvfile" accept=".pdf,.doc,.docx" required>
                                <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX. Max size: 5MB.</small>
                                <?php if (isset($errors['cvfile'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errors['cvfile']); ?></div><?php endif; ?>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end align-items-center"> 
                                <a href="more_details.php?jobId=<?php echo htmlspecialchars($jobId); ?>" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-submit-application">
                                    <i class="fas fa-paper-plane"></i>Submit Application
                                </button>
                            </div>

                        </form>
                        <?php elseif (!$jobId && $note_type === 'danger'): // Only show this if jobId is invalid AND it's a danger message ?>
                            <div class="text-center">
                                <!-- The $note_message for invalid/missing jobId is already shown above -->
                                <p class="mt-3">Please select a valid job to apply for.</p>
                                <a href="user.php" class="btn btn-primary"><i class="fas fa-list"></i>Browse Jobs</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Ensure login_footer.php path is correct
    if (file_exists("login_footer.php")) {
        include_once('login_footer.php'); 
    } else {
        echo "<p style='color:red; text-align:center; background:yellow; padding:5px;'>Error: login_footer.php not found at expected location.</p>";
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>