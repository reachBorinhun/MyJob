<?php 
if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
} 
?>

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
        flex-wrap: wrap;
        padding: 0.75rem 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        background-color: #fff;
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
        flex-wrap: wrap;
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
        color: rgb(9, 54, 188);
    }

    .navbar-toggle {
        display: none;
        font-size: 1.5rem;
        color: black;
        cursor: pointer;
    }

    .pimge {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        border: 2px solid #ccc;
    }

    @media screen and (max-width: 768px) {
        .navbar-links {
            display: none;
            flex-direction: column;
            width: 100%;
            margin-top: 1rem;
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
            width: 100%;
        }
    }
</style>

<!-- Navbar HTML -->
<div class="navbar" id="navbarid">
    <div class="navbar-logo">
        <a href="home.php"><img src="../../Image/logo/Jobio_1.png" alt="Logo"></a>
    </div>

    <div class="navbar-toggle" onclick="toggleNavbar()">
        <i class="fa fa-bars"></i>
    </div>

    <div class="navbar-links">
        <a class="<?php echo $active1?>" href="home.php"><i class="fa fa-home"></i> Home</a>
        <a class="<?php echo $active2?>" href="user.php"><i class="fa fa-search"></i> Search Job</a>
        <a class="<?php echo $active4?>" href="save_job.php"><i class="fa fa-bookmark"></i> Save Job</a>
        <a class="<?php echo $active3?>" href="login_ContactUs.php"><i class="fa fa-envelope"></i> Contact Us</a>
        <a class="<?php echo $active5?>" href="../index.php"><i class="fa fa-sign-out"></i> Log Out</a>
        <a class="<?php echo $active6?>" href="profile.php" id="profile">
            <div class="pimge" style="background-image: url('UploadImage/<?php echo $_SESSION['image']; ?>');"></div>
        </a>
    </div>
</div>

<!-- Script for responsive toggle -->
<script>
    function toggleNavbar() {
        var navbar = document.getElementById("navbarid");
        navbar.classList.toggle("responsive");
    }
</script>
