<?php
include("../conn.php");
session_start(); // Ensure session is started


// Count for Users
$sql_user = "SELECT count(*) as user_count FROM users";
$result_user = mysqli_query($conn, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);
$user_count = $row_user ? $row_user['user_count'] : 0;


$sql_job_approved = "SELECT count(*) as job_count FROM jobtable"; 
$result_job_approved = mysqli_query($conn, $sql_job_approved);
$row_job_approved = mysqli_fetch_assoc($result_job_approved);
$approved_job_count = $row_job_approved ? $row_job_approved['job_count'] : 0;


$unapproved_job_table_name = "unapproved_job"; // <<<< CONFIRM THIS TABLE NAME
$sql_job_unapproved = "SELECT count(*) as unapproved_job_count FROM `$unapproved_job_table_name`";
$result_job_unapproved = mysqli_query($conn, $sql_job_unapproved);
$unapproved_job_count = 0; // Default to 0
if ($result_job_unapproved) { // Check if query was successful
    $row_job_unapproved = mysqli_fetch_assoc($result_job_unapproved);
    if ($row_job_unapproved) {
        $unapproved_job_count = $row_job_unapproved['unapproved_job_count'];
    }
} else {
    // Query failed, likely because the table doesn't exist.
    // You might want to log this error: mysqli_error($conn)
    // For now, $unapproved_job_count remains 0.
}
// --- END OF JOB COUNTS ---


// Count for User Applications (from applications table - this is correct)
$sql_applications = "SELECT count(*) as applications_count FROM applications";
$result_applications = mysqli_query($conn, $sql_applications);
$row_applications = mysqli_fetch_assoc($result_applications);
$applications_count = $row_applications ? $row_applications['applications_count'] : 0;

// Count for Unread User Feedback (from contact_messages)
$sql_unread_feedback = "SELECT COUNT(*) as unread_feedback_count FROM contact_messages WHERE is_read = 0";
$result_unread_feedback = mysqli_query($conn, $sql_unread_feedback);
$row_unread_feedback = mysqli_fetch_assoc($result_unread_feedback);
$unread_feedback_count = $row_unread_feedback ? $row_unread_feedback['unread_feedback_count'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding-top: 70px; /* Adjust if your navbar height is different */
        }

        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px; /* Space between cards */
            padding: 25px;
            justify-content: center; /* Center cards if they don't fill the row */
        }

        .dashboard-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: #333;
            width: 280px; /* Adjust width as needed */
            height: 150px; /* Fixed height for consistency */
            padding: 20px;
            position: relative;
            overflow: hidden; /* To contain the border pseudo-element */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
        }

        .card-blue::before { background-color: #007bff; }
        .card-green::before { background-color: #28a745; }
        .card-red::before { background-color: #dc3545; }
        .card-yellow::before { background-color: #ffc107; }
        .card-pink::before { background-color: #e83e8c; }
        .card-grey::before { background-color: #adb5bd; }

        .card-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            margin-left: 15px;
        }

        .card-header {
            display: flex;
            align-items: center;
            font-size: 1.1em;
            font-weight: 600;
            color: #555;
        }

        .card-header i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .card-blue .card-header i { color: #007bff; }
        .card-green .card-header i { color: #28a745; }
        .card-red .card-header i { color: #dc3545; }
        .card-yellow .card-header i { color: #ffc107; }
        .card-pink .card-header i { color: #e83e8c; }
        .card-grey .card-header i { color: #6c757d; }

        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 10px;
        }

        .count-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            font-weight: bold;
            color: white;
        }

        .badge-blue { background-color: #007bff; }
        .badge-green { background-color: #28a745; }
        .badge-red { background-color: #dc3545; }
        .badge-yellow { background-color: #ffc107; }
        .badge-pink { background-color: #e83e8c; }

        .card-footer {
            font-size: 1.5em;
            color: #adb5bd;
        }
        .card-footer.view-all-text {
            font-size: 0.9em;
            font-weight: 500;
            color: #6c757d;
        }

        .add-new-job-card .card-body {
            justify-content: center;
        }
        .add-icon {
            font-size: 3em;
            color: #adb5bd;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .add-new-job-card .card-footer {
            display: none;
        }
    </style>
</head>
<body>
    <?php include_once 'admin_navbar.php'; ?>

    <div class="dashboard-container">
        <!-- Users Card -->
        <a href="user_list.php" class="dashboard-card card-blue">
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-users"></i> Users
                </div>
                <div class="card-body">
                    <div class="count-badge badge-blue"><?php echo $user_count; ?></div>
                    <div class="card-footer"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
        </a>

        <!-- Approved Jobs Card -->
        <a href="approved_job.php" class="dashboard-card card-green">
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-file-circle-check"></i> Approved Jobs 
                </div>
                <div class="card-body">
                    <div class="count-badge badge-green"><?php echo $approved_job_count; ?></div>
                    <div class="card-footer"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
        </a>

        <!-- Unapproved Jobs Card -->
        <a href="unapproved_job.php" class="dashboard-card card-red">
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-file-circle-xmark"></i> Unapproved Jobs
                </div>
                <div class="card-body">
                    <div class="count-badge badge-red"><?php echo $unapproved_job_count; ?></div>
                    <div class="card-footer"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
        </a>
        
        <!-- Users Feedback Card -->
        <a href="admin_user_messages.php" class="dashboard-card card-yellow">
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-comment-dots"></i> Users Feedback
                </div>
                <div class="card-body">
                    <div class="count-badge badge-yellow"><?php echo $unread_feedback_count; ?></div>
                    <div class="card-footer view-all-text">View all</div>
                </div>
            </div>
        </a>

        <!-- Users Apply Jobs Card (Using applications table) -->
        <a href="user_apply.php" class="dashboard-card card-pink"> 
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-file-lines"></i> Users Apply Jobs
                </div>
                <div class="card-body">
                    <div class="count-badge badge-pink"><?php echo $applications_count; ?></div>
                    <div class="card-footer"><i class="fas fa-arrow-right"></i></div>
                </div>
            </div>
        </a>
        
        <!-- Add New Job Card -->
        <a href="add_job.php" class="dashboard-card card-grey add-new-job-card">
            <div class="card-content">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i> Add New Job
                </div>
                <div class="card-body">
                    <div class="add-icon"><i class="fas fa-plus"></i></div>
                </div>
                <div class="card-footer"></div>
            </div>
        </a>

    </div>
</body>
</html>