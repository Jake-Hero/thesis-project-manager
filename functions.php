<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "db.php";
require "mail.php";

define("ROLE_STUDENT", 0);
define("ROLE_PANELIST", 1);
define("ROLE_ADVISOR", 2);
define("ROLE_ADMIN", 3);

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
        $arr['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $arr['date'] = date("Y-m-d H:i:s");       

        $query = "INSERT INTO users (date, fullname, username, email, password) VALUES(:date, :fullname, :username, :email, :password)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute($arr);

        $_SESSION['result_popup'] = "
                    <script type=\"text/javascript\">
                        swal({
                            title: \"Successfully Registered!\",
                            type: \"success\",
                            text: \"A verification code is sent to your email! You have been registered in the system.\",
                            allowOutsideClick: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Redirect'
                            }).then(function() {
                                window.location.href = \"login.php\";
                            });
                    </script>
                ";
        

        $_SESSION['username'] = $arr['username'];
        $_SESSION['userid'] = $con->lastInsertId();
        $_SESSION['logged_in'] = true;
        $_SESSION['profilepic'] = 'default_profile.jpg'; 
        $_SESSION['role'] = 0;
        $_SESSION['sent_verification'] = false;
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
            $_SESSION['sent_verification'] = false;

            if($row['email'] != $row['email_verified'] || is_null($row['email_verified'])) {
                $_SESSION['result'] =
                        "
                            <script type=\"text/javascript\">
                                swal({
                                    title: \"Verification\",
                                    type: \"warning\",
                                    text: \"You are not verified yet, Please verify your account. Check your inbox or your spam folder.\",
                                    allowOutsideClick: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK'
                                    });
                            </script>                        
                        ";

                sendVerificationCode();
                header("Location: verify.php");
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
        $vars['expiry'] = time() + (60 * 1); // 5 minutes expiration

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

    $errors = array();

    $username = $_SESSION['username'];
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

            $errors['success'] = "Changes to your profile has been saved!";
        }
        else 
        {
            $errors['all'] = "Your new password cannot be the same as your old one!";
        }
    }
    else 
    {
        $errors['all'] = "Verify Password does not match with the User's Password!";
    }
    return $errors;
}

function uploadImages()
{
    $arr = array();

    $arr['id'] = $_SESSION['userid'];
    $name = $_SESSION['username'];

    $imgName = $_FILES['name'];
    $imgSize = $_FILES['size'];
    $tmpName = $_FILES['tmp_name'];

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
        
        move_uploaded_file($tmpName, 'profile_pictures/' . $arr['newImgName']);
        $_SESSION['profilepic'] = $arr['newImgName'];

        header("Refresh:0");
    }
    return true;
}

?>