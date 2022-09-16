<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

define ('PROJECT_NAME', 'thesis-project-manager');
define ('ROOT_FOLDER', DIRECTORY_SEPARATOR . PROJECT_NAME);

require_once realpath(dirname(__FILE__) . '/../config/db.php');
require realpath(dirname(__FILE__) . '/../config/mail.php');

require "mobile_detection.php";
require "browser_detection.php";

define("ROLE_STUDENT", 0);
define("ROLE_PANELIST", 1);
define("ROLE_ADVISOR", 2);
define("ROLE_ADMIN", 3);

function getImageUri()
{
    return SITE_ROOT . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
}

function getFullName($id)
{
    global $con;
    $leader = NULL;

    $query = "SELECT fullname FROM users WHERE id = :id limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['id' => $id]);

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);
        $leader = $row['fullname'];
    }

    return $leader;
}

function getUserRole($role) {
    $roleString = array();

    switch($role)
    {
        case ROLE_STUDENT:
            $roleString = "Student";
            break;
        case ROLE_PANELIST:
            $roleString = "Panelist";
            break;
        case ROLE_ADVISOR:
            $roleString = "Subject Advisor";
            break;
        case ROLE_ADMIN:
            $roleString = "Site Admin";
            break;
        default:
            $roleString = "Unknown";
            break;
    }
    return $roleString;
}

function getIPAddress() {  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        $ip = $_SERVER['HTTP_CLIENT_IP'];  
    }  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    }  
    else{  
        $ip = $_SERVER['REMOTE_ADDR'];  
    }  
    return $ip;  
}  

function recordUserVisit()
{
    global $con;
    $browser=new Wolfcast\BrowserDetection;

    $visitData['browser_name']=$browser->getName();
    $visitData['browser_version']=$browser->getVersion();

    $detect=new Mobile_Detect();

    if($detect->isMobile()){
        $visitData['device_type']='Mobile';
    }elseif($detect->isTablet()){
        $visitData['device_type']='Tablet';
    }else{
        $visitData['device_type']='PC';
    }

    if($detect->isiOS()){
        $visitData['device_os']='IOS';
    }elseif($detect->isAndroidOS()){
        $visitData['device_os']='Android';
    }else{
        $visitData['device_os']='Window';
    }

    $visitData['http_url']=(isset($_SERVER['HTTPS'])) ? "https":"http";
    $visitData['http_url'].="//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $visitData['http_ref']='';
    if(isset($_SERVER['HTTP_REFERER'])){
        $visitData['http_ref']=$_SERVER['HTTP_REFERER'];
    }

    $query = "INSERT INTO visitor_data (browser_name, browser_version, device_type, device_os, url, ref) VALUES(:browser_name, :browser_version, :device_type, :device_os, :http_url, :http_ref)";
    $insertStm = $con->prepare($query);
    $insertStm->execute($visitData);
    return true;
}

function signup_user($data)
{
    global $con;
    $errors = array();

    // Field Errors
    if(!preg_match('/^[a-zA-Z ]+$/', $data['fullname']))
        $errors['fullname'] = "Enter a valid full name!";
    if(!preg_match('/^[a-zA-Z]+$/', $data['username']))
        $errors['username'] = "Enter a valid username!";
    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = "Enter a valid email address!";
    if(strlen(trim($data['password'])) < 4)
        $errors['password'] = "Your password must be longer than 4 characters.";

    if(isset($data['username']) && isset($data['email']) && !empty($data['username']) && !empty($data['email']))
    {    
        $query = "SELECT username, email FROM users WHERE username = :username OR email = :email";
        $select_stm = $con->prepare($query);
        $select_stm->execute(['username' => $data['username'], 'email' => $data['email']]);

        if($select_stm->rowCount() > 0)
        {
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);

            if($row['username'] == $data['username'])
                $errors['username'] = 'This username is already taken!';
            else if($row['email'] == $data['email'])
                $errors['email'] = 'This email is already in use!';
        }
    }

    // no error 
    if(count($errors) == 0)
    {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO users (date, ip_addr, fullname, username, email, password) VALUES(:creation, :ip, :fullname, :username, :email, :pass)";
        $insert_stm = $con->prepare($query);
        $insert_stm->bindValue('creation', date("Y-m-d H:i:s")); 
        $insert_stm->bindValue('ip', getIPAddress());
        $insert_stm->bindValue('fullname', $data['fullname']); 
        $insert_stm->bindValue('username', $data['username']); 
        $insert_stm->bindValue('email', $data['email']); 
        $insert_stm->bindValue('pass', $hashed_password);
        $insert_stm->execute();
        
        $_SESSION['username'] = $data['username'];
        $_SESSION['userid'] = $con->lastInsertId();
        $_SESSION['logged_in'] = true;
        $_SESSION['profilepic'] = 'default_profile.jpg'; 
        $_SESSION['role'] = 0;

        $_SESSION['result_popup'] = 
        '
            <script type="text/javascript">
                swal({
                    title: "Registered",
                    type: "success",
                    text: "You are now registered, Please login.",
                    allowOutsideClick: false,
                    showConfirmButton: true,
                    confirmButtonText: "OK"
                })
            </script>        
        ';

        header("Location: login.php");
    }
    return $errors;
}

function login_user($data)
{
    global $con;
    $errors = array();

    // Field Errors

    if(empty($data['username']))
        $errors['all'] = "You didn't fill up the username.";

    // no error 
    if(count($errors) == 0)
    {
        $query = "SELECT * FROM users WHERE (username = :username OR email = :username) limit 1;";
        $select_stm = $con->prepare($query);
        $select_stm->execute(['username' => $data['username'], 'password' => $data['password']]);

        if($select_stm->rowCount() > 0)
        {
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);

            if(password_verify($data['password'], $row['password']))
            {
                $_SESSION['user'] = $row;
                $_SESSION['logged_in'] = true;

                $query = "UPDATE users SET ip_addr = :ip WHERE username = :username OR email = :username limit 1;";
                $update_stm = $con->prepare($query);
                $update_stm->execute(['ip' => getIPAddress(), 'username' => $data['username']]);

                /*if($row['email'] != $row['email_verified'] || is_null($row['email_verified'])) {    
                    
                    sendVerificationCode($row['email']);
                }*/

                header("Location: dashboard.php");
            }
            else 
            {
                $errors['all'] = "You have typed a wrong password!";
            }
        }
        else 
        {
            $errors['all'] = "That username or email doesn't exist!";
        }
    }
    return $errors;
}

function sendVerificationCode($email = NULL)
{
    global $con;

    if(empty($email))
        $email = $_SESSION['user']['email'];

    $errors = array();
    $now = time();

    $vars = array();

    $vars['code'] = rand(pow(10, 5-1), pow(10, 5)-1);
    $vars['email'] = $email;

    $query = "SELECT * FROM verified WHERE email = :email";
    $select_stm = $con->prepare($query);
    $select_stm->bindValue(':email', $vars['email']);
    $select_stm->execute();

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);

        if($row['expiry'] < $now)
        {
            $vars['expiry'] = time() + (60 * 5); // 5 minutes expiration

            $query = "UPDATE verified SET code = :code, expiry = :expiry, email = :email WHERE email = :email";
            $insert_stm = $con->prepare($query);
            $insert_stm->execute($vars);

            unset($_SESSION['message_error']);
            $_SESSION['message'] = "A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.";
            $message = "Your verification code is: ". $vars['code'];
            send_mail($vars['email'], "Verify your account! - Verification Code", $message);                  
        }
        else 
        {
            unset($_SESSION['message']);
            $timeLapse = ($row['expiry'] - $now) / 60 % 60;
            $_SESSION['message_error'] = "A code was already sent to your email. Please wait for <strong>" .$timeLapse. " minutes</strong> before requesting for a new code.";
        }
    }
    else
    {
        $vars['expiry'] = time() + (60 * 5); // 5 minutes expiration

        $query = "INSERT INTO verified (code, expiry, email) VALUES(:code, :expiry, :email)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute($vars);

        $_SESSION['message'] = "A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.";
        $message = "Your verification code is: ". $vars['code'];
        send_mail($vars['email'], "Verify your account! - Verification Code", $message);  
    }
    return $errors;
}

function is_user_verified($id = NULL)
{
    global $con;

    if(!isset($id))
        $id = $_SESSION['user']['id'];

    $query = "SELECT email, email_verified FROM users WHERE id = :id limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['id' => $id]);

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);

        if($row['email'] == $row['email_verified'])
            return true;
    }

    return false;
}

function is_user_valid()
{
    global $con;

    if(isset($_SESSION['user']['id']))
    {
        $query = "SELECT * FROM users WHERE id = :id";
        $selectStmt = $con->prepare($query);
        $selectStmt->bindValue('id', $_SESSION['user']['id']);
        $selectStmt->execute();

        if($selectStmt->rowCount() < 1)
        {
            header("Location: logout.php");
            die;
        }
        else
        {
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user'] = $row;
        }
    }
}

function is_user_login($redirect = true)
{
    if(isset($_SESSION['user']) && isset($_SESSION['logged_in']))
        return true;

    if($redirect)
    {
        header("Location: index.php");
        die;
    }
    else
    {
        return false;
    }
}

function profileSave()
{
    global $con;

    $hashed_password = NULL;
    $username = $_SESSION['user']['username'];
    $password = $_POST['verifypassword'];        

    $query = "SELECT id, email, password FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['username' => $username]);
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

    if(password_verify($password, $row['password']))
    { // if verify password matches the password
        // if a user uploaded a profile picture
        if(!empty($_POST['email']) > 0 && $_POST['email'] == $row['email'])
        {
            $_SESSION['error_message'] = "Your new email cannot be the same as your old one!";
        }        
        else if(!empty($_POST['password']) && password_verify($_POST['password'], $row['password']))
        {
            $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
        }
        else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z ]+$/', $_POST['fullname']))
        {
            $_SESSION['error_message'] = "Enter a valid full name!";
        }
        else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z]+$/', $_POST['username']))
        {
            $_SESSION['error_message'] = "Enter a valid username!";
        }
        else 
        {
            if(!empty($_POST['username']))
            {
                $query = "SELECT COUNT(*) FROM users WHERE username = :username limit 1;";
                $countStm = $con->prepare($query);
                $countStm->execute(['username' => $_POST['username']]);
                $existing_username = $countStm->fetchColumn();
            }
            if(!empty($_POST['email']))
            {
                $query = "SELECT COUNT(*) FROM users WHERE email = :email limit 1;";
                $countStm = $con->prepare($query);
                $countStm->execute(['email' => $_POST['email']]);
                $existing_email = $countStm->fetchColumn();
            }

            if(!empty($_POST['username']) && $existing_username)
            {
                $_SESSION['error_message'] = "Choose another username, A user is already using that email!";
            }
            else if(!empty($_POSt['email']) && $existing_email)
            {
                $_SESSION['error_message'] = "Choose another email, A user is already using that email!";
            }
            else 
            {
                if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                    uploadImages();
               
                if(!empty($_POST['email']))
                {
                    $message = "Your email address " .$_SESSION['user']['email']. " was replaced by a new email address.";
                    send_mail($_SESSION['user']['email'], "Changed E-Mail", $message);

                    $_SESSION['result_popup'] =
                            "
                                <script type=\"text/javascript\">
                                    swal({
                                        title: \"Verification\",
                                        type: \"warning\",
                                        text: \"You are not verified yet, Please verify your account via 'Edit My Profile'.\",
                                        allowOutsideClick: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'OK'
                                        });
                                </script>                        
                            ";     
                            
                    //sendVerificationCode($_POST['email']);
                }
                if(!empty($_POST['password']))
                {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                    $message = "Your password was changed;";
                    send_mail($_SESSION['user']['email'], "Changed password", $message);
                }

                $fullname = NULL;
                if(!empty($_POST['fullname'])) $fullname = $_POST['fullname'];                
                $username = NULL;
                if(!empty($_POST['username'])) $username = $_POST['username'];
                $email = NULL;
                if(!empty($_POST['email'])) $email = $_POST['email'];

                $query = "UPDATE users SET 
                            fullname = COALESCE(:fullname, fullname),
                            username = COALESCE(:username, username),
                            email = COALESCE(:email, email),
                            password = COALESCE(:password, password) 
                          WHERE id = :id";

                $updateStmt = $con->prepare($query);
                $updateStmt->bindValue(':fullname', $fullname, PDO::PARAM_STR);
                $updateStmt->bindValue(':username', $username, PDO::PARAM_STR);
                $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
                $updateStmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
                $updateStmt->bindValue(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
                $updateStmt->execute();
            
                $_SESSION['success_message'] = "Changes to your profile has been saved!";
            }
        }
    }
    else 
    {
        $_SESSION['error_message'] = "Verify Password does not match with the User's Password!";
    }
    return true;
}

function adminEditProfile($str)
{
    global $con;

    $hashed_password = NULL;
    $username = $str;     

    $query = "SELECT * FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['username' => $username]);
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

    if(!empty($_POST['email']) && $_POST['email'] == $row['email'])
    {
        $_SESSION['error_message'] = "Your new email cannot be the same as your old one!";
    }        
    else if(!empty($_POST['password']) && password_verify($_POST['password'], $row['password']))
    {
        $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
    }
    else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z ]+$/', $_POST['fullname']))
    {
        $_SESSION['error_message'] = "Enter a valid full name!";
    }
    else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z]+$/', $_POST['username']))
    {
        $_SESSION['error_message'] = "Enter a valid username!";
    }
    else 
    {
        if(!empty($_POST['username']))
        {
            $query = "SELECT COUNT(*) FROM users WHERE username = :username limit 1;";
            $countStm = $con->prepare($query);
            $countStm->execute(['username' => $_POST['username']]);
            $existing_username = $countStm->fetchColumn();
        }
        if(!empty($_POST['email']))
        {
            $query = "SELECT COUNT(*) FROM users WHERE email = :email limit 1;";
            $countStm = $con->prepare($query);
            $countStm->execute(['email' => $_POST['email']]);
            $existing_email = $countStm->fetchColumn();
        }

        if(!empty($_POST['username']) && $existing_username)
        {
            $_SESSION['error_message'] = "Choose another username, A user is already using that email!";
        }
        else if(!empty($_POSt['email']) && $existing_email)
        {
            $_SESSION['error_message'] = "Choose another email, A user is already using that email!";
        }
        else 
        {
            if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                adminuploadImages($row);
            
            if(!empty($_POST['email']))
            {
                $message = "Your email address " .$row['email']. " was replaced by a new email address.";
                send_mail($row['email'], "Changed E-Mail", $message);
            }
            if(!empty($_POST['password']))
            {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                $message = "Your password was changed;";
                send_mail($row['email'], "Changed password", $message);
            }

            $fullname = NULL;
            if(!empty($_POST['fullname'])) $fullname = $_POST['fullname'];                
            $username = NULL;
            if(!empty($_POST['username'])) $username = $_POST['username'];
            $email = NULL;
            if(!empty($_POST['email'])) $email = $_POST['email'];
            $role = NULL;
            if(!empty($_POST['role']) && $_POST['role'] >= 0) $role = $_POST['role'];

            $query = "UPDATE users SET 
                        fullname = COALESCE(:fullname, fullname),
                        username = COALESCE(:username, username),
                        email = COALESCE(:email, email),
                        password = COALESCE(:password, password),
                        role = COALESCE(:role, role)
                        WHERE id = :id";

            $updateStmt = $con->prepare($query);
            $updateStmt->bindValue(':fullname', $fullname, PDO::PARAM_STR);
            $updateStmt->bindValue(':username', $username, PDO::PARAM_STR);
            $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $updateStmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            $updateStmt->bindValue(':role', $role, PDO::PARAM_STR);
            $updateStmt->bindValue(':id', $row['id'], PDO::PARAM_INT);
            $updateStmt->execute();
        
            $_SESSION['success_message'] = "Changes to this profile has been saved!";

            $query = "SELECT * FROM users WHERE id = :id LIMIT 1;";
            $selectStm = $con->prepare($query);
            $selectStm->execute(['id' => $row['id']]);
            $row = $selectStm->fetch(PDO::FETCH_ASSOC);
        }
    }
    return $row;
}

function adminuploadImages(array $row)
{
    global $con;
    $arr = array();

    $arr['id'] = $row['id'];
    $name = $row['username'];

    $imgName = $_FILES['image']['name'];
    $imgSize = $_FILES['image']['size'];
    $tmpName = $_FILES['image']['tmp_name'];

    $validExt = ['jpg', 'jpeg', 'png'];
    $imgExt = explode('.', $imgName);
    $imgExt = strtolower(end($imgExt));

    if(!in_array($imgExt, $validExt))
    {
        alert("Invalid file extension (jpg, jpeg, png is only allowed!)");
    }
    else if($imgSize > 1200000)
    {
        alert("File size is too large!");
    }
    else 
    {
        $imgName = explode('.', $imgName);

        $arr['newImgName'] = $name . " - " . date("m.d.y H.i.s");
        $arr['newImgName'] .= '.' . $imgExt;

        $query = "UPDATE users SET image = :newImgName WHERE id = :id";
        $q = $con->prepare($query);
        $q->execute($arr);

        $tmp_path = "assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "assets/profile_pictures/" .$arr['newImgName'];

        move_uploaded_file($tmpName, $tmp_path);
        resize_image($tmp_path, $real_path);

        unlink($tmp_path);

        header("Refresh:0");
    }
    return true;
}

function uploadImages()
{
    global $con;
    $arr = array();

    $arr['id'] = $_SESSION['user']['id'];
    $name = $_SESSION['user']['username'];

    $imgName = $_FILES['image']['name'];
    $imgSize = $_FILES['image']['size'];
    $tmpName = $_FILES['image']['tmp_name'];

    $validExt = ['jpg', 'jpeg', 'png'];
    $imgExt = explode('.', $imgName);
    $imgExt = strtolower(end($imgExt));

    if(!in_array($imgExt, $validExt))
    {
        alert("Invalid file extension (jpg, jpeg, png is only allowed!)");
    }
    else if($imgSize > 1200000)
    {
        alert("File size is too large!");
    }
    else 
    {
        $imgName = explode('.', $imgName);

        $arr['newImgName'] = $name . " - " . date("m.d.y H.i.s");
        $arr['newImgName'] .= '.' . $imgExt;

        $query = "UPDATE users SET image = :newImgName WHERE id = :id";
        $q = $con->prepare($query);
        $q->execute($arr);

        $tmp_path = "assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "assets/profile_pictures/" .$arr['newImgName'];

        move_uploaded_file($tmpName, $tmp_path);
        resize_image($tmp_path, $real_path);

        unlink($tmp_path);

        header("Refresh:0");
    }
    return true;
}

function scaleImage($resourceType, $image_width, $image_height, $resizeWidth, $resizeHeight)
{
    $imageLayer = imagecreatetruecolor($resizeWidth, $resizeHeight);
    imagecopyresampled($imageLayer, $resourceType, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $image_width, $image_height);
    return $imageLayer;
}

function resize_image($source_image, $resize_image)
{
    $new_width = 300;
    $new_height = 300;

    $sourceProperties = getimagesize($source_image);
    $uploadImageType = $sourceProperties[2];
    $sourceImageWidth = $sourceProperties[0];
    $sourceImageHeight = $sourceProperties[1];
    $ratio = $sourceImageWidth / $sourceImageHeight;

    if ($sourceImageWidth > $sourceImageHeight) {
        $sourceImageWidth = ceil($sourceImageWidth-($sourceImageWidth*abs($ratio-$new_width/$new_height)));
    } else {
        $sourceImageHeight = ceil($sourceImageHeight-($sourceImageHeight*abs($ratio-$new_width/$new_height)));
    }

    switch ($uploadImageType)
    {
        case IMAGETYPE_JPEG:
            $resourceType = imagecreatefromjpeg($source_image);
            $source_image = scaleImage($resourceType, $sourceImageWidth, $sourceImageHeight, $new_width, $new_height);
            imagejpeg($source_image, $resize_image);
        break;

        case IMAGETYPE_GIF:
            $resourceType = imagecreatefromgif($source_image);
            $source_image = scaleImage($resourceType, $sourceImageWidth, $sourceImageHeight, $new_width, $new_height);
            imagegif($source_image, $resize_image);
        break;

        case IMAGETYPE_PNG:
            $resourceType = imagecreatefrompng($source_image);
            $source_image = scaleImage($resourceType, $sourceImageWidth, $sourceImageHeight, $new_width, $new_height);
            imagepng($source_image, $resize_image);
        break;

        case IMAGETYPE_JPG:
            $resourceType = imagecreatefrompng($source_image);
            $source_image = scaleImage($resourceType, $sourceImageWidth, $sourceImageHeight, $new_width, $new_height);
            imagepng($source_image, $resize_image);
        break;
    }

    return $resize_image;
}

function create_group($data)
{
    global $con;
    $errors = array();

    // Field Errors
    if(empty($data['group_title']))
        $errors['error_message'] = "You did not give the group a thesis title.";
    else if(empty($data['group_leader']))
        $errors['error_message'] = "Make sure to assign a leader!";

    else if(!preg_match('/^[a-zA-Z ]+$/', $data['group_title']))
        $errors['error_message'] = "Enter a valid thesis title or group name!";

    if(!empty($data['group_title']))
    {    
        $query = "SELECT * FROM groups WHERE group_title = :title";
        $select_stm = $con->prepare($query);
        $select_stm->execute(['title' => $data['group_title']]);

        if($select_stm->rowCount() > 0)
        {
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);

            $errors['error_message'] = 'This thesis title or group name is already taken!';
        }
    }

    // no error 
    if(count($errors) == 0)
    {
        $query = "SELECT id, group_id FROM users WHERE id = :user_id OR username = :user_name";
        $select_stm = $con->prepare($query);
        $select_stm->bindValue('user_id', (int) $data['group_leader'], PDO::PARAM_INT);
        $select_stm->bindValue('user_name', $data['group_leader']);
        $select_stm->execute();

        if($select_stm->rowCount() > 0)
        {
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);   

            if($row['group_id'] > 0)
            {
                $errors['error_message'] = "User is already a leader or member of another thesis group.";
            }
            else 
            {
                $query = "INSERT INTO groups (creation, group_leader, group_title) VALUES(:creation, :leader, :title)";
                $insert_stm = $con->prepare($query);
                $insert_stm->execute(['creation' => date("Y-m-d H:i:s"), 'leader' => $row['id'], 'title' => $data['group_title']]);
            
                $query = "UPDATE users SET group_id = :groupid WHERE id = :leader";
                $updateStmt = $con->prepare($query);
                $updateStmt->execute(['groupid' => $con->lastInsertId(), 'leader' => $row['id']]);
            
                header("Location: edit_group.php?id=" .$con->lastInsertId());
            }
        }
        else 
        {
            $errors['error_message'] = "Specify a valid user, The specified ID or username is invalid.";
        }
    }
    return $errors;
}

function adminEditGroup($id)
{
    global $con;

    $hashed_password = NULL;
    $username = $str;     

    $query = "SELECT * FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['username' => $username]);
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

    if(!empty($_POST['email']) && $_POST['email'] == $row['email'])
    {
        $_SESSION['error_message'] = "Your new email cannot be the same as your old one!";
    }        
    else if(!empty($_POST['password']) && password_verify($_POST['password'], $row['password']))
    {
        $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
    }
    else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z ]+$/', $_POST['fullname']))
    {
        $_SESSION['error_message'] = "Enter a valid full name!";
    }
    else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z]+$/', $_POST['username']))
    {
        $_SESSION['error_message'] = "Enter a valid username!";
    }
    else 
    {
        if(!empty($_POST['username']))
        {
            $query = "SELECT COUNT(*) FROM users WHERE username = :username limit 1;";
            $countStm = $con->prepare($query);
            $countStm->execute(['username' => $_POST['username']]);
            $existing_username = $countStm->fetchColumn();
        }
        if(!empty($_POST['email']))
        {
            $query = "SELECT COUNT(*) FROM users WHERE email = :email limit 1;";
            $countStm = $con->prepare($query);
            $countStm->execute(['email' => $_POST['email']]);
            $existing_email = $countStm->fetchColumn();
        }

        if(!empty($_POST['username']) && $existing_username)
        {
            $_SESSION['error_message'] = "Choose another username, A user is already using that email!";
        }
        else if(!empty($_POSt['email']) && $existing_email)
        {
            $_SESSION['error_message'] = "Choose another email, A user is already using that email!";
        }
        else 
        {
            if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                adminuploadImages($row);
            
            if(!empty($_POST['email']))
            {
                $message = "Your email address " .$row['email']. " was replaced by a new email address.";
                send_mail($row['email'], "Changed E-Mail", $message);
            }
            if(!empty($_POST['password']))
            {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                $message = "Your password was changed;";
                send_mail($row['email'], "Changed password", $message);
            }

            $fullname = NULL;
            if(!empty($_POST['fullname'])) $fullname = $_POST['fullname'];                
            $username = NULL;
            if(!empty($_POST['username'])) $username = $_POST['username'];
            $email = NULL;
            if(!empty($_POST['email'])) $email = $_POST['email'];
            $role = NULL;
            if(!empty($_POST['role']) && $_POST['role'] >= 0) $role = $_POST['role'];

            $query = "UPDATE users SET 
                        fullname = COALESCE(:fullname, fullname),
                        username = COALESCE(:username, username),
                        email = COALESCE(:email, email),
                        password = COALESCE(:password, password),
                        role = COALESCE(:role, role)
                        WHERE id = :id";

            $updateStmt = $con->prepare($query);
            $updateStmt->bindValue(':fullname', $fullname, PDO::PARAM_STR);
            $updateStmt->bindValue(':username', $username, PDO::PARAM_STR);
            $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $updateStmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            $updateStmt->bindValue(':role', $role, PDO::PARAM_STR);
            $updateStmt->bindValue(':id', $row['id'], PDO::PARAM_INT);
            $updateStmt->execute();
        
            $_SESSION['success_message'] = "Changes to this profile has been saved!";

            $query = "SELECT * FROM users WHERE id = :id LIMIT 1;";
            $selectStm = $con->prepare($query);
            $selectStm->execute(['id' => $row['id']]);
            $row = $selectStm->fetch(PDO::FETCH_ASSOC);
        }
    }
    return $row;
}

?>