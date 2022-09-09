<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

require_once "db.php";
require "mobile_detection.php";
require "browser_detection.php";
require "mail.php";

define("ROLE_STUDENT", 0);
define("ROLE_PANELIST", 1);
define("ROLE_ADVISOR", 2);
define("ROLE_ADMIN", 3);

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
        $arr['username'] = $data['username'];
        $arr['email'] = $data['email'];

        $query = "SELECT username, email FROM users WHERE username = :username OR email = :email";
        $select_stm = $con->prepare($query);
        $select_stm->execute($arr);

        if($select_stm->rowCount() > 0)
        {
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);

            if($row['username'] == $arr['username'])
                $errors['username'] = 'This username is already taken!';
            if($row['email'] == $arr['email'])
                $errors['email'] = 'This email is already in use!';
        }
    }

    // no error 
    if(count($errors) == 0)
    {
        $arr['fullname'] = $data['fullname'];
        $arr['ip'] = getIPAddress();
        $arr['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $arr['date'] = date("Y-m-d H:i:s");       

        $query = "INSERT INTO users (date, ip_addr, fullname, username, email, password) VALUES(:date, :ip, :fullname, :username, :email, :password)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute($arr);

        $_SESSION['result_popup'] =
        "
            <script type=\"text/javascript\">
                swal({
                    title: \"Registered\",
                    type: \"success\",
                    text: \"You are registered, Please login.success\",
                    allowOutsideClick: false,
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                    });
            </script>                        
        ";

        header("Location: login.php");
        
        $_SESSION['username'] = $arr['username'];
        $_SESSION['userid'] = $con->lastInsertId();
        $_SESSION['logged_in'] = true;
        $_SESSION['profilepic'] = 'default_profile.jpg'; 
        $_SESSION['role'] = 0;
    }

    return $errors;
}

function login_user($data)
{
    global $con;
    $errors = array();

    // Field Errors

    $arr['username'] = $data['username'];  
    $arr['password'] = $data['password'];

    $query = "SELECT * FROM users WHERE (username = :username OR email = :username) limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute($arr);

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);

        if(password_verify($arr['password'], $row['password']))
        {
            $_SESSION['user'] = $row;
            $_SESSION['logged_in'] = true;

            $arr['ip'] = getIPAddress();
            $query = "UPDATE users SET ip_addr = :ip WHERE username = :username OR email = :username limit 1;";
            $update_stm = $con->prepare($query);
            $update_stm->execute($arr);

            if($row['email'] != $row['email_verified'] || is_null($row['email_verified'])) {
                $_SESSION['result_popup'] =
                        "
                            <script type=\"text/javascript\">
                                swal({
                                    title: \"Verification\",
                                    type: \"warning\",
                                    text: \"You are not verified yet, Please verify your account via 'Edit My Profile'. Check your inbox or your spam folder.\",
                                    allowOutsideClick: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK'
                                    });
                            </script>                        
                        ";

                sendVerificationCode();
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
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
    return $errors;
}

function sendVerificationCode()
{
    global $con;

    $errors = array();
    $now = time();

    $vars = array();

    $vars['code'] = rand(pow(10, 5-1), pow(10, 5)-1);
    $vars['email'] = $_SESSION['user']['email'];

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

function is_user_verified()
{
    global $con;
    $query = "SELECT * FROM users WHERE id = :id limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->bindValue(':id', $_SESSION['user']['id']);
    $select_stm->execute();

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user'] = $row;

        if($row['email'] == $row['email_verified'])
            return true;
    }

    return false;
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

    $username = $_SESSION['user']['username'];
    $password = $_POST['verifypassword'];        

    $query = "SELECT password FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->bindParam(':username', $username, PDO::PARAM_STR);
    $select_stm->execute();
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

    if(password_verify($password, $row['password']))
    { // if verify password matches the password
        // if a user uploaded a profile picture
        if(!password_verify($_POST['password'], $row['password']))
        {
            if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                uploadImages();

            $_SESSION['success_message'] = "Changes to your profile has been saved!";
        }
        else 
        {
            $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
        }
    }
    else 
    {
        $_SESSION['error_message'] = "Verify Password does not match with the User's Password!";
    }
    return $count;
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

        $arr['newImgName'] = $name . " - " . $imgName[0];
        $arr['newImgName'] .= '.' . $imgExt;

        $query = "UPDATE users SET image = :newImgName WHERE id = :id";
        $q = $con->prepare($query);
        $q->execute($arr);

        $tmp_path = "profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "profile_pictures/" .$arr['newImgName'];

        move_uploaded_file($tmpName, $tmp_path);
        resize_image($tmp_path, $real_path);

        unlink($tmp_path);

        $_SESSION['profilepic'] = $arr['newImgName'];

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

?>