<?php include("conn.php"); ?>
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
    <link rel="stylesheet" href="../CSS/job_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Document</title>
</head>

<body style="background-color: #f8f9fa;">
    <?php include_once("navbar.php"); ?>
    <?php include_once("ctg_bar.php"); ?>

    <form action="find_job.php" method="get" class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-4 mb-2">
                <input type="search" name="search" class="form-control" placeholder="<?php echo isset($_GET["search"]) ? $_GET["search"] : 'Search jobs...'; ?>">
            </div>
            <div class="col-md-3 mb-2">
                <select name="filter" class="form-select">
                    <option value="All Type">Job Type</option>
                    <option value="Full Time" <?php if(isset($_GET['filter']) && $_GET['filter']=='Full Time') echo 'selected'; ?>>Full Time</option>
                    <option value="Part Time" <?php if(isset($_GET['filter']) && $_GET['filter']=='Part Time') echo 'selected'; ?>>Part Time</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" name="submit" class="btn text-white w-100" style="background-color: rgb(85, 182, 243);">
                    <i class="fa fa-fw fa-search"></i> Search
                </button>
            </div>
        </div>
    </form>

    <div class="container">
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
            <?php
                endwhile;
            else:
            ?>
            <div class="col-12 text-center my-5">
                <h3 class="text-danger"><?php echo $note ?: "Data not found."; ?></h3>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($note != "Data not found.") include_once("footer.php"); ?>

