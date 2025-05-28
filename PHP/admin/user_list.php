<?php include("../conn.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - Admin</title>
    <link rel="stylesheet" href="../../CSS/user_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 25px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .page-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
            font-size: 1.8em;
        }
        .filter-form {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-form button {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .filter-form button.active,
        .filter-form button:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .filter-form button i {
            margin-right: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 6px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #e0e0e0;
        }
        table th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }
        table td {
            color: #555;
        }
        table td.id-number {
            font-weight: 500;
            color: #2c3e50;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        .action-buttons form {
            display: flex;
            gap: 8px;
            margin: 0;
        }
        .action-buttons button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: flex;
            align-items: center;
        }
        .action-buttons button i {
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .action-buttons button:active {
            transform: translateY(1px);
        }
        .no-users-message td {
            text-align: center;
            padding: 25px;
            font-style: italic;
            color: #777;
            background-color: #fdfdfd;
        }
    </style>
</head>
<body>
    <?php include_once 'admin_navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">User Management</h1>

        <form action="user_list.php" method="get" class="filter-form">
            <?php
                $current_filter = 'all'; // Default
                if (isset($_GET['admin'])) { $current_filter = 'admin'; }
                elseif (isset($_GET['user'])) { $current_filter = 'user'; }
            ?>
            <button type="submit" name="all" value="all" class="<?php echo ($current_filter == 'all' ? 'active' : ''); ?>">
                <i class="fas fa-list"></i> All
            </button>
            <button type="submit" name="user" value="user" class="<?php echo ($current_filter == 'user' ? 'active' : ''); ?>">
                <i class="fas fa-user"></i> Users
            </button>
            <button type="submit" name="admin" value="admin" class="<?php echo ($current_filter == 'admin' ? 'active' : ''); ?>">
                <i class="fas fa-user-shield"></i> Admin
            </button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone (from latest app)</th> <!-- Updated Header -->
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Base part of the SQL query
                $sql_select = "SELECT u.userid, u.fName, u.lName, u.email, u.role, 
                                      (SELECT app.applicant_phone 
                                       FROM applications app 
                                       WHERE app.userid = u.userid 
                                       ORDER BY app.application_id DESC 
                                       LIMIT 1) AS latest_phone";
                $sql_from = " FROM users u";
                $sql_where = ""; // Initialize WHERE clause
                $sql_order = " ORDER BY u.userid DESC";

                if (isset($_GET['admin'])) {
                    $role_filter = 'admin';
                    $sql_where = " WHERE u.role = '$role_filter'";
                } elseif (isset($_GET['user'])) {
                    $role_filter = 'user';
                    $sql_where = " WHERE u.role = '$role_filter'";
                }
                
                $sql = $sql_select . $sql_from . $sql_where . $sql_order;
                
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                        <tr>
                            <td class="id-number"><?php echo $count; ?></td>
                            <td class="id-number">WW<?php echo htmlspecialchars($row['userid']); ?></td>
                            <td><?php echo htmlspecialchars($row['fName']) . ' ' . htmlspecialchars($row['lName']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php 
                                // Phone number is now aliased as 'latest_phone' from the subquery
                                echo !empty($row['latest_phone']) ? htmlspecialchars($row['latest_phone']) : 'N/A'; 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>
                            <td class="action-buttons">
                                <form action="renter_password.php" method="GET" style="margin:0;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['userid']); ?>">
                                    <button type="submit" name="edit" value="edit" class="btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    <button type="submit" name="delete" value="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                <?php 
                        $count++;
                    }
                } else {
                    $colspan = 7;
                    echo "<tr class='no-users-message'><td colspan='$colspan'>";    
                    if (!$result) {
                        echo "Error fetching users: " . mysqli_error($conn);
                    } else {
                        echo "No users found matching this filter.";
                    }
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>