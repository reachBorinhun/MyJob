<?php
session_start();
include("../conn.php");

$dashboard_link = "admin_dashbord.php"; // PLEASE ADJUST if your dashboard filename/path is different
$status_message = ''; // For displaying messages
$alert_type = '';     // For Bootstrap alert class

// --- HANDLE DELETE ACTION ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $message_id_to_delete = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($message_id_to_delete) {
        $sql_delete = "DELETE FROM contact_messages WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);

        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $message_id_to_delete);
            if (mysqli_stmt_execute($stmt_delete)) {
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    // Redirect to the same page without action parameters to clear them from URL
                    // and show success message via session or just reload. For simplicity, reloading.
                    // For a better UX with flash messages, you'd use sessions here.
                    header("Location: admin_user_messages.php?status=success_deleted");
                    exit();
                } else {
                    // No row deleted (maybe ID didn't exist)
                    header("Location: admin_user_messages.php?status=error_delete_notfound");
                    exit();
                }
            } else {
                error_log("DB Execute Error (delete_message_inline): " . mysqli_error($conn));
                header("Location: admin_user_messages.php?status=error_delete");
                exit();
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            error_log("DB Prepare Error (delete_message_inline): " . mysqli_error($conn));
            header("Location: admin_user_messages.php?status=error_delete");
            exit();
        }
    } else {
        // Invalid ID for delete
        header("Location: admin_user_messages.php?status=error_params");
        exit();
    }
}
// --- END HANDLE DELETE ACTION ---


// --- HANDLE TOGGLE READ/UNREAD STATUS (Moved from separate file for consolidation) ---
if (isset($_GET['action']) && ($_GET['action'] === 'read' || $_GET['action'] === 'unread') && isset($_GET['id'])) {
    $message_id_toggle = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $toggle_action = $_GET['action'];

    if ($message_id_toggle) {
        $new_status = ($toggle_action === 'read') ? 1 : 0;
        $sql_toggle = "UPDATE contact_messages SET is_read = ? WHERE id = ?";
        $stmt_toggle = mysqli_prepare($conn, $sql_toggle);

        if ($stmt_toggle) {
            mysqli_stmt_bind_param($stmt_toggle, "ii", $new_status, $message_id_toggle);
            if (mysqli_stmt_execute($stmt_toggle)) {
                $status_param = ($toggle_action === 'read') ? 'success_read' : 'success_unread';
                header("Location: admin_user_messages.php?status=" . $status_param);
                exit();
            } else {
                error_log("DB Execute Error (toggle_read_inline): " . mysqli_error($conn));
                header("Location: admin_user_messages.php?status=error_update");
                exit();
            }
            mysqli_stmt_close($stmt_toggle);
        } else {
            error_log("DB Prepare Error (toggle_read_inline): " . mysqli_error($conn));
            header("Location: admin_user_messages.php?status=error_update");
            exit();
        }
    } else {
        header("Location: admin_user_messages.php?status=error_params");
        exit();
    }
}
// --- END HANDLE TOGGLE READ/UNREAD STATUS ---


// Fetch messages for display (this runs after any action might have occurred and redirected)
$sql_select_messages = "SELECT cm.id, cm.name, cm.email, cm.message, cm.submitted_at, cm.is_read,
                               u.fName as user_firstName, u.lName as user_lastName, u.userid as message_user_id
                        FROM contact_messages cm
                        LEFT JOIN users u ON cm.user_id = u.userid
                        ORDER BY cm.is_read ASC, cm.submitted_at DESC";
$result_messages = mysqli_query($conn, $sql_select_messages);


// Handle display of status messages from GET parameters
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success_read':
        case 'success_unread':
            $status_message = 'Message status updated successfully!';
            $alert_type = 'success';
            break;
        case 'success_deleted':
            $status_message = 'Message deleted successfully!';
            $alert_type = 'success';
            break;
        case 'error_update':
        case 'error_delete':
        case 'error_delete_notfound':
        case 'error_params':
            $status_message = 'An error occurred. Please try again or check parameters.';
            $alert_type = 'danger';
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - User Feedback Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ... (your existing styles from the previous version) ... */
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .container { margin-top: 20px; max-width: 900px; }
        .message-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .message-card.unread {
            border-left: 5px solid #ffc107;
        }
        .message-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .message-header .name { font-weight: bold; font-size: 1.1em; color: #333; }
        .message-header .email { color: #007bff; font-size: 0.9em; }
        .message-header .date { font-size: 0.85em; color: #6c757d; }
        .message-body { white-space: pre-wrap; line-height: 1.6; color: #444; }
        .no-messages { text-align: center; padding: 30px; font-size: 1.2em; color: #6c757d; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #333; }
        .badge.bg-warning { color: #000 !important; }
        .action-button-group { margin-top: 10px; }
        .action-button-group .btn { margin-left: 5px; }
    </style>
</head>
<body>
    <?php include_once 'admin_navbar.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2>User Feedback Messages</h2>
            <a href="<?php echo htmlspecialchars($dashboard_link); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if ($status_message): ?>
            <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($status_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <?php if ($result_messages && mysqli_num_rows($result_messages) > 0): ?>
            <?php while ($msg = mysqli_fetch_assoc($result_messages)): ?>
                <div class="message-card <?php echo ($msg['is_read'] == 0) ? 'unread' : ''; ?>">
                    <div class="message-header">
                        <!-- ... (header content as before) ... -->
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="name"><?php echo htmlspecialchars($msg['name']); ?></span>
                                <?php if ($msg['message_user_id'] && $msg['user_firstName']): ?>
                                    (Registered User: <?php echo htmlspecialchars($msg['user_firstName'] . ' ' . ($msg['user_lastName'] ?? '')); ?>)
                                <?php elseif ($msg['message_user_id']): ?>
                                     (Registered User ID: <?php echo $msg['message_user_id']; ?>)
                                <?php endif; ?>
                                <br>
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="email"><?php echo htmlspecialchars($msg['email']); ?></a>
                            </div>
                            <div class="text-end">
                                <span class="date"><?php echo date("M d, Y H:i", strtotime($msg['submitted_at'])); ?></span>
                                <br>
                                <span class="badge <?php echo ($msg['is_read'] == 0) ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                    <?php echo ($msg['is_read'] == 0) ? 'Unread' : 'Read'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <div class="action-button-group text-end mt-2">
                        <?php if ($msg['is_read'] == 0): ?>
                            <!-- Link to toggle read status (action handled at top of this file) -->
                            <a href="admin_user_messages.php?action=read&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check-circle"></i> Mark as Read
                            </a>
                        <?php else: ?>
                            <a href="admin_user_messages.php?action=unread&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-envelope-open"></i> Mark as Unread
                            </a>
                        <?php endif; ?>
                        <!-- Link to delete message (action handled at top of this file) -->
                        <a href="admin_user_messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this message permanently?');">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="message-card no-messages">
                <p>No user feedback messages found.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php if (isset($conn)) mysqli_close($conn); ?>