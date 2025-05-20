<?php include("../conn.php"); ?>
<?php session_start(); ?>

<?php $active2 = "active"; ?>

<?php
$note = "";
$ctg = $_GET["ctg"];

// Adopt safer GET parameter fetching as in the first code for robustness in HTML rendering
// This does not change the core logic of how $search1 and $filter affect the SQL query
$search1 = $_GET["search"] ?? '';
$filter = $_GET["filter"] ?? ''; // If form sends "All Type", $filter will be "All Type". If nothing selected or empty, it's ''.

$search = mysqli_real_escape_string($conn, $search1);

// Original SQL query logic from the second code - UNCHANGED
if (!empty($search1) || (!empty($filter) && $filter != "All Type" && $filter != "")) { // Condition slightly adjusted to handle empty filter as well
    if (!empty($search1)) {
        if ($filter == "All Type" || empty($filter)) {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND (jobId LIKE '%$search%' OR title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        } else if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND ( jobId LIKE '%$search%' OR title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%') AND jobType = '$filter'";
        } else {
            // Fallback for search term with unhandled filter type, as per original structure implied behavior
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND (jobId LIKE '%$search%' OR title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        }
    } else { // search1 is empty
        if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE category='$ctg' AND jobType = '$filter'";
        } else { // search1 is empty and filter is "All Type" or empty or other
            $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
        }
    }
} else { // No search term and filter is "All Type" or empty
    $sql = "SELECT * FROM jobtable WHERE category='$ctg'";
}

$result = mysqli_query($conn, $sql);
// Check for query errors and update $note if $result is false - crucial for "No results" message
if ($result === false) {
    // echo '<script> alert("Error in SQL query.");</script>'; // Optional: for debugging
    $note = "Error retrieving data."; // Or a more generic "No results found."
} elseif (mysqli_num_rows($result) == 0) {
    // This part of $note assignment based on original logic where alerts were commented out.
    // If specific branches set $note = "Data not found.", that will be used.
    // Otherwise, the generic "No results found" will be used later.
    if (empty($note)) { // Only set if not already set by a more specific error/condition
      // $note = "Data not found."; // This could override specific $note values. Let's rely on the ternary operator later for default.
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Removed old CSS link and inline styles -->
</head>

<body class="bg-light">

    <?php include_once("admin_navbar.php"); ?>
    <?php include_once("admin_ctg_bar.php"); ?>

    <div class="container mt-4">
        <div class="p-5 rounded bg-dark text-white text-center mb-4" style="background-image: url('../../Image/Home/ctg2.jpg'); background-size: cover;">
            <?php
            // Original $c1, $c2 logic from second code - UNCHANGED
            // $ctg variable is already defined from $_GET["ctg"]
            if ($ctg == 'Graphics') {
                $c1 = 'Graphics & Design';
                $c2 = 'Designs to make you stand out';
            } elseif ($ctg == 'Programming') {
                $c1 = 'Programming & Tech';
                $c2 = 'You think it. A programmer develops it';
            } else if ($ctg == 'Digital') {
                $c1 = 'Digital Marketing';
                $c2 = 'Build your brand. Grow your business.';
            } elseif ($ctg == 'Video') {
                $c1 = 'Video & Animation';
                $c2 = 'Bring your story to life with creative videos.';
            } elseif ($ctg == 'Writing') {
                $c1 = 'Writing & Translation';
                $c2 = 'Get your words across—in any language.';
            } elseif ($ctg == 'Music') {
                $c1 = 'Music & Audio';
                $c2 = 'Do not miss a beat. Bring your sound to life.';
            } elseif ($ctg == 'Business') {
                $c1 = 'Business';
                $c2 = 'Business to make you stand out';
            } elseif ($ctg == 'AI') {
                $c1 = 'AI Services';
                $c2 = 'AI to make you stand out';
            } else {
                $c1 = 'New';
                $c2 = 'New to make you stand out';
            }
            ?>
            <h1 class="display-4"><?php echo htmlspecialchars($c1); ?></h1>
            <p class="lead fw-bold"><?php echo htmlspecialchars($c2); ?></p>
        </div>

        <form class="row g-2 mb-4" method="get" action="admin_job_category.php">
            <div class="col-md-5">
                <input type="search" name="search" value="<?= htmlspecialchars($search1) ?>" class="form-control" placeholder="Search">
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="All Type" <?= ($filter == "All Type" || $filter == "") ? 'selected' : '' ?>>All Type</option>
                    <option value="Full Time" <?= $filter == "Full Time" ? 'selected' : '' ?>>Full Time</option>
                    <option value="Part Time" <?= $filter == "Part Time" ? 'selected' : '' ?>>Part Time</option>
                </select>
            </div>
            <input type="hidden" name="ctg" value="<?= htmlspecialchars($ctg) ?>">
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i> Search</button>
            </div>
        </form>

        <?php if ($result && mysqli_num_rows($result) > 0) : ?>
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["title"]); ?></h5>
                                <!-- Job ID is not displayed in the card body to match the first code's visual style -->
                                <p class="mb-1"><strong>Job Type:</strong> <?= htmlspecialchars($row['jobType']); ?></p>
                                <p class="mb-1"><strong>Company:</strong> <?= htmlspecialchars($row['company']); ?></p>
                                <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($row['location']); ?></p>
                                <p class="mb-1"><strong>Price:</strong> $<?= htmlspecialchars($row['price']); ?> per monthly</p>
                                <p class="mb-1 text-danger"><strong>Exit Day:</strong> <?= htmlspecialchars($row['exitDay']); ?></p>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <!-- Form action and method from original second code -->
                                <form action="more_details.php" method="get">
                                    <input type="hidden" name="jobId" value="<?= htmlspecialchars($row['jobId']); ?>">
                                    <!-- Button class and text from first code -->
                                    <button class="btn btn-outline-primary btn-sm" type="submit" name="apply">
                                        More Details <i class="fas fa-info-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php
                // Display $note if it's set (e.g., by SQL error or specific logic), otherwise default message.
                echo htmlspecialchars(!empty($note) ? $note : "No results found.");
                ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- Removed original .note div -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS kit from first code is not strictly necessary if CSS version covers icons -->
    <!-- <script src="https://kit.fontawesome.com/a076d05399.js"></script> -->
</body>
</html>