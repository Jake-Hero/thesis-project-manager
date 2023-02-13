<!-- Website Icon -->
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">

<nav class="navbar navbar-expand-lg navbar-dark static-top" style="background-color: #A020F0;">
    <div class="container">
        <a class="navbar-brand" href="../dashboard.php">
            <img src="../assets/images/lpu-b-logo.png" alt="..." height="36">
            <img src="../assets/images/lpu-ccs-logo.png" alt="..." height="36">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto align-items-center">       
                
                <!-- Home / Dashboard -->
                <li class="nav-item <?php if($currentPage == 'dashboard') echo 'fw-bold'; ?>">
                    <a class="nav-link active" href="../dashboard.php">Dashboard</a>
                </li>

                <!-- Different Version of group.php -->
                <li class="nav-item <?php if($currentPage == 'group') echo 'fw-bold'; ?>">
                <?php if($_SESSION['user']['role'] >= ROLE_ADVISOR): ?>
                    <a class="nav-link active" href="../admin/group.php">Group</a>
                <?php else: ?>
                    <a class="nav-link active" href="../group.php">Group</a>                    
                <?php endif; ?>
                </li>

                <li class="nav-item <?php if($currentPage == 'archives') echo 'fw-bold'; ?>">
                    <a class="nav-link active" href="../archive.php">Archives</a>
                </li>

                <li class="nav-item <?php if($currentPage == 'about') echo 'fw-bold'; ?>">
                    <a class="nav-link active" href="../about.php">About</a>                    
                </li>

                <!-- User Profile - Edit, Admin Panel, Logout -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo '../assets/profile_pictures/' . $_SESSION['user']['image'] ?>" class="rounded-circle" height="30" alt="Avatar" />
                    </a>        
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="../profile.php">Edit My Profile</a>

                        <?php if($_SESSION['user']['role'] == ROLE_PANELIST): ?>
                            <a class="dropdown-item" href="../panelist_group.php">Group & Grading</a>
                        <?php endif; ?>

                        <?php if($_SESSION['user']['role'] == ROLE_ADVISOR): ?>
                            <a class="dropdown-item" href="./group.php">Group & Grading</a>
                        <?php endif; ?>

                        <?php if($_SESSION['user']['role'] >= ROLE_ADMIN): ?>
                            <a class="dropdown-item" href="./index.php">Administrative Panel</a>
                        <?php endif; ?>

                        <a class="dropdown-item" href="../logout.php">Logout</a>
                    </div>
                </li>  
            </ul>   
        </div>
    </div>
</nav>