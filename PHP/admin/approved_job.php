<?php include("../conn.php");
session_start();
?>
<?php $active4 = "active"; ?>
<?php
if (isset($_GET['remove'])) {
    $jobid = $_GET['id'];
    // If you want to delete the image file when removing an approved job:
    // $sql_get_image = "SELECT job_image FROM jobtable WHERE jobId = '$jobid'";
    // $res_get_image = mysqli_query($conn, $sql_get_image);
    // if ($row_image = mysqli_fetch_assoc($res_get_image)) {
    //     if (!empty($row_image['job_image'])) {
    //         // Assuming job_image stores 'image/uploads/filename.jpg'
    //         $file_to_delete_relative_to_project_root = $row_image['job_image'];
    //         $image_file_to_delete_absolute = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $file_to_delete_relative_to_project_root;
    //         if (file_exists($image_file_to_delete_absolute)) {
    //             unlink($image_file_to_delete_absolute);
    //         }
    //     }
    // }
    $sql = "DELETE FROM jobtable WHERE `jobtable`.`jobId` = $jobid";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        echo '<script> alert("Delete successful.");</script>';
        // It's generally better to redirect after the alert or ensure the alert doesn't prevent redirection.
        // Consider: echo '<script> alert("Delete successful."); window.location.href="approved_job.php";</script>'; exit();
        header('location:approved_job.php');
        exit(); // Add exit after header
    }
}
?>
<?php
$note = "";
// Original search/filter logic
if (isset($_GET["search"]) || isset($_GET["filter"])) {
    $search1 = $_GET["search"] ?? ''; // Use null coalescing for safety
    $filter = $_GET["filter"] ?? 'All Type'; // Use null coalescing
    $search = mysqli_real_escape_string($conn, $search1);

    if (!empty($search)) {
        if ($filter == "All Type") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        } else if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%') AND jobType = '$filter'";
        } else { // Fallback if filter is not recognized but search is present
             $sql = "SELECT * FROM jobtable WHERE (title LIKE '%$search%' OR company LIKE '%$search%' OR location LIKE '%$search%' OR price LIKE '%$search%' OR exitDay LIKE '%$search%')";
        }
    } else { // Search is empty
        if ($filter == "Full Time" || $filter == "Part Time") {
            $sql = "SELECT * FROM jobtable WHERE jobType = '$filter'";
        } else { // Search empty and filter is "All Type" or not recognized
            $sql = "SELECT * FROM jobtable ";
        }
    }
} else if (isset($_GET['idcheck']) && !empty($_GET['idcheck'])) { // Handle JOB ID search separately
    $jobIdSearch = mysqli_real_escape_string($conn, $_GET['idcheck']);
    $sql = "SELECT * FROM jobtable WHERE jobId = '$jobIdSearch'";
}
else {
    $sql = "SELECT * FROM jobtable ";
}

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) == 0 && (isset($_GET["search"]) || isset($_GET["filter"]) || isset($_GET['idcheck']))) {
    $note = "Data not found.";
} elseif (!$result) {
    $note = "Error fetching data: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS from first code -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome (using version from second code as it's newer, ensure icons are compatible or update) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Approved Job</title>
    <style>
        /* Styles adapted from the first code */
        .search-form .form-control,
        .search-form .btn {
            margin-bottom: 10px; /* For spacing when elements stack on small screens */
        }
        @media (min-width: 768px) { /* md breakpoint and up */
            .search-form .form-control,
            .search-form .btn {
                margin-bottom: 0; /* No margin when side-by-side */
            }
        }

        .card.styled-card { /* Apply to the card itself */
            border: none; /* Remove default Bootstrap card border if card-body provides the visual */
        }

        .card.styled-card .card-body {
            background-color: #f8f9fa; /* Light grey background from first code */
            border-radius: 5px;       /* Rounded corners from first code */
            padding: 15px;           /* Specific padding from first code */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Shadow from first code */
            position: relative; /* For absolute positioning of remove button wrapper */
            display: flex;
            flex-direction: column;
        }

        .card.styled-card .card-title {
            color: #007bff; /* Blue color for title from first code */
            margin-bottom: 0.75rem;
        }

        .card.styled-card .card-text-custom { /* Custom class for p tags to match first code's font-size */
            font-size: 14px;
            margin-bottom: 0.5rem; /* Adjust paragraph spacing */
        }
        
        .card.styled-card .card-img-top.job-image {
            max-height: 200px;
            object-fit: cover;
            /* Ensuring top corners match card-body if card has no border and image is first */
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        .remove-button-wrapper {
            position: absolute;
            top: 5px;  /* Inside padding of card-body */
            right: 5px; /* Inside padding of card-body */
            z-index: 10;
        }

        .note {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .note h1 {
            color: red;
            font-size: 1.75rem; /* Adjusted H1 size */
        }

        /* Retaining job-image style from second code if not fully covered by card-img-top */
        .job-image {
            width: 100%;
            /* max-height is in .card.styled-card .card-img-top.job-image */
            /* object-fit is in .card.styled-card .card-img-top.job-image */
            margin-bottom: 10px; /* Only if image is not card-img-top or needs space below */
            /* border-radius is handled by card-img-top or specific styling */
        }
        
    </style>
</head>

<body>

    <?php include_once("admin_navbar.php"); ?>
    <?php include_once("admin_ctg_bar.php"); ?>

    <!-- Search Form adapted from first code's layout -->
    <form action="approved_job.php" method="get" class="container my-4 search-form">
        <div class="row">
            <div class="col-md-5">
                <input id="fsearch" type="search" name="search" class="form-control" placeholder="<?php
                    echo isset($_GET["search"]) ? htmlspecialchars($_GET["search"]) : "Search jobs...";
                ?>">
            </div>
            <div class="col-md-3">
                <select name="filter" id="filter_type_select" class="form-control"> <!-- Changed ID to be unique -->
                    <option value="All Type" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'All Type' ? 'selected' : '');?>>Job Type</option>
                    <option value="Full Time" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'Full Time' ? 'selected' : '');?>>Full Time</option>
                    <option value="Part Time" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'Part Time' ? 'selected' : '');?>>Part Time</option>
                </select>
            </div>
            <div class="col-md-2">
                 <input type="number" name="idcheck" id="job_id_search_input" class="form-control" placeholder="JOB ID" value="<?php echo isset($_GET['idcheck']) ? htmlspecialchars($_GET['idcheck']) : ''; ?>"> <!-- Changed ID to be unique -->
            </div>
            <div class="col-md-2">
                <button type="submit" name="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search <!-- Updated to FA6 icon syntax -->
                </button>
            </div>
        </div>
    </form>

    <!-- Display Filtered Job Type, styled like first code -->
    <h3 id="phpmg" class="text-center mt-3 mb-3">
        <?php
        if (isset($_GET["filter"]) && $_GET['filter'] !== 'All Type' && !empty($_GET['filter'])) {
            echo "Job Type: " . htmlspecialchars($_GET['filter']);
        }
        ?>
    </h3>

    <div class="container">
        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $image_base_path_for_browser = "../../";
                    $full_image_url = "";
                    $job_image_db_path_part = ""; 
                    if (!empty($row['job_image'])) {
                        $job_image_db_path_part = htmlspecialchars($row['job_image']);
                        $full_image_url = $image_base_path_for_browser . $job_image_db_path_part; 
                    }
            ?>
                    <!-- Each job item as a card, structure for remove button form -->
                    <div class="col-md-4 mb-4">
                        <form action="approved_job.php" method="get" style="height: 100%;"> <!-- Form for remove, takes full height of col -->
                            <input type="hidden" name="id" value="<?php echo $row['jobId']; ?>">
                            <div class="card styled-card h-100">
                                <?php
                                $display_image = false;
                                if (!empty($full_image_url)) {
                                    $server_path_to_check_display = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/WorkWise/' . $job_image_db_path_part; // Adjust '/WorkWise/' if your project root is different
                                    if (file_exists($server_path_to_check_display)) {
                                        $display_image = true;
                                    } else {
                                        // Optionally, set a placeholder if file not found but DB entry exists
                                        // $full_image_url = $image_base_path_for_browser . "image/uploads/placeholder.png";
                                        // $display_image = true; // if you want to show placeholder
                                    }
                                }
                                if ($display_image): ?>
                                    <img src="<?php echo $full_image_url; ?>" alt="<?php echo htmlspecialchars($row['title']); ?> image" class="card-img-top job-image">
                                <?php endif; ?>

                                <div class="card-body">
                                    <div class="remove-button-wrapper">
                                        <button type="submit" name="remove" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this job?');" title="Remove Job">
                                            <i class="fas fa-times"></i> <!-- FA6 icon for remove -->
                                        </button>
                                    </div>

                                    <h5 class="card-title"><?php echo htmlspecialchars($row["title"]); ?></h5>
                                    <p class="card-text-custom"><strong>Job Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                                    <p class="card-text-custom"><strong>Job Type:</strong> <?php echo htmlspecialchars($row['jobType']); ?></p>
                                    <p class="card-text-custom"><strong>Company:</strong> <?php echo htmlspecialchars($row['company']); ?></p>
                                    <p class="card-text-custom"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                    <p class="card-text-custom"><strong>Price:</strong> $<?php echo htmlspecialchars($row['price']); ?> per monthly</p>
                                    
                                    <!-- Exit Day from first code, if available in $row -->
                                    <?php if (isset($row['exitDay']) && !empty($row['exitDay'])): ?>
                                        <p class="card-text-custom"><strong>Exit Day:</strong> <span class="text-danger"><?php echo htmlspecialchars($row['exitDay']); ?></span></p>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-2"> <!-- Pushes button to the bottom -->
                                        <!-- "More Details" as a link styled as button, like in first code -->
                                        <a href="more_details.php?jobId=<?php echo $row['jobId']; ?>" class="btn btn-info w-100 apply-btn">
                                            More Details <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
            <?php
                } // End While
            ?>
        </div> <!-- /.row -->
    </div> <!-- /.container -->
<?php
            } else { // No results found
                if (empty($note)) { 
                    $note = "No approved jobs found.";
                }
?>
    <div class="container note"> <!-- Added container for better alignment if needed -->
        <h1><?php echo htmlspecialchars($note); ?></h1>
    </div>
<?php
            } // End if/else for results
if ($conn) { mysqli_close($conn); } 
?>

<!-- Bootstrap JS and dependencies from first code -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>