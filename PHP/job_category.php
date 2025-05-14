<?php include("conn.php"); ?>


<?php $active2 = "active"; ?>

<?php
$note = "";
$ctg=$_GET["ctg"];
if (isset($_GET["search"]) || isset($_GET["filter"])) {
    $search1 = $_GET["search"];
    $filter = $_GET["filter"];
    $search = mysqli_real_escape_string($conn, $search1);

    if (!empty($search)) {
        if ($filter == "All Type") {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {
                // echo '<script> alert("Data not found.");</script>';

            }
        } else if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%') AND jobType = '$filter'";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {


            }

        } else {
            // echo '<script> alert("Data not found.");</script>';

        }
    } else {
        if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND jobType = '$filter'";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {
                // echo '<script> alert("Data not found.");</script>';
                $note = "Data not found.";
            }

        } else {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' ";

        }

    }
} else {
    $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome + Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../CSS/job_list.css">
    <style>
        #seardiv {
            background-image: url(../Image/Home/ctg4.png);
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            width: 90%;
            height: 200px;
            margin: 20px auto;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            padding: 20px;
            color: white;
        }
        #ctg_name {
            font-size: 50px;
            font-weight: 700;
        }
        #ctg_ds {
            font-size: 24px;
            font-style: italic;
            font-weight: 500;
        }
        #fsearch {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
            border: none;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">

<?php include_once("navbar.php"); ?>
<?php include_once("ctg_bar.php"); ?>

<form action="job_category.php" method="get">
    <div class="container" id="seardiv">
        <?php 
        $ctg = $_GET['ctg'] ?? '';
        switch($ctg) {
            case 'Graphics': $c1='Graphics & Design'; $c2='Designs to make you stand out'; break;
            case 'Programming': $c1='Programming & Tech'; $c2='You think it. A programmer develops it'; break;
            case 'Digital': $c1='Digital Marketing'; $c2='Build your brand. Grow your business.'; break;
            case 'Video': $c1='Video & Animation'; $c2='Bring your story to life with creative videos.'; break;
            case 'Writing': $c1='Writing & Translation'; $c2='Get your words across—in any language.'; break;
            case 'Music': $c1='Music & Audio'; $c2='Do not miss a beat. Bring your sound to life.'; break;
            case 'Business': $c1='Business'; $c2='Business to make you stand out'; break;
            case 'AI': $c1='AI Services'; $c2='AI to make you stand out'; break;
            default: $c1='New'; $c2='New to make you stand out'; break;
        }
        ?>
        <h1 id="ctg_name"><?php echo $c1; ?></h1>
        <h3 id="ctg_ds"><?php echo $c2; ?></h3>

        <div class="row align-items-center gx-2 gy-2">
    <div class="col-md-6">
        <input id="fsearch" type="search" name="search" class="form-control"
               placeholder="<?php echo isset($_GET['search']) ? $_GET['search'] : 'Search jobs...'; ?>">
    </div>
    <div class="col-md-3">
        <select name="filter" class="form-select">
            <option value="All Type">Job Type</option>
            <option value="Full Time" <?php if(isset($_GET['filter']) && $_GET['filter']=='Full Time') echo 'selected'; ?>>Full Time</option>
            <option value="Part Time" <?php if(isset($_GET['filter']) && $_GET['filter']=='Part Time') echo 'selected'; ?>>Part Time</option>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" name="submit" class="btn text-white w-100" style="background-color: rgb(85, 182, 243);">
            <i class="fa fa-fw fa-search"></i> Search
        </button>
    </div>
</div>

        <input type="hidden" name="ctg" value="<?php echo htmlspecialchars($ctg); ?>">
    </div>
</form>

<div class="container my-4">
    <?php if (isset($_GET["filter"])): ?>
        <h5 class="text-muted mb-4">Job Type: <strong><?php echo $_GET['filter']; ?></strong></h5>
    <?php endif; ?>

    <div class="row">
        <?php
        if ($result && mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary"><?php echo $row["title"]; ?></h5>
                    <p class="mb-2"><strong>Category:</strong> <?php echo $row['category']; ?></p>
                    <p class="mb-2"><strong>Type:</strong> <?php echo $row['jobType']; ?></p>
                    <p class="mb-2"><strong>Company:</strong> <?php echo $row['company']; ?></p>
                    <p class="mb-2"><strong>Location:</strong> <?php echo $row['location']; ?></p>
                    <p class="mb-0"><strong>Price:</strong> $<?php echo $row['price']; ?> per month</p>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="col-12 text-center my-5">
            <h3 class="text-danger"><?php echo $note ?? "Data not found."; ?></h3>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (($note ?? '') != "Data not found.") include_once("footer.php"); ?>
</body>
</html>

<?php $conn->close(); ?>
