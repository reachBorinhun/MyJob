<?php
session_start();

// Make sure error reporting is on for debugging (remove or comment out for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file.
// The path "../conn.php" assumes conn.php is one directory above where this script is.
// If your profile page and this script are in a 'user' folder, and conn.php is in the root, this is correct.
include("../conn.php"); 

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// 1. Check if the user is logged in
if (!isset($_SESSION['id'])) {
    $response['message'] = 'Authentication required. Please log in again.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$user_id_to_update = $_SESSION['id']; // The 'userid' from your session

// 2. Check if a file was uploaded via the 'profile_image_file' input name
if (isset($_FILES['profile_image_file']) && $_FILES['profile_image_file']['error'] === UPLOAD_ERR_OK) {
    
    // Define the target directory for uploads.
    // This should be relative to THIS upload_profile_image.php script.
    // If this script is in the same directory as your profile page,
    // and 'UploadImage' is also in that directory, this is correct.
    $target_dir = "UploadImage/"; 

    // --- Ensure the UploadImage directory exists and is writable ---
    if (!file_exists($target_dir)) {
        // Attempt to create it recursively with 0775 permissions
        if (!mkdir($target_dir, 0775, true)) { 
            $response['message'] = 'Failed to create upload directory. Please create "'.$target_dir.'" manually and set permissions.';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }
    if (!is_writable($target_dir)) {
        $response['message'] = 'The upload directory "'.$target_dir.'" is not writable by the server. Please check permissions.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    // --- End Directory Check ---

    $original_file_name = basename($_FILES["profile_image_file"]["name"]);
    $imageFileType = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    
    // Create a more unique and safer file name to prevent conflicts and overwriting.
    // Example: user_123_timestamp.jpg
    $new_file_name = "user_" . $user_id_to_update . "_" . time() . "." . $imageFileType;
    $target_file_path_on_server = $target_dir . $new_file_name; // Full path on server for move_uploaded_file

    // --- File Validation ---
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    // Check if image file is an actual image
    $check = getimagesize($_FILES["profile_image_file"]["tmp_name"]);
    if ($check === false) {
        $response['message'] = "File is not a valid image.";
    } 
    // Check file size
    else if ($_FILES["profile_image_file"]["size"] > $max_file_size) {
        $response['message'] = "Sorry, your file is too large (Max 5MB).";
    }
    // Allow certain file formats
    else if (!in_array($imageFileType, $allowed_types)) {
        $response['message'] = "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
    }
    // If all checks pass, try to move the uploaded file
    else {
        // Optional: Delete the old image file if it exists and is not the default
        $stmt_old_image = mysqli_prepare($conn, "SELECT image FROM users WHERE userid = ?");
        if ($stmt_old_image) {
            mysqli_stmt_bind_param($stmt_old_image, "i", $user_id_to_update);
            mysqli_stmt_execute($stmt_old_image);
            $result_old_image = mysqli_stmt_get_result($stmt_old_image);
            if ($row_old_image = mysqli_fetch_assoc($result_old_image)) {
                $old_image_name_db = $row_old_image['image'];
                if (!empty($old_image_name_db) && $old_image_name_db !== 'default-profile.png' && file_exists($target_dir . $old_image_name_db)) {
                    unlink($target_dir . $old_image_name_db);
                }
            }
            mysqli_stmt_close($stmt_old_image);
        }

        // Attempt to move the uploaded file to its new home
        if (move_uploaded_file($_FILES["profile_image_file"]["tmp_name"], $target_file_path_on_server)) {
            // File uploaded successfully. Now update the database.
            // Assuming your users table has an 'image' column to store the filename,
            // and 'userid' column for the user's ID.
            $stmt_update = mysqli_prepare($conn, "UPDATE users SET image = ? WHERE userid = ?");
            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "si", $new_file_name, $user_id_to_update);
                if (mysqli_stmt_execute($stmt_update)) {
                    $_SESSION['image'] = $new_file_name; // Update the image name in the session
                    
                    $response['success'] = true;
                    $response['message'] = 'Profile image updated successfully!';
                    // This is the path your JavaScript will use for the <img> src.
                    // It should be relative to your HTML page.
                    $response['newImagePath'] = $target_dir . $new_file_name; 
                } else {
                    $response['message'] = "Database update failed: " . mysqli_stmt_error($stmt_update);
                    // If DB update fails, delete the just-uploaded file to avoid orphaned files
                    if (file_exists($target_file_path_on_server)) {
                        unlink($target_file_path_on_server);
                    }
                }
                mysqli_stmt_close($stmt_update);
            } else {
                $response['message'] = "Database prepare statement failed: " . mysqli_error($conn);
                // If DB prepare fails, delete the uploaded file
                if (file_exists($target_file_path_on_server)) {
                    unlink($target_file_path_on_server);
                }
            }
        } else {
            $php_upload_errors = array(
                UPLOAD_ERR_OK         => 'No errors.',
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            );
            $err_code = $_FILES['profile_image_file']['error'];
            $response['message'] = "Sorry, there was an error uploading your file. Server couldn't move the file. Error: " . ($php_upload_errors[$err_code] ?? "Unknown error code $err_code");
        }
    }
} else if (isset($_FILES['profile_image_file'])) {
    // Handle specific initial upload errors reported by PHP
    $error_code = $_FILES['profile_image_file']['error'];
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $response['message'] = "File is too large (exceeds server or form configuration limit).";
            break;
        case UPLOAD_ERR_PARTIAL:
            $response['message'] = "File was only partially uploaded.";
            break;
        case UPLOAD_ERR_NO_FILE: // This case should ideally be caught by JS, but good to have server-side.
            $response['message'] = "No file was selected for upload.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response['message'] = "Server configuration error: Missing a temporary folder for uploads.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response['message'] = "Server configuration error: Failed to write file to disk.";
            break;
        case UPLOAD_ERR_EXTENSION:
            $response['message'] = "A PHP extension stopped the file upload.";
            break;
        default:
            $response['message'] = "An unknown upload error occurred (code: " . $error_code . ").";
            break;
    }
} else {
    $response['message'] = 'No file data received by the server. Ensure the form input name is "profile_image_file".';
}

// Always send a JSON response back to the JavaScript
header('Content-Type: application/json');
echo json_encode($response);
exit(); // Crucial to prevent any other output
?>