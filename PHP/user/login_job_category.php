<?php session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}
include("../conn.php");
$active2 = "active";

$ctg = $_GET["ctg"];
$note = "";
$search1 = $_GET["search"] ?? '';
$filter = $_GET["filter"] ?? '';

$search = mysqli_real_escape_string($conn, $search1);

if (!empty($search1) || !empty($filter)) {
    $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
    if (!empty($search1)) {
        $sql .= " AND (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
    }
    if ($filter == "Full Time" || $filter == "Part Time") {
        $sql .= " AND jobType = '$filter'";
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
    <title>Job Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include_once("login_navbar.php"); ?>
<?php include_once("login_ctg_bar.php"); ?>

<div class="container mt-4">
    <div class="p-5 rounded bg-dark text-white text-center mb-4" style="background-image: url('../../Image/Home/ctg2.jpg'); background-size: cover;">
        <?php
        $categoryMap = [
            'Graphics' => ['Graphics & Design', 'Designs to make you stand out'],
            'Programming' => ['Programming & Tech', 'You think it. A programmer develops it'],
            'Digital' => ['Digital Marketing', 'Build your brand. Grow your business.'],
            'Video' => ['Video & Animation', 'Bring your story to life with creative videos.'],
            'Writing' => ['Writing & Translation', 'Get your words across—in any language.'],
            'Music' => ['Music & Audio', 'Do not miss a beat. Bring your sound to life.'],
            'Business' => ['Business', 'Business to make you stand out'],
            'AI' => ['AI Services', 'AI to make you stand out'],
        ];
        $c1 = $categoryMap[$ctg][0] ?? 'New';
        $c2 = $categoryMap[$ctg][1] ?? 'New to make you stand out';
        ?>
        <h1 class="display-4"><?php echo $c1; ?></h1>
        <p class="lead fw-bold"><?php echo $c2; ?></p>
    </div>

    <form class="row g-2 mb-4" method="get" action="login_job_category.php">
        <div class="col-md-5">
            <input type="search" name="search" value="<?= htmlspecialchars($search1) ?>" class="form-control" placeholder="Search">
        </div>
        <div class="col-md-3">
            <select name="filter" class="form-select">
                <option value="All Type" <?= $filter == "All Type" ? 'selected' : '' ?>>All Type</option>
                <option value="Full Time" <?= $filter == "Full Time" ? 'selected' : '' ?>>Full Time</option>
                <option value="Part Time" <?= $filter == "Part Time" ? 'selected' : '' ?>>Part Time</option>
            </select>
        </div>
        <input type="hidden" name="ctg" value="<?= htmlspecialchars($ctg) ?>">
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i> Search</button>
        </div>
    </form>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= $row["title"]; ?></h5>
                            <p class="mb-1"><strong>Job Type:</strong> <?= $row['jobType']; ?></p>
                            <p class="mb-1"><strong>Company:</strong> <?= $row['company']; ?></p>
                            <p class="mb-1"><strong>Location:</strong> <?= $row['location']; ?></p>
                            <p class="mb-1"><strong>Price:</strong> $<?= $row['price']; ?> per monthly</p>
                            <p class="mb-1 text-danger"><strong>Exit Day:</strong> <?= $row['exitDay']; ?></p>
                        </div>
                        <div class="card-footer text-center bg-white">
                            <form action="more_details.php" method="get">
                                <input type="hidden" name="jobId" value="<?= $row['jobId']; ?>">
                                <button class="btn btn-outline-primary btn-sm" type="submit" name="apply">
                                    More Details <i class="fas fa-info-circle"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= $note ?: "No results found."; ?>
        </div>
    <?php endif; ?>
</div>
<?php if (($note ?? '') != "Data not found.") include_once("login_footer.php"); ?>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
