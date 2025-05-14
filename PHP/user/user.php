<?php session_start(); ?>
<?php
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<?php include("../conn.php"); ?>
<?php $active2 = "active"; ?>
<?php
$note = "";
if (isset($_GET["search"]) || isset($_GET["filter"])) {
    $search1 = $_GET["search"];
    $filter = $_GET["filter"];
    $search = mysqli_real_escape_string($conn, $search1);

    if (!empty($search)) {
        if ($filter == "All Type") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {
                // echo '<script> alert("Data not found.");</script>';
            }
        } else if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%') AND jobType = '$filter'";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {
            }
        } else {
            // echo '<script> alert("Data not found.");</script>';
        }
    } else {
        if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE jobType = '$filter'";
            $result = mysqli_query($conn, $sql);
            if ($result == false) {
                // echo '<script> alert("Data not found.");</script>';
                $note = "Data not found.";
            }
        } else {
            $sql = "SELECT * FROM jobtable ";
        }
    }
} else {
    $sql = "SELECT * FROM jobtable ";
}

$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Job Listings</title>
    <style>
        .note {
            width: 100%;
            text-align: center;
        }

        .note h1 {
            color: red;
        }

        .search-form input,
        .search-form select,
        .search-form button {
            margin-bottom: 10px;
        }

        .card-body {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: #007bff;
        }

        .card-text {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <?php include_once("login_navbar.php"); ?>
    <?php include_once("login_ctg_bar.php"); ?>

    <!-- Search Form -->
    <form action="user.php" method="get" class="container my-4 search-form">
        <div class="row">
            <div class="col-md-8">
                <input id="fsearch" type="search" name="search" class="form-control" placeholder="<?php if (isset($_GET["search"])) { echo $_GET["search"]; } else { echo "Search jobs..."; } ?>">
            </div>
            <div class="col-md-2">
                <select name="filter" id="idcheck" class="form-control">
                    <option value="All Type">Job Type</option>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" name="submit" class="btn btn-primary w-100">
                    <i class="fa fa-fw fa-search"></i> Search
                </button>
            </div>
        </div>
    </form>

    <!-- Display Filtered Job Type -->
    <h3 class="text-center">
        <?php
        if (isset($_GET["filter"])) {
            echo "Job Type: " . $_GET['filter'];
        }
        ?>
    </h3>

    <div class="container">
        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row["title"]; ?></h5>
                                <p class="card-text"><strong>Job Type:</strong> <?php echo $row['jobType']; ?></p>
                                <p class="card-text"><strong>Company:</strong> <?php echo $row['company']; ?></p>
                                <p class="card-text"><strong>Location:</strong> <?php echo $row['location']; ?></p>
                                <p class="card-text"><strong>Price:</strong> $<?php echo $row['price']; ?> per month</p>
                                <p class="card-text"><strong>Exit Day:</strong> <span class="text-danger"><?php echo $row['exitDay']; ?></span></p>
                                <a href="more_details.php?jobId=<?php echo $row['jobId']; ?>" class="btn btn-info w-100">
                                    More Details <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
        </div>
    </div>
<?php
            } else {
                $note = "Data not found.";
?>

    <div class="note">
        <h1><?php echo $note; ?></h1>
    </div>

<?php
            }
?>

<?php if ($note != 'Data not found.') {
    include_once('login_footer.php');
} ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
