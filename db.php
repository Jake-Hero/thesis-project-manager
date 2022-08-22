<?php
    $db_host = 'localhost';
	$db_username = 'root';
	$db_password = '';
    $db_name = 'thesis-manager';

    try {
        $con = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_username, $db_password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die;
    }
?>