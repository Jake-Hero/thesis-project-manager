<?php
    /* localhost */
    $db_host = 'localhost';
	$db_username = 'root';
	$db_password = '';
    $db_name = 'thesis-manager';

    /* live /
    $db_host = 'localhost';
	$db_username = 'u811891609_root';
	$db_password = 'Thesismanager7415';
    $db_name = 'u811891609_thesismanager';*/

    try {
        $con = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_username, $db_password);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die;
    }
?>