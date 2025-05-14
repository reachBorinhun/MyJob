<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Job Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ✅ Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .navbar-custom {
            background-color: rgb(85, 182, 243);
        }

        .navbar-custom .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: rgb(85, 182, 243);
            }
        }
    </style>
</head>

<body>

    <!-- ✅ Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Categories</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCategories" aria-controls="navbarCategories" aria-expanded="false" aria-label="Toggle navigation">
                <span><i class="fa fa-bars"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCategories">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Graphics">Graphics & Design</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Programming">Programming & Tech</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Digital">Digital Marketing</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Video">Video & Animation</a></li>
                    <li class="nav-item"><a class="nav-link" hre    f="job_category.php?ctg=Writing">Writing & Translation</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Music">Music & Audio</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=Business">Business</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=AI">AI Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_category.php?ctg=New">New*</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ✅ Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
