<?php
require_once "config.php"; // starts session + DB connection

/* ----------------- LOGIN CHECK FUNCTION ----------------- */
if (!function_exists('requireLogin')) {
    function requireLogin() {
        // Check if user is logged in
        if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header("Location: login.php");
            exit();
        }
    }
}

/* ----------------- SANITIZE FUNCTION ----------------- */
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data, $link) {
        $data = trim($data);
        $data = stripslashes($data);
        return mysqli_real_escape_string($link, $data);
    }
}

/* ------------------------------------------------------- */

requireLogin();

// Initialize variables
$student_id = $first_name = $last_name = $email = $phone = $enrollment_date = $major_department_id = "";
$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['class_id'])) {
        $class_id = sanitizeInput($_POST['class_id'], $link);

        // Delete the class from the database
        $sql = "DELETE FROM Classes WHERE class_id = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $class_id);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: classes.php?success=Class deleted successfully");
                exit();
            } else {
                echo "Error deleting class: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
?>
