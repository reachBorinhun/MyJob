<html>
<style>
    /* -- Modernized Footer -- */
    .body {
        background-color: white;
        margin: 0;
        padding: 0;
        line-height: 1.2;
        scroll-behavior: smooth;
        box-sizing: border-box;
        margin-top: 150px;
    }

    footer {
        background: linear-gradient(135deg, #355CFF, #2EC4B6);
        padding: 60px 10%;
        color: white;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    .footer-column {
        flex: 1 1 20%;
        margin-bottom: 40px;
        min-width: 200px;
    }

    .footer-column h4 {
        font-size: 20px;
        margin-bottom: 20px;
        position: relative;
        font-weight: 600;
        color: #FFD700;
    }

    .footer-column h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -8px;
        height: 3px;
        width: 40px;
        background-color: #ffffff;
        border-radius: 2px;
    }

    .footer-column ul li {
        margin-bottom: 12px;
    }

    .footer-column ul li a {
        color: #f2f2f2;
        text-decoration: none;
        font-size: 16px;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-column ul li a:hover {
        color: #FFD700;
        transform: translateX(5px);
    }

    .footer-bottom {
        width: 100%;
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 15px;
        color: #f0f0f0;
    }

    @media (max-width: 768px) {
        .footer-column {
            flex: 1 1 45%;
            text-align: center;
        }

        .footer-column h4::after {
            left: 50%;
            transform: translateX(-50%);
        }
    }

    @media (max-width: 500px) {
        .footer-column {
            flex: 1 1 100%;
        }
    }
</style>

<div class="body">
    <footer>

        <div class="footer-column">
            <h4>Job Categories</h4>
            <ul>
                <li><a href="find_job.php">All</a></li>
                <li><a href="job_category.php?ctg=Graphics">Graphics & Design</a></li>
                <li><a href="job_category.php?ctg=Programming">Programming & Tech</a></li>
                <li><a href="job_category.php?ctg=Digital">Digital Marketing</a></li>
                <li><a href="job_category.php?ctg=Video">Video & Animation</a></li>
                <li><a href="job_category.php?ctg=Writing">Writing & Translation</a></li>
                <li><a href="job_category.php?ctg=Music">Music & Audio</a></li>
                <li><a href="job_category.php?ctg=Business">Business</a></li>
                <li><a href="job_category.php?ctg=AI">AI Services</a></li>
                <li><a href="job_category.php?ctg=New">New*</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>About Us</h4>
            <ul>
                <li><a href="#">Career</a></li>
                <li><a href="#">Press & News</a></li>
                <li><a href="#">Partnerships</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Help & Support</a></li>
                <li><a href="#">Trust & Safety</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>My Account</h4>
            <ul>
                <li><a href="#">My Account</a></li>
                <li><a href="#">Find Talent</a></li>
                <li><a href="#">Find Freelance</a></li>
            </ul>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Jobio Group. All rights reserved.</p>
        </div>

    </footer>
</div>
</html>
