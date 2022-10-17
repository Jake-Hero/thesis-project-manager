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

function getWeekday($date) {
    return date('l', strtotime($date));
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
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);

        $query = "INSERT INTO users (date, ip_addr, fullname, username, email, password) VALUES(:creation, :ip, :fullname, :username, :email, :pass)";
        $insert_stm = $con->prepare($query);
        $insert_stm->bindValue('creation', date("Y-m-d H:i:s")); 
        $insert_stm->bindValue('ip', getIPAddress());
        $insert_stm->bindValue('fullname', $data['fullname']); 
        $insert_stm->bindValue('username', $data['username']); 
        $insert_stm->bindValue('email', $email); 
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

        header("Location: " . ROOT_FOLDER . "/login.php");
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

                header("Location: " . ROOT_FOLDER . "/dashboard.php");
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
            header("Location: " . ROOT_FOLDER . "/logout.php");
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
        header("Location: " . ROOT_FOLDER . "/index.php");
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
                if(!empty($_POST['fullname'])) 
                {
                    $fullname = $_POST['fullname'];       
                    
                    $query = "SELECT posted_by FROM comments WHERE posted_by = :name";
                    $selectStmt = $con->prepare($query);
                    $selectStmt->bindParam('name', $_SESSION['user']['fullname']);
                    $selectStmt->execute();

                    if($selectStmt->rowCount() > 0)
                    {
                        $result = $selectStmt->fetch();

                        foreach($result as $row)
                        {
                            $query = "UPDATE comments SET posted_by = :new_name WHERE posted_by = :old_name";
                            $selectStmt = $con->prepare($query);
                            $selectStmt->bindParam('new_name', $fullname);
                            $selectStmt->bindParam('old_name', $_SESSION['user']['fullname']);
                            $selectStmt->execute();                            
                        }
                    }
                }

                $username = NULL;
                if(!empty($_POST['username'])) $username = $_POST['username'];
                $email = NULL;
                if(!empty($_POST['email'])) $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

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
            
                header("Refresh:0");
            }
        }
    }
    else 
    {
        $_SESSION['error_message'] = "Verify Password does not match with the User's Password!";
    }
    return true;
}

function createUserProfile()
{
    global $con;

    $hashed_password = NULL;
    $image_name = "default_profile.jpg";

    if(empty($_POST['fullname']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']))
        $_SESSION['error_message'] = "Make sure to fill out all the fields!";

    if($_POST['role'] < 0)
        $_SESSION['error_message'] = "Assign a role!";

    if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z ]+$/', $_POST['fullname']))
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
            $_SESSION['error_message'] = "Choose another username, A user is already using that username!";
        }
        else if(!empty($_POST['email']) && $existing_email)
        {
            $_SESSION['error_message'] = "Choose another email, A user is already using that email!";
        }
        else 
        {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                $image_name = uploadnewImages($_POST['username']);

            $query = "INSERT INTO users (date, ip_addr, fullname, username, email, password, image, role) VALUES(:creation, :ip, :fullname, :username, :email, :pass, :img, :role)";
            $insert_stm = $con->prepare($query);
            $insert_stm->bindValue('creation', date("Y-m-d H:i:s")); 
            $insert_stm->bindValue('ip', getIPAddress());
            $insert_stm->bindValue('fullname', $_POST['fullname']); 
            $insert_stm->bindValue('username', $_POST['username']); 
            $insert_stm->bindValue('email', $_POST['email']); 
            $insert_stm->bindValue('pass', $hashed_password);
            $insert_stm->bindValue('img', $image_name);
            $insert_stm->bindValue('role', $_POST['role']);
            $insert_stm->execute();
        
            header("Location: " . ROOT_FOLDER . "/admin/members.php?page=1&search=" . $_POST['email']);
        }
    }
    return true;
}

function uploadnewImages($name)
{
    global $con;
    $arr = array();

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

        $imgName = $name . " - " . date("m.d.y H.i.s");
        $imgName .= '.' . $imgExt;

        $tmp_path = "../assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "../assets/profile_pictures/" .$imgName;

        move_uploaded_file($tmpName, $tmp_path);
        resize_image($tmp_path, $real_path);

        unlink($tmp_path);

        header("Refresh:0");
    }
    return $imgName;
}

function adminEditTask($id)
{
    global $con;

    $query = "SELECT * FROM tasks WHERE taskid = :id limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['id' => $id]);

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);

        if(!empty($_POST['task_end']) && $_POST['task_end'] <= $row['taskstart']) {
            $_SESSION['error_message'] = "Your end date cannot conflict with start date!";
        } else {
            $assigned = $row['taskassignedto'];
            if(!empty($_POST['assigned_to']))
            {
                $assigned = $_POST['assigned_to'];
                log_group($id, $_SESSION['user']['fullname'] . " has assigned " . getFullName($_POST['assigned_to']) . " to " . $row['tasktitle']);
            }
            $status = $row['taskstatus'];
            if(!empty($_POST['task_status']))
            {
                $status = $_POST['task_status'];
                switch($_POST['task_status']) {
                    case 0:
                        log_group($id, $_SESSION['user']['fullname'] . " has changed task status to Incomplete");
                        break;
                    case 1:
                        log_group($id, $_SESSION['user']['fullname'] . " has changed task status to Pending");
                        break;
                    case 2:
                        log_group($id, $_SESSION['user']['fullname'] . " has changed task status to Complete");
                        break; 
                }
            }
            $title = $row['tasktitle'];
            if(!empty($_POST['task_title']))
            {
                $title = $_POST['task_title'];
                log_group($id, $_SESSION['user']['fullname'] . " has changed task title to " . $_POST['task_title']);
            }
            $detail = $row['taskdetail'];
            if(!empty($_POST['task_detail']))
            {
                $detail = $_POST['task_detail'];
                log_group($id, $_SESSION['user']['fullname'] . " has changed the task detail.");
            }

            $duedate = $row['taskdue'];

            if(!empty($_POST['task_end']))
            {
                $duedate = $_POST['task_end'];
                log_group($id, $_SESSION['user']['fullname'] . " has changed the due date to " . $_POST['task_end']);
            }

            $query = "UPDATE tasks SET 
                        tasktitle = COALESCE(:title, tasktitle),
                        taskdetail = COALESCE(:detail, taskdetail),
                        taskdue = COALESCE(:duedate, taskdue),
                        taskassignedto = COALESCE(:assigned, taskassignedto),
                        taskstatus = COALESCE(:status, taskstatus)
                        WHERE taskid = :id";

            $updateStmt = $con->prepare($query);
            $updateStmt->bindValue('title', $title, PDO::PARAM_STR);
            $updateStmt->bindValue('detail', $detail, PDO::PARAM_STR);
            $updateStmt->bindValue('duedate', $duedate, PDO::PARAM_STR);
            $updateStmt->bindValue('assigned', $assigned, PDO::PARAM_INT);
            $updateStmt->bindValue('status', $status, PDO::PARAM_INT);
            $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
            $updateStmt->execute();
        
            header("Refresh:0");
            $_SESSION['success_message'] = "Changes to this task has been saved!";
        
            $query = "SELECT * FROM tasks WHERE taskid = :id limit 1;";
            $select_stm = $con->prepare($query);
            $select_stm->execute(['id' => $id]);
            $row = $select_stm->fetch(PDO::FETCH_ASSOC);
        }
    }
    else 
    {
        header("Location: " . ROOT_FOLDER . "/admin/members.php?page=1");
    }
    return $row;
}

function adminEditProfile($str)
{
    global $con;

    $hashed_password = NULL;
    $username = $str;     

    $query = "SELECT * FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['username' => $username]);

    if($select_stm->rowCount() > 0)
    {
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
                $_SESSION['error_message'] = "Choose another username, A user is already using that username!";
            }
            else if(!empty($_POST['email']) && $existing_email)
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
                if(!empty($_POST['fullname'])) 
                {
                    $fullname = $_POST['fullname'];
                
                    $query = "SELECT posted_by FROM comments WHERE posted_by = :name";
                    $selectStmt = $con->prepare($query);
                    $selectStmt->bindParam('name', $_SESSION['user']['fullname']);
                    $selectStmt->execute();

                    if($selectStmt->rowCount() > 0)
                    {
                        $result = $selectStmt->fetch();

                        foreach($result as $row)
                        {
                            $query = "UPDATE comments SET posted_by = :new_name WHERE posted_by = :old_name";
                            $selectStmt = $con->prepare($query);
                            $selectStmt->bindParam('new_name', $fullname);
                            $selectStmt->bindParam('old_name', $_SESSION['user']['fullname']);
                            $selectStmt->execute();                            
                        }
                    }
                }
                    
                $username = NULL;
                if(!empty($_POST['username'])) $username = $_POST['username'];
                $email = NULL;
                if(!empty($_POST['email'])) $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
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
                $updateStmt->bindValue('fullname', $fullname, PDO::PARAM_STR);
                $updateStmt->bindValue('username', $username, PDO::PARAM_STR);
                $updateStmt->bindValue('email', $email, PDO::PARAM_STR);
                $updateStmt->bindValue('password', $hashed_password, PDO::PARAM_STR);
                $updateStmt->bindValue('role', $role, PDO::PARAM_STR);
                $updateStmt->bindValue('id', $row['id'], PDO::PARAM_INT);
                $updateStmt->execute();
            
                header("Refresh:0");
                $_SESSION['success_message'] = "Changes to this profile has been saved!";
            
                $query = "SELECT * FROM users WHERE username = :username limit 1;";
                $select_stm = $con->prepare($query);
                $select_stm->execute(['username' => $username]);
                $row = $select_stm->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
    else 
    {
        header("Location: " . ROOT_FOLDER . "/admin/members.php?page=1");
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

        $tmp_path = "../assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "../assets/profile_pictures/" .$arr['newImgName'];

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

function log_group($groupid, $details)
{
    global $con;
   
    $query = "SELECT COUNT(*) FROM groups WHERE groupid = :id";
    $selectStmt = $con->prepare($query);
    $selectStmt->bindValue('id', $groupid, PDO::PARAM_INT);
    $selectStmt->execute();

    if($selectStmt->fetchColumn() > 0)
    {
        $query = "INSERT INTO group_logs (groupid, log_date, log_details) VALUES(:id, :date, :details)";
        $selectStmt = $con->prepare($query);
        $selectStmt->bindValue('id', $groupid, PDO::PARAM_INT);
        $selectStmt->bindValue('date', date("Y-m-d H:i:s"));
        $selectStmt->bindValue('details', $details);
        $selectStmt->execute();        
    }
    return true;
}

function adminEditGroup($id)
{
    global $con; 

    $query = "SELECT * FROM groups WHERE groupid = :id limit 1;";
    $selectStmt = $con->prepare($query);
    $selectStmt->execute(['id' => $id]);

    if($selectStmt->rowCount() > 0)
    {
        $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if((!empty($_POST['add_member']) && !empty($_POST['remove_member'])) && $_POST['add_member'] == $_POST['remove_member'])
        {
            $_SESSION['error_message'] = "You cannot add and remove the same member at the same time!";
        }
        else 
        {
            if(!empty($_POST['group_leader']))
            {
                $query = "SELECT id, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
                $selectStmt = $con->prepare($query);
                $selectStmt->bindValue('user_id', $_POST['group_leader'], PDO::PARAM_INT);
                $selectStmt->bindValue('fullname', $_POST['group_leader']);
                $selectStmt->bindValue('username', $_POST['group_leader']);
                $selectStmt->execute();
            
                if($selectStmt->rowCount() > 0)
                    $leader_row = $selectStmt->fetch();            
            }
            if(!empty($_POST['add_member']))
            {
                $query = "SELECT COUNT(*) FROM users WHERE group_id = :id";
                $countStmt = $con->prepare($query);
                $countStmt->bindValue('id', $id, PDO::PARAM_INT);
                $countStmt->execute();

                $count = $countStmt->fetchColumn();

                $query = "SELECT id, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
                $selectStmt = $con->prepare($query);
                $selectStmt->bindValue('user_id', $_POST['add_member'], PDO::PARAM_INT);
                $selectStmt->bindValue('fullname', $_POST['add_member']);
                $selectStmt->bindValue('username', $_POST['add_member']);
                $selectStmt->execute();

                if($selectStmt->rowCount() > 0)
                    $add_row = $selectStmt->fetch();
            }

            if(!empty($_POST['remove_member']))
            {
                $query = "SELECT id, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
                $deleteMmbrStmt = $con->prepare($query);
                $deleteMmbrStmt->bindValue('user_id', $_POST['remove_member'], PDO::PARAM_INT);
                $deleteMmbrStmt->bindValue('fullname', $_POST['remove_member']);
                $deleteMmbrStmt->bindValue('username', $_POST['remove_member']);
                $deleteMmbrStmt->execute();

                if($deleteMmbrStmt->rowCount() > 0)
                    $delete_row = $deleteMmbrStmt->fetch();
            }

            if(!empty($_POST['add_member']) && $count >= 5)
            {
                $_SESSION['error_message'] = "This thesis group can only have a maximum of five members!";
            }
            else if((!empty($_POST['group_leader']) && $leader_row['group_id'] > 0) || (!empty($_POST['add_member']) && $add_row['group_id'] > 0))
            {
                $_SESSION['error_message'] = "That user is already a leader or member of a thesis group!";
            }
            else if(!empty($_POST['remove_member']) && $delete_row['group_id'] != $id)
            {
                $_SESSION['error_message'] = "That user is not a member of this group!";
            }
            else 
            {
                $group_title = NULL;
                if(!empty($_POST['group_title'])) 
                {
                    $group_title = $_POST['group_title'];     
                    log_group($id, $_SESSION['user']['fullname'] . " has changed the thesis title.");
                }
                    
                $leader = NULL;
                if(!empty($_POST['group_leader'])) $leader = $leader_row['id'];
                
                if(!empty($_POST['group_leader']))
                {
                    $query = "UPDATE users SET group_id = :id WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
                    $updateStmt->bindValue('user_id', $leader_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    log_group($id, $_SESSION['user']['fullname'] . " has given the leader status to " . getFullName($leader_row['id']));
                }
                if(!empty($_POST['add_member']))
                {
                    $query = "UPDATE users SET group_id = :id WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
                    $updateStmt->bindValue('user_id', $add_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    log_group($id, $_SESSION['user']['fullname'] . " has added " . getFullName($add_row['id']) . " to the group.");
                }

                if(!empty($_POST['remove_member']))
                {
                    $query = "UPDATE users SET group_id = 0 WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('user_id', $delete_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    if($row['group_leader'] == $delete_row['id'])
                    {
                        $query = "UPDATE users SET group_leader = 0 WHERE id = :user_id";
                        $updateStmt = $con->prepare($query);
                        $updateStmt->bindValue('user_id', $delete_row['id'], PDO::PARAM_INT);
                        $updateStmt->execute();
                    }

                    log_group($id, $_SESSION['user']['fullname'] . " has removed " . getFullName($delete_row['id']) . " from the group.");
                }

                $query = "UPDATE groups SET 
                            group_leader = COALESCE(:leader, group_leader),
                            group_title = COALESCE(:title, group_title) 
                            WHERE groupid = :id";

                $updateStmt = $con->prepare($query);
                $updateStmt->bindValue('leader', $leader, PDO::PARAM_INT);
                $updateStmt->bindValue('title', $group_title, PDO::PARAM_STR);
                $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
                $updateStmt->execute();
            
                $_SESSION['success_message'] = "Changes to the group has been saved!";
            
                $query = "SELECT * FROM groups WHERE groupid = :id limit 1;";
                $selectStmt = $con->prepare($query);
                $selectStmt->execute(['id' => $id]);
                $row = $selectStmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
    else 
    {
        header("Location: " . ROOT_FOLDER . "/admin/group.php?page=1");
    }
    return $row;
}

?>