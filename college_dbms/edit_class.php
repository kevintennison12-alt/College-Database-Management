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


$class_id = $_GET['id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $course_id = sanitizeInput($_POST["course_id"], $link);
    $faculty_id = sanitizeInput($_POST["faculty_id"], $link);
    $semester = sanitizeInput($_POST["semester"], $link);
    $year = sanitizeInput($_POST["year"], $link);
    $room_number = sanitizeInput($_POST["room_number"], $link);
    $schedule = sanitizeInput($_POST["schedule"], $link);
    $max_capacity = sanitizeInput($_POST["max_capacity"], $link);

    // Update class details
    $sql = "UPDATE Classes SET course_id = ?, faculty_id = ?, semester = ?, year = ?, 
            room_number = ?, schedule = ?, max_capacity = ? WHERE class_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iissssii", $course_id, $faculty_id, $semester, $year,
                               $room_number, $schedule, $max_capacity, $class_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: classes.php?success=Class updated successfully");
            exit();
        } else {
            echo "Error updating class: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch existing class details for pre-filling the form
$sql = "SELECT * FROM Classes WHERE class_id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $class_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $class = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>

<!-- Add the same form structure here for editing -->
