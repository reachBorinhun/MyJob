<?php
include("../conn.php");
session_start(); // Ensure session is started

// Your existing admin authentication logic should be here or in admin_navbar.php
// For example:
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../login.php');
//     exit();
// }

$sql_user = "SELECT count(*) as user_count FROM users ";
$result_user = mysqli_query($conn, $sql_user);
$row_user = mysqli_fetch_assoc($result_user);

$sql_job = "SELECT count(*) as job_count FROM jobtable ";
$result_job = mysqli_query($conn, $sql_job);
$row_job = mysqli_fetch_assoc($result_job);

$sql_uajob = "SELECT count(*) as unjob_count FROM unapproved_job ";
$result_uajob = mysqli_query($conn, $sql_uajob);
$row_uajob = mysqli_fetch_assoc($result_uajob);

// --- Query to count unread feedback messages ---
$sql_unread_feedback = "SELECT COUNT(*) as unread_feedback_count FROM contact_messages WHERE is_read = 0";
$result_unread_feedback = mysqli_query($conn, $sql_unread_feedback);
$row_unread_feedback = mysqli_fetch_assoc($result_unread_feedback);
$unread_feedback_count = $row_unread_feedback ? $row_unread_feedback['unread_feedback_count'] : 0;
// --- End of new query ---
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../CSS/admin_dashbord.css"> <!-- Your existing CSS -->

    <title>Admin Dashboard</title>
    <style>
        /* Optional: Style for unread count if your CSS doesn't cover it */
        .unread-badge {
            background-color: red;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <?php include_once 'admin_navbar.php'; ?>

    <div class="main_div">
        <a href="user_list.php">
            <div class="sub_div">
                <h1><i class="fa fa-group"> </i> Users</h1>
                <h2><?php echo $row_user['user_count']; ?></h2>
            </div>
        </a>
        <a href="approved_job.php">
            <div class="sub_div">
                <h1><i class="fa fa-group"></i> Approved Job</h1> <!-- Assuming fa-group is a placeholder, consider fa-check-circle -->
                <h2><?php echo $row_job['job_count']; ?></h2>
            </div>
        </a>
        <a href="unapproved_job.php">
            <div class="sub_div">
                <h1>Unapproved Job</h1> <!-- Consider fa-hourglass-half or fa-times-circle -->
                <h2><?php echo $row_uajob['unjob_count']; ?></h2>
            </div>
        </a>
        
        <!-- Updated Users Feedback Link -->
         <a href="admin_user_messages.php"> <!-- <<<< MAKE SURE THIS FILENAME IS CORRECT -->
            <div class="sub_div">
                <h1>Users Feedback <i class="fa fa-comments-o"></i>
                    <?php if ($unread_feedback_count > 0): ?>
                        <span class="unread-badge"><?php echo $unread_feedback_count; ?></span>
                    <?php endif; ?>
                </h1>
                <h2>
                    <?php echo $unread_feedback_count > 0 ? $unread_feedback_count . " Unread" : "View All"; ?>
                </h2>
            </div>
        </a>

        <a href="#"> <!-- Placeholder for User Apply Job -->
            <div class="sub_div">
                <h1>User Apply Job</h1>
                 <h2>----</h2>
            </div>
        </a>
        
        <a href="add_job.php">
            <div class="sub_div">
                <h1>Add new Job</h1>
                <!-- If you want a count or action text in H2: <h2>Action</h2> -->
            </div>
        </a>
        <a href="#"> <!-- Placeholder for Add Notification -->
            <div class="sub_div">
                <h1>Add Notification</h1>
                <!-- If you want a count or action text in H2: <h2>Action</h2> -->
            </div>
        </a>
        <!-- If this hidden div is for layout, ensure its purpose or remove if not needed -->
        <!-- <div class="sub_div hidden"></div> -->
    </div>
</body>
</html>