<?php
session_start();
// Add your admin authentication check here if you decide to secure this action file
// For now, skipping as per your request on other files.
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//    header('Location: ../login.php');
//    exit();
// }

include("../conn.php");

$redirect_to = "admin_user_messages.php"; // Default redirect page

if (isset($_GET['id']) && isset($_GET['action'])) {
    $message_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $action = $_GET['action']; // Should be 'read' or 'unread'

    if ($message_id && ($action === 'read' || $action === 'unread')) {
        $new_status = ($action === 'read') ? 1 : 0; // 1 for TRUE (read), 0 for FALSE (unread)

        $sql = "UPDATE contact_messages SET is_read = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $new_status, $message_id);
            if (mysqli_stmt_execute($stmt)) {
                // Success
                $status_param = ($action === 'read') ? 'success_read' : 'success_unread';
                header("Location: " . $redirect_to . "?status=" . $status_param);
                exit();
            } else {
                // Database execution error
                error_log("DB Execute Error (toggle_read): " . mysqli_error($conn));
                header("Location: " . $redirect_to . "?status=error_update");
                exit();
            }
            mysqli_stmt_close($stmt);
        } else {
            // SQL prepare error
            error_log("DB Prepare Error (toggle_read): " . mysqli_error($conn));
            header("Location: " . $redirect_to . "?status=error_update");
            exit();
        }
    } else {
        // Invalid ID or action parameter
        header("Location: " . $redirect_to . "?status=error_params"); // You can define this status message
        exit();
    }
} else {
    // ID or action not provided in GET request
    header("Location: " . $redirect_to);
    exit();
}

if (isset($conn)) {
    mysqli_close($conn);
}
?>