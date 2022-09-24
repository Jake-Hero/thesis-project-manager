<!-- Website Icon -->
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">

<nav class="navbar navbar-expand-lg navbar-dark static-top" style="background-color: #A020F0;">
    <div class="container">
        <a class="navbar-brand" href="<?php echo ROOT_FOLDER; ?>/dashboard.php">
            <img src="<?php echo ROOT_FOLDER; ?>/assets/images/lpu-b-logo.png" alt="..." height="36">
            <img src="<?php echo ROOT_FOLDER; ?>/assets/images/lpu-ccs-logo.png" alt="..." height="36">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto align-items-center">       
                
                <!-- Home / Dashboard -->
                <li class="nav-item <?php if($currentPage == 'dashboard') echo 'fw-bold'; ?>">
                    <a class="nav-link active" href="<?php echo ROOT_FOLDER; ?>/dashboard.php">Dashboard</a>
                </li>

                <!-- Different Version of group.php -->
                <li class="nav-item <?php if($currentPage == 'group') echo 'fw-bold'; ?>">
                <?php if($_SESSION['user']['role'] >= ROLE_ADVISOR): ?>
                    <a class="nav-link active" href="<?php echo ROOT_FOLDER; ?>/admin/group.php">Group</a>
                <?php else: ?>
                    <a class="nav-link active" href="<?php echo ROOT_FOLDER; ?>/group.php">Group</a>                    
                <?php endif; ?>
                </li>

                <li class="nav-item <?php if($currentPage == 'about') echo 'fw-bold'; ?>">
                    <a class="nav-link active" href="<?php echo ROOT_FOLDER; ?>/about.php">About</a>                    
                </li>

                <!-- User Profile - Edit, Admin Panel, Logout -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' . $_SESSION['user']['image'] ?>" class="rounded-circle" height="30" alt="Avatar" />
                    </a>        
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="<?php echo ROOT_FOLDER; ?>/profile.php">Edit My Profile</a>

                        <?php if($_SESSION['user']['role'] >= ROLE_ADVISOR): ?>
                            <a class="dropdown-item" href="<?php echo ROOT_FOLDER; ?>/admin/index.php">Administrative Panel</a>
                        <?php endif; ?>

                        <a class="dropdown-item" href="<?php echo ROOT_FOLDER; ?>/logout.php">Logout</a>
                    </div>
                </li>  
            </ul>   
        </div>
    </div>
</nav>