<?php session_start(); ?>
<?php 
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
} ?>
<?php include("../conn.php"); ?>

<?php
  $userid = $_SESSION['id'];
if(isset($_GET['apply'])){
    $job = $_GET['jobId'];
    $sql="SELECT * FROM apply_job WHERE userid = '$userid' AND jobid = '$job'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result)>0 ) {
        echo '<script> alert("You already applied .. ");window.location.href="user.php"; </script>';
    } 
}
if (isset($_POST['submit'])) {
    $jobid = $_POST['jobid'];
 
        $sql="INSERT INTO apply_job (userid, jobid) VALUES('$userid','$jobid');";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo '<script> alert("Job applied successfully");window.location.href="user.php"; </script>';
        } else {
            echo '<script> alert("Application Failed");window.location.href="find_freelancers.php";</script>';
        }
    }
?>


<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <title>Job Application Form</title>
    <style>
        /* General Body Style */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Container for Form */
        .container {
            max-width: 700px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Heading Style */
        h2 {
            text-align: center;
            color: #007bff;
            font-family: 'Helvetica Neue', sans-serif;
            margin-bottom: 30px;
            font-size: 30px;
        }

        /* Back Button Style */
        #backlink {
            font-size: 18px;
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 30px;
        }

        #backlink:hover {
            text-decoration: underline;
        }

        /* Label Style */
        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }

        /* Input Fields Style */
        input, textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: 2px solid #ccc;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border 0.3s ease;
        }

        /* Input focus effect */
        input:focus, textarea:focus {
            border: 2px solid #007bff;
            outline: none;
        }

        /* File Input */
        input[type="file"] {
            padding: 14px;
        }

        /* Submit Button Style */
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Submit Button Text Alignment */
        .submit {
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 25px;
                max-width: 90%;
            }

            h2 {
                font-size: 24px;
            }

            button {
                font-size: 16px;
            }
        }

    </style>
</head>

<body>

    <div class="container">
        <a id="backlink" href="user.php"><i class="glyphicon glyphicon-menu-left"></i> Back to Freelancers</a>

        <h2>Job Application Form</h2>
        <form action="Job Application Form.php" method="POST" enctype="multipart/form-data">

            <div class="input-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="input-group">
                <label for="resume">Upload CV (PDF or Word):</label>
                <input type="file" id="resume" name="resume" accept=".pdf, .doc, .docx">
            </div>

            <div class="input-group">
                <label for="cover_letter">Cover Letter:</label>     
                <textarea id="cover_letter" name="cover_letter" rows="5" required></textarea>
            </div>

            <input type="hidden" name="jobid" value="<?php echo $_GET['jobId']; ?>">

            <div class="submit">
                <button type="submit" name="submit">Submit Application</button>
            </div>
        </form>
    </div>

</body>

</html>
