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
$buttonColor = '#bbbebb'; // Default bookmark button color
$note = ""; // For displaying messages (e.g., bookmark success/error)

// 1. Get Job ID from URL
if (isset($_GET['jobId'])) {
    $jobId = filter_var($_GET['jobId'], FILTER_VALIDATE_INT);
    if ($jobId === false) {
        // Invalid Job ID format
        $jobId = null; // Ensure it's null if invalid
        $note = "<p class='error-message'>Error: Invalid Job ID format.</p>";
    }
}

if (!$jobId) {
    // If jobId is still null (not provided or invalid)
    // We can't proceed without a Job ID.
    // You might want to redirect or show a more comprehensive error page.
    // For now, we'll set a note and the page will show limited info.
    if (empty($note)) { // Avoid overwriting previous error message
         $note = "<p class='error-message'>Error: Job ID not provided or invalid.</p>";
    }
}

// 2. Handle Bookmark Toggle Action (if any) - PRG Pattern
if ($jobId && isset($_GET['action']) && $_GET['action'] === 'toggle_bookmark') {
    // Check current bookmark status
    $sql_check_bm = "SELECT COUNT(*) as count FROM bmjob WHERE userId = ? AND jobId = ?";
    $stmt_check_bm = mysqli_prepare($conn, $sql_check_bm);
    mysqli_stmt_bind_param($stmt_check_bm, "ii", $userid, $jobId);
    mysqli_stmt_execute($stmt_check_bm);
    $result_check_bm = mysqli_stmt_get_result($stmt_check_bm);
    $is_bookmarked = false;
    if ($result_check_bm && $row_check_bm = mysqli_fetch_assoc($result_check_bm)) {
        if ($row_check_bm['count'] > 0) {
            $is_bookmarked = true;
        }
    }
    mysqli_stmt_close($stmt_check_bm);

    if ($is_bookmarked) {
        // Already bookmarked, so remove it
        $sql_toggle_bm = "DELETE FROM bmjob WHERE userId = ? AND jobId = ?";
        $status_message = "removed";
    } else {
        // Not bookmarked, so add it
        $sql_toggle_bm = "INSERT INTO bmjob (userId, jobId) VALUES (?, ?)";
        $status_message = "added";
    }

    $stmt_toggle_bm = mysqli_prepare($conn, $sql_toggle_bm);
    mysqli_stmt_bind_param($stmt_toggle_bm, "ii", $userid, $jobId);
    if (mysqli_stmt_execute($stmt_toggle_bm)) {
        // Success, redirect to clean URL (PRG pattern)
        header("Location: more_details.php?jobId=" . $jobId . "&bookmark_status=" . $status_message);
        exit();
    } else {
        $note = "<p class='error-message'>Error updating bookmark: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
    }
    mysqli_stmt_close($stmt_toggle_bm);
}

// Display bookmark status message if redirected
if (isset($_GET['bookmark_status'])) {
    if ($_GET['bookmark_status'] === 'added') {
        $note = "<p class='success-message'>Job bookmarked successfully!</p>";
    } elseif ($_GET['bookmark_status'] === 'removed') {
        $note = "<p class='info-message'>Job bookmark removed.</p>";
    }
}


// 3. Fetch Job Details (Always, if jobId is valid)
if ($jobId) {
    $sql_job = "SELECT * FROM jobtable WHERE jobId = ?";
    $stmt_job = mysqli_prepare($conn, $sql_job);

    if ($stmt_job) {
        mysqli_stmt_bind_param($stmt_job, "i", $jobId);
        mysqli_stmt_execute($stmt_job);
        $result_job = mysqli_stmt_get_result($stmt_job);

        if ($result_job && mysqli_num_rows($result_job) > 0) {
            $row = mysqli_fetch_assoc($result_job); // Populate $row with job details
        } else {
            if (empty($note)) { // Don't overwrite existing notes like bookmark status
                $note .= "<p class='error-message'>Job details not found for the provided ID.</p>";
            }
            // $row remains null
        }
        mysqli_stmt_close($stmt_job);
    } else {
        $note .= "<p class='error-message'>Error preparing job details query: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        // $row remains null
    }
}

// 4. Fetch Current Bookmark Status (for button color)
// This should run after any toggle action and after job details are fetched (or attempted)
if ($jobId && $userid) { // Check $userid as well, though it should be set
    $sql_bm_status = "SELECT COUNT(*) as count FROM bmjob WHERE userId = ? AND jobId = ?";
    $stmt_bm_status = mysqli_prepare($conn, $sql_bm_status);
    if ($stmt_bm_status) {
        mysqli_stmt_bind_param($stmt_bm_status, "ii", $userid, $jobId);
        mysqli_stmt_execute($stmt_bm_status);
        $result_bm_status = mysqli_stmt_get_result($stmt_bm_status);
        if ($result_bm_status && $row_bm_status = mysqli_fetch_assoc($result_bm_status)) {
            if ($row_bm_status['count'] > 0) {
                $buttonColor = 'green';
            }
        } // Silently fail on error fetching bookmark status for color to not overwrite primary $note
        mysqli_stmt_close($stmt_bm_status);
    }
}

// mysqli_close($conn); // Optional: Close connection if not persistent or closed elsewhere
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Job Details</title>
    <link rel="stylesheet" href="../../CSS/more_details.css">
    <style>
        /* Basic styling for messages */
        .error-message { color: red; font-weight: bold; }
        .success-message { color: green; font-weight: bold; }
        .info-message { color: blue; }
    </style>
</head>

<body>
    <br><br><br>
    <div class="job_listings">
        <div class="job_row">
            <div class="job">
                <!-- Corrected back button: Assuming user.php is in the same directory -->
                <!-- Adjust 'href' as needed for your directory structure -->
                <a id="back" href="user.php" style="float: inline-start;">
                    <i class="fa fa-chevron-left" style="font-size:20px;"></i>
                </a>

                <?php echo $note; // Display any notes/errors/success messages at the top ?>

                <?php if ($row): // IMPORTANT: Only display job details if $row is populated ?>
                    <form action="more_details.php" method="get" style="display: inline-block; float: right;">
                        <input type="hidden" name="jobId" value="<?php echo htmlspecialchars($jobId); ?>">
                        <input type="hidden" name="action" value="toggle_bookmark">
                        <button id="bookmark" type="submit" title="Toggle Bookmark">
                            <i class="fa fa-bookmark" style="color: <?php echo $buttonColor; ?>;"></i>
                        </button>
                    </form>
                    <h2>
                        <?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?>
                    </h2>
                    
                    <div class="job_details">
                        <div class="details">
                            <table>
                                <tr>
                                    <td class="subtitle">Job Type</td>
                                    <td>
                                        <?php echo htmlspecialchars($row['jobType'] ?? 'N/A'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="subtitle">Company</td>
                                    <td>
                                        <?php echo htmlspecialchars($row['company'] ?? 'N/A'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="subtitle">Location</td>
                                    <td>
                                        <?php echo htmlspecialchars($row['location'] ?? 'N/A'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="subtitle">Price</td>
                                    <td>
                                        <?php echo htmlspecialchars($row['price'] ?? 'N/A'); ?> per monthly
                                    </td>
                                </tr>
                                <tr>
                                    <td class="subtitle">Exit Day:</td>
                                    <td><span style="color: rgba(255, 0, 0, 0.601);">
                                            <?php echo htmlspecialchars($row['exitDay'] ?? 'N/A'); ?>
                                        </span></td>
                                </tr>
                            </table>
                            <br>
                            <p>Responsibilities:</p>
                            <ul>
                                <li>
                                    <?php echo nl2br(htmlspecialchars($row['responsibilities'] ?? 'N/A')); ?>
                                </li>
                            </ul>
                            <br>
                            <p>Requirements:</p>
                            <ul>
                                <li>
                                    <?php echo nl2br(htmlspecialchars($row['requirements'] ?? 'N/A')); ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <center>
                        <form action="Job Application Form.php" method="get">
                            <!-- Ensure $row['jobId'] exists if $row exists, or use $jobId if $row['jobId'] might be missing -->
                            <input type="hidden" name="jobId" value="<?php echo htmlspecialchars($row['jobId'] ?? $jobId); ?>">
                            <button class="apply-btn" type="submit" name="apply_now_button">
                                Apply Now <i class="fas fa-info-circle"></i>
                            </button>
                        </form>
                    </center>

                <?php elseif (!$jobId && empty($note)): // If $jobId was not set initially and no error message is already set ?>
                    <p class='error-message'>Error: No Job ID was specified in the URL. Cannot display details.</p>
                    <p><a href="user.php">Go back to listings</a></p>
                <?php elseif (empty($note)): // General fallback if $row is null and no other specific note was set ?>
                     <p>The requested job details could not be displayed. The job may not exist or there was an error loading the data.</p>
                     <p><a href="user.php">Go back to listings</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>