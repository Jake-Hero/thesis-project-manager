<!-- Website Icon -->
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">

<nav class="navbar navbar-expand-lg navbar-dark static-top" style="background-color: #A020F0;">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/lpu-b-logo.png" alt="..." height="36">
            <img src="images/lpu-ccs-logo.png" alt="..." height="36">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto align-items-center">                         
                <li class="nav-item fw-bold">
                    <?php if(is_user_verified()): ?>
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo 'profile_pictures/' .$_SESSION['user']['image'] ?>" class="rounded-circle border border-light btn-lg" height="30" alt="Avatar" />
                    </a>        
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="profile.php">Edit My Profile</a>

                        <?php if($_SESSION['user']['role'] >= ROLE_ADVISOR): ?>
                            <a class="dropdown-item" href="admin.php">Administrative Panel</a>
                        <?php endif; ?>

                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>  
            </ul>   
        </div>
    </div>
</nav>