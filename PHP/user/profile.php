<?php session_start(); ?>
<?php 
if (!isset($_SESSION['id'])) { // This 'id' is likely your session key for the user's ID
    header('Location: ../login.php');
    exit();
} ?>
<?php include("../conn.php"); ?>
<?php $active6 = "active"; ?>
 <?php 
  $fName = $_SESSION['fName'] ?? 'N/A'; 
  $lName = $_SESSION['lName'] ?? 'N/A';
  $email = $_SESSION['email'] ?? 'N/A';
  
  // This 'id' from the session is the one we'll use, 
  // and it corresponds to the 'userid' column in your database table.
  $session_user_id_value = $_SESSION['id'] ?? 'N/A'; 

  $image_name = $_SESSION['image'] ?? 'default-profile.png'; 
  $image_path = "UploadImage/" . $image_name; 

  $apply_job_count = 8; // Placeholder
  $post_job_count = 3;  // Placeholder
  
  // Fetch actual joined_date from your 'users' table
  $joined_date_display = "Unknown";
  if ($session_user_id_value !== 'N/A') {
    // MODIFIED: Changed 'id = ?' to 'userid = ?' in the SQL query
    $stmt_user = mysqli_prepare($conn, "SELECT joined_date FROM users WHERE userid = ?"); 
    if ($stmt_user) {
        mysqli_stmt_bind_param($stmt_user, "i", $session_user_id_value); // Use the value from session
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
        if ($row_user = mysqli_fetch_assoc($result_user)) {
            $joined_date_timestamp = strtotime($row_user['joined_date']);
            if ($joined_date_timestamp) {
                 $joined_date_display = date('M, d, Y', $joined_date_timestamp);
            } else {
                $joined_date_display = "Invalid Date"; 
            }
        }
        mysqli_stmt_close($stmt_user);
    }
  }

  // This uses the session ID for display formatting, which is fine.
  $user_id_display_formatted = "www" . str_pad($session_user_id_value, 2, '0', STR_PAD_LEFT);

 ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - WorkWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card-container {
            max-width: 500px;
            margin-top: 3rem;
            margin-bottom: 3rem;
        }
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .profile-avatar-wrapper {
            position: relative;
            cursor: pointer; 
            margin-right: 1rem;
        }
        .profile-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: block; 
        }
        .profile-avatar-wrapper .upload-icon-overlay { 
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 50%;
            padding: 0.25rem;
            font-size: 0.7rem;
            line-height: 1;
            opacity: 0; 
            transition: opacity 0.3s ease;
        }
        .profile-avatar-wrapper:hover .upload-icon-overlay {
            opacity: 1; 
        }

        .profile-info .username {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.1rem;
        }
        .profile-info .account-label {
            font-size: 0.8rem;
            color: #6c757d;
            display: block;
            margin-bottom: 0.25rem;
        }
        .profile-info .joined-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .edit-profile-btn {
            color: #0d6efd;
            font-size: 1.2rem;
            text-decoration: none;
        }
        .edit-profile-btn:hover {
            color: #0a58ca;
        }
        .stats-section {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .stat-item {
            display: flex;
            align-items: center;
        }
        .stat-item .stat-label {
            font-size: 0.9rem;
            color: #495057;
             margin-right: 0.5rem;
        }
        .stat-item .stat-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        .stat-divider {
            border-left: 1px solid #dee2e6;
            height: 30px;
            align-self: center;
        }
        .form-group-custom {
            margin-bottom: 1rem;
        }
        .form-control-plaintext.profile-field {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            color: #212529;
        }
        .form-control-plaintext.profile-field strong {
            color: #495057;
            margin-right: 0.5rem;
        }
        .headerbar { /* Assuming this style is still relevant or defined elsewhere */
            background-color: #f1f1f1;
            padding: 10px 0;
            text-align: center;
            font-size: 0.9rem;
            color: #555;
            border-bottom: 1px solid #ddd;
        }
        .toast-container {
            z-index: 1090;
        }
    </style>
</head>

<body>
    
    <?php include_once("login_navbar.php") ?>

    <div class="container profile-card-container">
        <div class="card profile-card">
            <div id="profile-update-alert" class="alert" role="alert" style="display: none;"></div>

            <div class="profile-header">
                <form id="profileImageForm" enctype="multipart/form-data" style="margin:0; padding:0;">
                    <label for="profile_image_file_input" class="profile-avatar-wrapper" title="Change profile picture">
                        <img src="<?php echo file_exists($image_path) ? htmlspecialchars($image_path) : 'https://via.placeholder.com/70'; ?>" 
                             alt="Profile Avatar" class="profile-avatar" id="profileAvatarDisplay">
                        <span class="upload-icon-overlay"><i class="fas fa-camera"></i></span>
                    </label>
                    <input type="file" name="profile_image_file" id="profile_image_file_input" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                </form>

                <div class="profile-info flex-grow-1">
                    <span class="account-label">Account username:</span>
                    <h2 class="username"><?php echo htmlspecialchars($fName . ' ' . $lName); ?></h2>
                    <span class="joined-date">Joined on: <?php echo htmlspecialchars($joined_date_display); ?></span>
                </div>
                <form action="user_renter_password.php" method="post" class="ms-auto">
                    <button type="submit" name="edit" class="btn btn-link edit-profile-btn p-0" title="Edit Profile">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                </form>
            </div>

            <div class="stats-section">
                <div class="stat-item">
                    <span class="stat-label">Apply Job</span>
                    <span class="stat-number"><?php echo $apply_job_count; ?></span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $post_job_count; ?></span>
                    <span class="stat-label ms-2">Post Job</span> 
                </div>
            </div>

            <div class="user-details-section">
                <div class="form-group-custom">
                    <div class="form-control-plaintext profile-field">
                        <strong>User ID:</strong> <?php echo htmlspecialchars($user_id_display_formatted); // MODIFIED: Use the formatted variable ?>
                    </div>
                </div>
                <div class="form-group-custom">
                    <div class="form-control-plaintext profile-field">
                        <strong>First Name:</strong> <?php echo htmlspecialchars($fName); ?>
                    </div>
                </div>
                <div class="form-group-custom">
                     <div class="form-control-plaintext profile-field">
                        <strong>Last Name:</strong> <?php echo htmlspecialchars($lName); ?>
                    </div>
                </div>
                <div class="form-group-custom">
                    <div class="form-control-plaintext profile-field">
                        <strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
                    </div>
                </div>
            </div>
            
            <hr class="my-4"> 

            <form action="user_renter_password.php" method="post" class="text-end">
                <button type="submit" name="userdelete" class="btn btn-danger">Delete account</button>
            </form>

            <div class="asd" style="display: none;">
                <div class="www item1"><h2 id="name"><?php echo $fName.' '.$lName ;?></h2></div>
                <div class="www item2">Apply Job:</div>
                <div class="www item3">Post Job:</div>
                <div class="www item7" style="background-image: url(<?php echo htmlspecialchars($image_path);?>);" id="hiddenDivImageBackground"></div>
                <div class="www item5">
                    <div class="lable"><label>User ID : <span>ww0<?php echo $session_user_id_value;?></span></label></div><br>
                    <div class="lable"><label>First Name : <span><?php echo $fName;?></span></label></div><br>
                    <div class="lable"><label>Last Name : <span><?php echo $lName;?></span></label></div><br>
                    <div class="lable"><label>Email : <span><?php echo $email;?></span></label></div>
                </div>
                <div class="www item4"><button type="submit" name="edit"><i class="fa fa-edit" style="font-size:36px"></i></button></div>
                <div class="www item6"><button type="submit" name="userdelete">Delete Account</button></div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="imageUploadToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Profile Update</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileImageInput = document.getElementById('profile_image_file_input');
            const profileAvatarDisplay = document.getElementById('profileAvatarDisplay');
            const hiddenDivImageBackground = document.getElementById('hiddenDivImageBackground');
            const imageUploadToastEl = document.getElementById('imageUploadToast');
            const imageUploadToast = bootstrap.Toast.getOrCreateInstance(imageUploadToastEl);

            if (profileImageInput) {
                profileImageInput.addEventListener('change', function (event) {
                    if (event.target.files && event.target.files[0]) {
                        const file = event.target.files[0];
                        const formData = new FormData();
                        formData.append('profile_image_file', file);
                        profileAvatarDisplay.style.opacity = '0.5';

                        fetch('upload_profile_image.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            profileAvatarDisplay.style.opacity = '1';
                            const toastBody = imageUploadToastEl.querySelector('.toast-body');
                            const toastHeader = imageUploadToastEl.querySelector('.toast-header strong');

                            if (data.success) {
                                profileAvatarDisplay.src = data.newImagePath + '?t=' + new Date().getTime();
                                if(hiddenDivImageBackground) {
                                    hiddenDivImageBackground.style.backgroundImage = 'url(' + data.newImagePath + '?t=' + new Date().getTime() + ')';
                                }
                                toastHeader.textContent = 'Success!';
                                imageUploadToastEl.classList.remove('text-bg-danger');
                                imageUploadToastEl.classList.add('text-bg-success');
                                toastBody.textContent = data.message;
                            } else {
                                toastHeader.textContent = 'Error!';
                                imageUploadToastEl.classList.remove('text-bg-success');
                                imageUploadToastEl.classList.add('text-bg-danger');
                                toastBody.textContent = data.message || 'An error occurred.';
                            }
                            imageUploadToast.show();
                        })
                        .catch(error => {
                            profileAvatarDisplay.style.opacity = '1';
                            const toastBody = imageUploadToastEl.querySelector('.toast-body');
                            const toastHeader = imageUploadToastEl.querySelector('.toast-header strong');
                            toastHeader.textContent = 'Error!';
                            imageUploadToastEl.classList.remove('text-bg-success');
                            imageUploadToastEl.classList.add('text-bg-danger');
                            toastBody.textContent = 'Network error or server issue: ' + error;
                            imageUploadToast.show();
                            console.error('Error:', error);
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>