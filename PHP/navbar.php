<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- Embedded CSS -->
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .navbar-logo img {
        height: 50px;
    }

    .navbar-links {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .navbar-links a {
        color: black;
        text-decoration: none;
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .navbar-links a:hover,
    .navbar-links a.active {
        color:rgb(9, 54, 188);
    }

    .navbar-toggle {
        display: none;
        font-size: 1.5rem;
        color: white;
        cursor: pointer;
    }

    @media screen and (max-width: 768px) {
        .navbar {
            flex-wrap: wrap;
        }

        .navbar-links {
            display: none;
            flex-direction: column;
            width: 100%;
            padding: 1rem 0;
        }

        .navbar.responsive .navbar-links {
            display: flex;
        }

        .navbar-toggle {
            display: block;
        }

        .navbar-links a {
            padding: 0.75rem 1.5rem;
            text-align: left;
        }
    }
</style>

<!-- Navbar HTML -->
<div class="navbar" id="navbarid">
    <div class="navbar-logo">
        <a href="index.php">
            <img src="../Image/logo/ITJobs.png" alt="ITJobs Logo">
        </a>
    </div>

    <div class="navbar-toggle" onclick="toggleNavbar()">
        <i class="fa fa-bars"></i>
    </div>

    <div class="navbar-links">
        <a class="<?php echo $active1 ?>" href="index.php"><i class="fa fa-home"></i> Home</a>
        <a class="<?php echo $active2 ?>" href="find_job.php"><i class="fa fa-search"></i> Search Job</a>
        <a class="<?php echo $active4 ?>" href="about.php"><i class="fa fa-info-circle"></i> About Us</a>
        <a class="<?php echo $active5 ?>" href="ContectUs.php"><i class="fa fa-envelope"></i> Contact Us</a>
        <a class="<?php echo $active6 ?>" href="login.php"><i class="fa fa-user"></i> Login</a>
    </div>
</div>

<!-- Script for responsive toggle -->
<script>
    function toggleNavbar() {
        var navbar = document.getElementById("navbarid");
        navbar.classList.toggle("responsive");
    }
</script>
