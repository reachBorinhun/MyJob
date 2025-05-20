<?php include("../conn.php"); ?>

<!DOCTYPE html>
<html lang="en">

<?php 
    // Process approval request
    if (isset($_POST['approve'])) {
        $userId = $_POST['id']; // Get the hidden input
        if ($userId == 1) {
            echo "User ID 1 is already approved.";
        } else {
            $sql = "UPDATE apply_job SET approve = 1 WHERE userid = '$userId'";
            $run = mysqli_query($conn, $sql);
            if ($run) {
                echo "<script>alert('User approved successfully'); window.location.href='user_apply.php';</script>";
            } else {
                echo "Error updating: " . mysqli_error($conn);
            }
        }
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/user_apply.css">
    <title>User Apply</title>
</head>

<?php include_once 'admin_navbar.php'; ?>

<body>
    <h1>User Apply</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>User ID</th>
                <th>Email</th>
                <th>Job Title</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT 
                    apply_job.userid,
                    apply_job.approve,
                    users.email,
                    users.userid AS user_id,
                    jobtable.title AS job_title
                FROM apply_job
                JOIN users ON apply_job.userid = users.userid
                JOIN jobtable ON apply_job.jobid = jobtable.jobid";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td>WW<?php echo $row['userid']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['job_title']; ?></td>
                    <td>
                        <form action="user_apply.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $row['userid']; ?>">
                            <?php
                            if ($row['approve'] == 0) {
                                echo '<button type="submit" name="approve" value="approve">Approve</button>';
                            } else {
                                echo '<span style="color: green;">Approved</span>';
                            }
                            ?>
                        </form>
                    </td>
                </tr>
        <?php
            }
        }
        ?>
        </tbody>
    </table>
</body>

</html>
