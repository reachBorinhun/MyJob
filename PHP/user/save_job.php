<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

include("../conn.php"); // Ensure $conn is established here
$active4 = "active"; // For your navbar
$userid = $_SESSION['id'];
$note = ""; // For general messages
$success_message = "";
$error_message = "";

// Handle flash messages from redirects
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// --- HANDLE REMOVE ACTION (POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_bookmark_id'])) {
    $bookmark_id_to_remove = filter_input(INPUT_POST, 'remove_bookmark_id', FILTER_VALIDATE_INT);

    if ($bookmark_id_to_remove) {
        // Ensure the bookmark belongs to the current user before deleting
        $sql_delete = "DELETE FROM bmjob WHERE id = ? AND userId = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "ii", $bookmark_id_to_remove, $userid);
            if (mysqli_stmt_execute($stmt_delete)) {
                if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
                    $_SESSION['success_message'] = "Bookmark removed successfully.";
                } else {
                    $_SESSION['error_message'] = "Could not remove bookmark or it was already removed.";
                }
            } else {
                $_SESSION['error_message'] = "Error removing bookmark: " . htmlspecialchars(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_delete);
        } else {
            $_SESSION['error_message'] = "Error preparing delete statement: " . htmlspecialchars(mysqli_error($conn));
        }
    } else {
        $_SESSION['error_message'] = "Invalid bookmark ID for removal.";
    }
    header('Location: save_job.php'); // Redirect to clear POST data and show message
    exit();
}

// --- HANDLE SEARCH AND FILTER (GET request) ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$job_type_filter = isset($_GET['filter']) ? $_GET['filter'] : 'All Type';

$sql_base = "SELECT bm.id as bookmark_id, job.*
             FROM bmjob bm
             JOIN jobtable job ON bm.jobId = job.jobId
             WHERE bm.userId = ?"; // User specific

$conditions = [];
$params = [$userid]; // Start with userid for the base condition
$param_types = "i"; // Type for userid

if (!empty($search_term)) {
    $conditions[] = "(job.title LIKE ? OR job.company LIKE ? OR job.location LIKE ?)";
    $like_search_term = "%" . $search_term . "%";
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $param_types .= "sss";
}

if ($job_type_filter !== 'All Type' && ($job_type_filter === 'Full Time' || $job_type_filter === 'Part Time')) {
    $conditions[] = "job.jobType = ?";
    $params[] = $job_type_filter;
    $param_types .= "s";
}

if (!empty($conditions)) {
    $sql_base .= " AND " . implode(" AND ", $conditions);
}
$sql_base .= " ORDER BY bm.id DESC"; // Or job.title, etc.

$stmt_select = mysqli_prepare($conn, $sql_base);
if ($stmt_select) {
    if (!empty($params) && !empty($param_types)) {
        // mysqli_stmt_bind_param needs references, so we create them
        $bind_names[] = $param_types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt_select], $bind_names));
    }
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
} else {
    $error_message = "Error preparing search statement: " . htmlspecialchars(mysqli_error($conn));
    $result = false; // Ensure result is false on error
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Jobs - WorkWise</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Your Custom CSS (can override Bootstrap or add new styles) -->
    <link rel="stylesheet" href="../../CSS/job_list_bootstrap.css"> <!-- Create this file for custom styles -->
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }
        .main-color-bg {
            background-color: rgb(55, 90, 196) !important;
            border-color: rgb(55, 90, 196) !important;
        }
        .main-color-text {
            color: rgb(55, 90, 196) !important;
        }
        .btn-main-color {
            background-color: rgb(55, 90, 196);
            color: white;
            border-color: rgb(45, 80, 186); /* Slightly darker for border */
        }
        .btn-main-color:hover {
            background-color: rgb(45, 80, 186);
            color: white;
            border-color: rgb(35, 70, 176);
        }
        .card {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header.main-color-bg {
            color: white;
        }
        .job-detail-label {
            font-weight: bold;
            color: #555;
        }
        .search-filter-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .remove-btn-container {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>

<body>

    <?php include_once("login_navbar.php"); ?>

    <div class="container mt-4">
        <h2 class="mb-4 main-color-text">My Saved Jobs</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Form -->
        <div class="search-filter-section">
            <form action="save_job.php" method="get" class="form-row align-items-end">
                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="search" class="sr-only">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           placeholder="Search by title, company, location..."
                           value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label for="filter" class="sr-only">Job Type</label>
                    <select name="filter" id="filter" class="form-control">
                        <option value="All Type" <?php if ($job_type_filter == 'All Type') echo 'selected'; ?>>All Job Types</option>
                        <option value="Full Time" <?php if ($job_type_filter == 'Full Time') echo 'selected'; ?>>Full Time</option>
                        <option value="Part Time" <?php if ($job_type_filter == 'Part Time') echo 'selected'; ?>>Part Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-main-color btn-block"><i class="fa fa-fw fa-search"></i> Filter / Search</button>
                </div>
            </form>
            <?php if (!empty($search_term) || $job_type_filter !== 'All Type'): ?>
                <div class="mt-2">
                    <small>
                        Showing results for:
                        <?php if (!empty($search_term)) echo "<em>'" . htmlspecialchars($search_term) . "'</em> "; ?>
                        <?php if ($job_type_filter !== 'All Type') echo "Job Type: <em>" . htmlspecialchars($job_type_filter) . "</em>"; ?>
                        <a href="save_job.php" class="ml-2">Clear Filters</a>
                    </small>
                </div>
            <?php endif; ?>
        </div>


        <!-- Job Listings -->
        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card position-relative">
                            <div class="card-header main-color-bg text-white">
                                <h5 class="card-title mb-0" style="font-size: 1.1rem;"><?php echo htmlspecialchars($row["title"]); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="remove-btn-container">
                                     <form action="save_job.php" method="post" onsubmit="return confirm('Are you sure you want to remove this saved job?');">
                                        <input type="hidden" name="remove_bookmark_id" value="<?php echo htmlspecialchars($row['bookmark_id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Bookmark">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                </div>

                                <p class="card-text mb-1"><span class="job-detail-label">Company:</span> <?php echo htmlspecialchars($row['company']); ?></p>
                                <p class="card-text mb-1"><span class="job-detail-label">Location:</span> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="card-text mb-1"><span class="job-detail-label">Type:</span> <?php echo htmlspecialchars($row['jobType']); ?></p>
                                <?php if (!empty($row['category'])): ?>
                                    <p class="card-text mb-1"><span class="job-detail-label">Category:</span> <?php echo htmlspecialchars($row['category']); ?></p>
                                <?php endif; ?>
                                <p class="card-text mb-3"><span class="job-detail-label">Salary:</span> $<?php echo htmlspecialchars($row['price']); ?> per monthly</p>

                                <a href="more_details.php?jobId=<?php echo htmlspecialchars($row['jobId']); ?>"
                                   class="btn btn-outline-secondary btn-sm">
                                    More Details <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <?php
                        if (!empty($search_term) || $job_type_filter !== 'All Type') {
                            echo "No saved jobs match your current filter/search criteria.";
                        } else {
                            echo "You haven't saved any jobs yet.";
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            if ($stmt_select) mysqli_stmt_close($stmt_select);
            // mysqli_close($conn); // Optional: close connection if appropriate
            ?>
        </div> <!-- /.row -->
    </div> <!-- /.container -->

    <!-- Bootstrap JS and dependencies (jQuery, Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>