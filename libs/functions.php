<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

require_once realpath(dirname(__FILE__) . '/../config/db.php');
require_once realpath(dirname(__FILE__) . '/../config/mail.php');
require_once "mobile_detection.php";
require_once "browser_detection.php";

// Department (Archive)
define("DEPT_IT", 1);
define("DEPT_CS", 2);

// Roles
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

function isPasswordStrong($password)
{
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w\_]@', $password);

    return ($uppercase && $lowercase && $number && $specialChars);
}

function signup_user($data)
{
    global $con;
    $errors = array();

    // Field Errors
    if(!preg_match('/^[a-zA-Z \.]+$/', $data['fullname']))
        $errors['fullname'] = "Enter a valid full name!";
    if(!preg_match('/^[a-zA-Z0-9\_\.\-]+$/', $data['username']))
        $errors['username'] = "Enter a valid username!";
    if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = "Enter a valid email address!";
    if(strlen(trim($data['password'])) < 8)
        $errors['password'] = "Your password must be longer than 4 characters.";
    if(!isPasswordStrong($data['password'])) 
        $errors['password'] = "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";

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
        $userid = $con->lastInsertId();

        $_SESSION['username'] = $data['username'];
        $_SESSION['userid'] = $userid;
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

        $query = "INSERT INTO grades (userid) VALUES(:id)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute(['id' => $userid]);

        $vars['code'] = rand(pow(10, 5-1), pow(10, 5)-1);
        $vars['email'] = $email;
        $vars['expiry'] = time() + (60 * 5); // 5 minutes expiration
    
        $query = "INSERT INTO verified (code, expiry, email) VALUES(:code, :expiry, :email)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute($vars);

        $message = "Welcome to Thesis & Capstone Manager!";
        $message.= "\r\n\n";
        $message.= "You have just recently signed up, to get you started please verify your account!";
        $message.= "\r\n\n";
        $message.= "Your verification code is: ". $vars['code'];
        $message.= "\r\n\n";
        $message.= "You may type this verification code after logging in, via 'Edit My Profile'."; 
        $message.= "\r\nThis code will expire and is only valid for <strong>5 minutes</strong>";
        $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
        $message.= "\r\nThis message is automated, Please do not reply to this email.";
        $message = nl2br($message);
        
        send_mail($email, "Welcome to Thesis & Capstone Manager", $message);

        header("Location: ./login.php");
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

                header("Location: ./dashboard.php");
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

            unset($_SESSION['error_message']);
            $_SESSION['message'] = "A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.";
            $message = "Your verification code is: ". $vars['code'];
            $message.= "\r\nThis code will expire and is only valid for <strong>5 minutes</strong>";
            $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
            $message.= "\r\nThis message is automated, Please do not reply to this email.";
            $message = nl2br($message);
            
            send_mail($vars['email'], "Verify your account! - Verification Code", $message);                  
        }
        else 
        {
            unset($_SESSION['message']);
            $timeLapse = ($row['expiry'] - $now) / 60 % 60;
            $_SESSION['error_message'] = "A code was already sent to your email. Please wait for <strong>" .$timeLapse. " minutes</strong> before requesting for a new code.";
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
        $message.= "\r\nThis code will expire and is only valid for <strong>5 minutes</strong>";
        $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
        $message.= "\r\nThis message is automated, Please do not reply to this email.";
        $message = nl2br($message);

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
            header("Location: ./logout.php");
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
    if(!(isset($_SESSION['user']) && isset($_SESSION['logged_in']))) 
    {
        if($redirect)
        {
            header("Location: ./index.php");
            die;
        }
        else
        {
            return false;
        }
    }
    return true;
}

function profileSave()
{
    global $con;

    $hashed_password = NULL;
    $username = $_SESSION['user']['username'];
    $password = $_POST['verifypassword'];        

    $arr = array();
                
    $arr['id'] = $_SESSION['user']['id'];
    $name = $_SESSION['user']['username'];

    $imgName = $_FILES['image']['name'];
    $imgSize = $_FILES['image']['size'];
    $tmpName = $_FILES['image']['tmp_name'];

    $imgExt = explode('.', $imgName);
    $imgExt = strtolower(end($imgExt));

    $query = "SELECT id, email, password FROM users WHERE username = :username limit 1;";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['username' => $username]);
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

    if(password_verify($password, $row['password']))
    { // if verify password matches the password
        if(!empty($_POST['email']) > 0 && $_POST['email'] == $row['email'])
        {
            $_SESSION['error_message'] = "Your new email cannot be the same as your old one!";
        }        
        else if(!empty($_POST['password']) && password_verify($_POST['password'], $row['password']))
        {
            $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
        }
        else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z \.]+$/', $_POST['fullname']))
        {
            $_SESSION['error_message'] = "Enter a valid full name!";
        }
        else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9\_\.\-]+$/', $_POST['username']))
        {
            $_SESSION['error_message'] = "Enter a valid username!";
        }
        else if(!empty($_POST['password']) && !isPasswordStrong($_POST['password'])) 
        {
            $_SESSION['error_message'] = "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";
        }
        else if ($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['type'] != "image/jpg" && $_FILES['image']['type'] != "image/jpeg" && $_FILES['image']['type'] != "image/png")
        {
            $_SESSION['error_message'] = "You can only upload (jpg, jpeg, png is only allowed!) files!";
        }
        else if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $imgSize > 5242880)
        {
            $_SESSION['error_message'] = "You cannot upload an image with over 5MB+ file size!";
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

                    $message = "Your password was changed.";
                    $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who made this changes.";
                    $message.= "\r\nOtherwise, <strong>contact the Site Administrator immediately</strong>!";
                    $message.= "\r\nThis message is automated, Please do not reply to this email.";

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
            
                if($_FILES['image']['error'] != UPLOAD_ERR_NO_FILE)
                {    
                    $imgName = explode('.', $imgName);
            
                    $arr['newImgName'] = $name . " - " . date("m.d.y H.i.s");
                    $arr['newImgName'] .= '.' . $imgExt;
            
                    $query = "UPDATE users SET image = :newImgName WHERE id = :id";
                    $q = $con->prepare($query);
                    $q->execute($arr);
            
                    $tmp_path = "./assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
                    $real_path = "./assets/profile_pictures/" .$arr['newImgName'];
            
                    move_uploaded_file($tmpName, $tmp_path);
                    resize_image($tmp_path, $real_path);
            
                    unlink($tmp_path);
                }

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

function categorizedDepartment($val)
{
    switch($val)
    {
        case DEPT_IT:
            $dept = 'Information Technology';
            break;
        case DEPT_CS:
            $dept = 'Computer Science';
            break;                    
        default:
            $dept = 'Unspecified';
            break;
    }
    return $dept;
}

function createArchive()
{
    global $con;
    $query = "SELECT * FROM archives WHERE title = :title";
    $selectStmt = $con->prepare($query);
    $selectStmt->execute(['title' => $_POST['title']]);

    if(empty($_POST['title']))
    {
        $_SESSION['error_message'] = "Make sure to fill out all the fields!";
    }
    else if(!empty($_POST['title']) && !preg_match('/[a-zA-Z \']*\S[a-zA-Z \']/', $_POST['title']))
    {
        $_SESSION['error_message'] = "Enter a valid research title name!";
    }
    else if($selectStmt->rowCount() > 0)
    {
        $_SESSION['error_message'] = "Title is already taken!";
    }
    else 
    {
        if($_FILES['document']['error'] != UPLOAD_ERR_NO_FILE)
        {
            $arr = array();
        
            $fileName = $_FILES['document']['name'];
            $fileSize = $_FILES['document']['size'];
            $tmpName = $_FILES['document']['tmp_name'];
        
            $fileExt = explode('.', $fileName);
            $fileExt = strtolower(end($fileExt));
        
            if ($_FILES['document']['type'] != "application/pdf")
            {
                $_SESSION['error_message'] = "You can only upload PDF files!";
            }
            else if($fileSize > 15728640)
            {
                $_SESSION['error_message'] = "File size is too large!";
            }
            else 
            {
                $fileName = $_POST['title'];
                $fileName .= '.' . $fileExt;
        
                $path = "./assets/archives/" .$fileName;
        
                move_uploaded_file($tmpName, $path);

                $query = "INSERT INTO archives (date, title, file, publishedyear, department) VALUES(:added, :title, :file, :year, :department)";
                $insert_stm = $con->prepare($query);
                $insert_stm->bindValue('added', date("Y-m-d H:i:s")); 
                $insert_stm->bindValue('title', $_POST['title']);
                $insert_stm->bindValue('file', $fileName);
                $insert_stm->bindValue('year', $_POST['year']);
                $insert_stm->bindValue('department', $_POST['department']);
                $insert_stm->execute();

                $_SESSION['success_message'] = "You have successfully added a new archived document!";

                header("Location: ./archive.php");
            }
        }    
        else 
        {
            $_SESSION['error_message'] = "An error occured while trying to upload the file!";
        }
    }
    return true;
}

function createUserProfile()
{
    global $con;

    $hashed_password = NULL;
    $image_name = "default_profile.jpg";

    if(empty($_POST['fullname']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']))
    {
        $_SESSION['error_message'] = "Make sure to fill out all the fields!";
    }
    else if($_POST['role'] < 0)
    {
        $_SESSION['error_message'] = "Assign a role!";
    }
    else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z \.]+$/', $_POST['fullname']))
    {
        $_SESSION['error_message'] = "Enter a valid full name!";
    }
    else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9\_\.\-]+$/', $_POST['username']))
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
        
            header("Location: ../admin/members.php?page=1&search=" . $_POST['email']);
        }
    }
    return true;
}

function getGradeConversion($type, $score)
{
    switch($type)
    {
        case 1: // Pre Oral 
            $table = array(
                30 => 100,
                29 => 99,
                28 => 97,
                27 => 95,
                26 => 94,
                25 => 92,
                24 => 90,
                23 => 89,
                22 => 87,
                21 => 85,
                20 => 84,
                19 => 82,
                18 => 80,
                17 => 79,
                16 => 77,
                15 => 75,
                14 => 74,
                13 => 73,
                12 => 72,
                11 => 71,
                10 => 70,
                9 => 69,
                8 => 68,
                7 => 67,
                6 => 66,
                5 => 65,
                4 => 64,
                3 => 63,
                2 => 62,
                1 => 61,
                0 => 60
              );
            break;
        default: // Oral
            $table = array(
                35 => 100,
                34 => 99,
                33 => 98,
                32 => 96,
                31 => 94,
                30 => 92,
                29 => 91,
                28 => 90,
                27 => 89,
                26 => 88,
                25 => 86,
                24 => 84,
                23 => 82,
                22 => 81,
                21 => 80,
                20 => 79,
                19 => 78,
                18 => 76,
                17 => 74,
                16 => 73,
                15 => 72,
                14 => 71,
                13 => 70,
                12 => 69,
                11 => 68,
                10 => 67,
                9 => 66,
                8 => 65,
                7 => 64,
                6 => 63,
                5 => 63,
                4 => 62,
                3 => 62,
                2 => 61,
                1 => 61,
                0 => 60
            );
            break;
    }

    return $table[$score];
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
        echo '<script>alert("Invalid file extension (jpg, jpeg, png is only allowed!)");';
    }
    else if($imgSize > 1200000)
    {
        echo '<script>alert("File size is too large!");</script>';
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
        header("Location: ./admin/members.php?page=1");
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

        if(!empty($_POST['password']) && !isPasswordStrong($_POST['password'])) 
        {
            $_SESSION['error_message'] = "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";
        }
        else if(!empty($_POST['email']) && $_POST['email'] == $row['email'])
        {
            $_SESSION['error_message'] = "Your new email cannot be the same as your old one!";
        }        
        else if(!empty($_POST['password']) && password_verify($_POST['password'], $row['password']))
        {
            $_SESSION['error_message'] = "Your new password cannot be the same as your old one!";
        }
        else if(!empty($_POST['fullname']) && !preg_match('/^[a-zA-Z \.]+$/', $_POST['fullname']))
        {
            $_SESSION['error_message'] = "Enter a valid full name!";
        }
        else if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9\.\-]+$/', $_POST['username']))
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

                    $message = "Your password was changed.";
                    $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who made this changes.";
                    $message.= "\r\nOtherwise, <strong>contact the Site Administrator immediately</strong>!";
                    $message.= "\r\nThis message is automated, Please do not reply to this email.";

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
            
                $message = "Your account has been modified by ". $_SESSION['user']['fullname'];
                $message.= "\r\n\nPlease ignore this E-Mail, this is just to notify you with the changes made in your account.";
                $message.= "\r\nThis message is automated, Please do not reply to this email.";
                $message = nl2br($message);
                
                send_mail($row['email_verified'], "Account Modified", $message);   

                $query = "SELECT * FROM users WHERE username = :username limit 1;";
                $select_stm = $con->prepare($query);
                $select_stm->execute(['username' => $username]);
                $row = $select_stm->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
    else 
    {
        header("Location: ./admin/members.php?page=1");
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

    $imgExt = explode('.', $imgName);
    $imgExt = strtolower(end($imgExt));

    if ($_FILES['image']['type'] != "image/jpg" && $_FILES['image']['type'] != "image/jpeg" && $_FILES['image']['type'] != "image/png")
    {
        echo '<script>alert("You can only upload (jpg, jpeg, png is only allowed!) files!");';
    }
    else if($imgSize > 5242880)
    {
        echo '<script>alert("You cannot upload an image with over 5MB+ file size!");';
    }
    else 
    {
        $imgName = explode('.', $imgName);

        $arr['newImgName'] = $name . " - " . date("m.d.y H.i.s");
        $arr['newImgName'] .= '.' . $imgExt;

        $query = "UPDATE users SET image = :newImgName WHERE id = :id";
        $q = $con->prepare($query);
        $q->execute($arr);

        $tmp_path = "./assets/profile_pictures/tmp_" . $_FILES['image']['name'] . "." .$imgExt;
        $real_path = "./assets/profile_pictures/" .$arr['newImgName'];

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
                $query = "SELECT id, email_verified, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
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

                $query = "SELECT id, email_verified, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
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
                $query = "SELECT id, email_verified, group_id FROM users WHERE id = :user_id OR fullname = :fullname OR username = :username";
                $deleteMmbrStmt = $con->prepare($query);
                $deleteMmbrStmt->bindValue('user_id', $_POST['remove_member'], PDO::PARAM_INT);
                $deleteMmbrStmt->bindValue('fullname', $_POST['remove_member']);
                $deleteMmbrStmt->bindValue('username', $_POST['remove_member']);
                $deleteMmbrStmt->execute();

                if($deleteMmbrStmt->rowCount() > 0)
                    $delete_row = $deleteMmbrStmt->fetch();
            }

            if(isset($deleteMmbrStmt) && $deleteMmbrStmt->rowCount() < 1 || isset($selectStmt) && $selectStmt->rowCount() < 1)
            {
                $_SESSION['error_message'] = "That user doesn't exist!";
            }
            else if(isset($_POST['add_member']) && (isset($count) && $count >= 5))
            {
                $_SESSION['error_message'] = "This thesis group can only have a maximum of five members!";
            }
            else if((isset($_POST['group_leader']) && (isset($leader_row) && $leader_row['group_id'] > 0)) || (isset($_POST['add_member']) && (isset($add_row) && $add_row['group_id'] > 0)))
            {
                $_SESSION['error_message'] = "That user is already a leader or member of a thesis group!";
            }
            else if(isset($_POST['remove_member']) && (isset($delete_row) && $delete_row['group_id'] != $id))
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
                    $query = "UPDATE users SET group_id = :id, advised_by = :adviser WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
                    $updateStmt->bindValue('adviser', $row['created_by'], PDO::PARAM_INT);
                    $updateStmt->bindValue('user_id', $leader_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    $message = "You have been made the Leader of the group ". $row['group_title'] . ' by ' . $_SESSION['user']['fullname'];
                    $message.= "\r\n\nPlease ignore this E-Mail, this is just to notify you with the changes made in your account.";
                    $message.= "\r\nThis message is automated, Please do not reply to this email.";
                    $message = nl2br($message);
                    
                    send_mail($leader_row['email_verified'], "Made a Leader of a Group!", $message);   


                    log_group($id, $_SESSION['user']['fullname'] . " has given the leader status to " . getFullName($leader_row['id']));
                }
                if(!empty($_POST['add_member']))
                {
                    $query = "UPDATE users SET group_id = :id, advised_by = :adviser WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('id', $id, PDO::PARAM_INT);
                    $updateStmt->bindValue('adviser', $row['created_by'], PDO::PARAM_INT);
                    $updateStmt->bindValue('user_id', $add_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    $message = "You have been added to the group ". $row['group_title'] . ' by ' . $_SESSION['user']['fullname'];
                    $message.= "\r\n\nPlease ignore this E-Mail, this is just to notify you with the changes made in your account.";
                    $message.= "\r\nThis message is automated, Please do not reply to this email.";
                    $message = nl2br($message);
                    
                    send_mail($add_row['email_verified'], "Added to the Group", $message);   

                    log_group($id, $_SESSION['user']['fullname'] . " has added " . getFullName($add_row['id']) . " to the group.");
                }

                if(!empty($_POST['remove_member']))
                {
                    $query = "UPDATE users SET group_id = 0, advised_by = 0 WHERE id = :user_id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->bindValue('user_id', $delete_row['id'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    if($row['group_leader'] == $delete_row['id'])
                    {
                        $query = "UPDATE groups SET group_leader = 0 WHERE groupid = :id";
                        $updateStmt = $con->prepare($query);
                        $updateStmt->bindValue('id', $row['groupid'], PDO::PARAM_INT);
                        $updateStmt->execute();
                    }

                    $message = "You have been removed from the group ". $row['group_title'] . ' by ' . $_SESSION['user']['fullname'];
                    $message.= "\r\n\nPlease ignore this E-Mail, this is just to notify you with the changes made in your account.";
                    $message.= "\r\nThis message is automated, Please do not reply to this email.";
                    $message = nl2br($message);
                    
                    send_mail($delete_row['email_verified'], "Removed from the Group", $message);   

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
        header("Location: ./admin/group.php?page=1");
    }
    return $row;
}

?>