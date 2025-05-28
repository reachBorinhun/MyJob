<?php include("../conn.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Added for responsiveness -->
    <title>User Applications</title>
    <!-- Link to your existing CSS if it contains base styles or navbar styles -->
    <link rel="stylesheet" href="../../CSS/user_apply.css"> 
    <style>
        /* General Body Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6; /* Lighter, more neutral background */
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Container for the main content */
        .container {
            width: 90%;
            max-width: 1200px; /* Max width for larger screens */
            margin: 30px auto;
            padding: 25px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Page Title */
        h1 {
            color: #2c3e50; /* Darker, more professional blue/grey */
            margin-bottom: 25px;
            padding-bottom: 15px;
            font-size: 2em; /* Larger title */
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse; /* Still useful, but borders managed differently */
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); /* Softer shadow for table */
            border-radius: 6px; /* Rounded corners for the table wrapper (if any) or table itself */
            overflow: hidden; /* Important for rounded corners on table if borders are inside */
        }

        table th, table td {
            padding: 12px 15px; /* Increased padding for better readability */
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #e0e0e0; /* Lighter border for rows */
        }

        table th {
            background-color: #3498db; /* A pleasant blue for headers */
            color: white;
            font-weight: 600; /* Slightly bolder */
            text-transform: uppercase; /* Uppercase headers for distinction */
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        table td {
            color: #555; /* Slightly softer text color for data */
        }

        /* Alternating row colors for better scan-ability */
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Hover effect for rows */
        table tbody tr:hover {
            background-color: #f1f1f1; /* Subtle hover indication */
        }
        
        /* Last row no bottom border */
        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* "Not Available" text styling */
        .na-text {
            color: #95a5a6; /* Muted color for N/A text */
            font-style: italic;
        }

        /* Status Badge Styling */
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px; /* Pill shape */
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 80px; /* Ensure consistent width for badges */
        }

        .status-approved {
            background-color: #2ecc71; /* Green for approved */
            color: white;
        }
        
        /* Action Button Styling */
        .action-button {
            background-color: #e67e22; /* An orange for "needs action" */
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease;
        }

        .action-button:hover {
            background-color: #d35400; /* Darker orange on hover */
            transform: translateY(-1px); /* Slight lift on hover */
        }
        .action-button:active {
            transform: translateY(0px); /* Press down effect */
        }

        /* Form inside table cell - remove default margins */
        td form {
            margin: 0;
            display: inline-block; /* To align button correctly if needed */
        }

        /* Alert messages (PHP generated) - Basic Styling */
        .alert-message { /* You would echo this class in your PHP script>alert(...) wrapper */
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 0.95em;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* "No applications found" message */
        .no-applications-message td {
            text-align: center;
            padding: 25px;
            font-style: italic;
            color: #777;
            background-color: #fdfdfd; /* Slightly different background */
        }

    </style>
</head>

<body>
<?php include_once 'admin_navbar.php'; // Assuming this is styled or you will style it ?>

<div class="container">
    <h1>User Applications</h1>

    <?php
    // Handle approval and display messages
    if (isset($_POST['approve'])) {
        $applicationIdToUpdate = mysqli_real_escape_string($conn, $_POST['application_id_for_update']);
        $sql_update = "UPDATE applications SET approve = 1 WHERE application_id = '$applicationIdToUpdate'"; 
        $run_update = mysqli_query($conn, $sql_update);

        // Instead of script alert, you can display a styled message
        // For this example, I'll keep the script alert for immediate redirection
        // But in a real app, you might show a message on the page itself before redirecting or via session flash messages
        if ($run_update) {
            echo "<script>alert('Application approved successfully.'); window.location.href='user_apply.php';</script>";
            // Example for inline message (would require removing the redirect for it to be seen):
            // echo "<div class='alert-message alert-success'>Application approved successfully.</div>";
        } else {
            echo "<script>alert('Failed to approve application. Error: " . mysqli_error($conn) . "'); window.location.href='user_apply.php';</script>";
            // Example for inline message:
            // echo "<div class='alert-message alert-danger'>Failed to approve application. Error: " . mysqli_error($conn) . "</div>";
        }
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Email</th>
                <th>Job Title</th>
                <th>Phone Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "
                SELECT 
                    app.application_id,
                    app.approve AS is_approved,
                    u.email,
                    jt.title AS job_title,
                    app.applicant_phone 
                FROM applications app
                INNER JOIN users u ON app.userid = u.userid
                INNER JOIN jobtable jt ON app.jobid = jt.jobid
                ORDER BY app.application_id DESC
            ";

            $result = mysqli_query($conn, $sql); 
            $count = 1;

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $count++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                    
                    // Apply class for 'Ot mean lek'
                    echo "<td>" . (!empty($row['applicant_phone']) ? htmlspecialchars($row['applicant_phone']) : '<span class="na-text">Ot mean lek</span>') . "</td>";

                    echo "<td>";
                    if ($row['is_approved'] == 1) {
                        // Use the new status badge
                        echo "<span class='status-badge status-approved'>Approved</span>";
                    } else {
                        echo "<form method='POST' style='margin: 0;'>
                                <input type='hidden' name='application_id_for_update' value='" . $row['application_id'] . "'>
                                <button type='submit' name='approve' value='approve' class='action-button'>Approve</button>
                              </form>";
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            } else {
                $colspan = 5; // Number of columns
                if (!$result) {
                    echo "<tr class='no-applications-message'><td colspan='$colspan'>Error fetching applications: " . mysqli_error($conn) . "</td></tr>";
                } else {
                    echo "<tr class='no-applications-message'><td colspan='$colspan'>No applications found.</td></tr>";
                }
            }

            mysqli_close($conn);
            ?>
        </tbody>
    </table>
</div> <!-- /.container -->
</body>
</html>