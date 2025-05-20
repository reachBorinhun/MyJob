<?php 
include("../conn.php"); 
session_start();

if (isset($_POST['submit'])) {
    if ($_POST['submit'] == 'Add Job') {
        $userid = $_SESSION['id'];

        $title = $_POST['job_title'];
        $jobType = $_POST['job_type'];
        $company = $_POST['company'];
        $location = $_POST['location'];
        $price = $_POST['price'];
        $exitDay = $_POST['exit_day'];
        $responsibilities = $_POST['responsibilities'];
        $requirement = $_POST['requirements'];
        $ctg = $_POST['ctg'];

        // PAYMENT - I didn't see payment input in your form; set empty or add if needed
        $payment = ''; // or set to some value if needed

        // IMAGE UPLOAD
        if (isset($_FILES['job_img']) && $_FILES['job_img']['error'] == 0) {
            $target_dir = "../../image/uploads/"; // relative to this file's location
            $filename = basename($_FILES['job_img']['name']);
            $unique_filename = time() . "_" . $filename;
            $target_file = $target_dir . $unique_filename;

            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['job_img']['tmp_name'], $target_file)) {
                    // Save relative path to DB (relative to root folder for use in HTML)
                    $job_image_path = "image/uploads/" . $unique_filename;
                } else {
                    echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
                    $job_image_path = null;
                }
            } else {
                echo '<script>alert("Only JPG, JPEG, PNG & GIF files are allowed.");</script>';
                $job_image_path = null;
            }
        } else {
            $job_image_path = null;
        }

        // SQL Insert
        $sql = "INSERT INTO unapproved_job 
            (userid, category, title, job_type, company, location, price, exit_day, responsibilities, requirement, payment, job_image) 
            VALUES 
            ('$userid', '$ctg', '$title', '$jobType', '$company', '$location', '$price', '$exitDay', '$responsibilities', '$requirement', '$payment', '$job_image_path')";

        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo '<script> alert("Job added successfully"); window.location.href="admin_dashbord.php"; </script>';
        } else {
            echo '<script> alert("Job not added."); window.location.href="admin_dashbord.php"; </script>';
        }
    } else {
        header('location:admin_dashboard.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link rel="stylesheet" href="../../CSS/add_job.css" />

    <title>Admin Add Job</title>
    <style>
        form {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-size: 18px;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <form action="add_job.php" method="post" enctype="multipart/form-data">
        <a href="admin_dashbord.php" id="remove"><i class="fa fa-remove"></i></a>
        <h1>Add Your Job</h1>

        <label for="job_Category">Job Category:</label>
        <select name="ctg" id="job_Category">
            <option value="Graphics">Graphics & Design</option>
            <option value="Programming">Programming & Tech</option>
            <option value="Digital">Digital Marketing</option>
            <option value="Video">Video & Animation</option>
            <option value="Writing">Writing & Translation</option>
            <option value="Music">Music & Audio</option>
            <option value="Business">Business</option>
            <option value="AI">AI Services</option>
            <option value="New">New*</option>
        </select>

        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" required /><br />

        <label for="company">Company:</label>
        <input type="text" name="company" required /><br />

        <label for="location">Location:</label>
        <input type="text" name="location" required /><br />

        <label for="price">Price:</label>
        <input type="number" name="price" required /><br />

        <label for="job_type">Job Type:</label>
        <select name="job_type" id="job_type" required>
            <option value="Full Time">Full Time</option>
            <option value="Part Time">Part Time</option>
        </select>
        <br /><br />

        <label for="responsibilities">Responsibilities:</label>
        <textarea name="responsibilities" rows="4" required></textarea
        ><br />

        <label for="requirements">Requirements:</label>
        <textarea name="requirements" rows="4" required></textarea
        ><br />

        <label for="exit_day">Exit Day:</label>
        <input type="date" name="exit_day" required /><br />

        <label for="job_img">Upload Job Image:</label>
        <input type="file" name="job_img" accept="image/*" required /><br /><br />

        <input id="cancel" type="submit" value="Cancel" />
        <input id="add" type="submit" name="submit" value="Add Job" />
    </form>
</body>

</html>
