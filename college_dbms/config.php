<?php
// config.php - Database configuration with error handling
session_start();

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); 
define('DB_NAME', 'college_dbms');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$link) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
