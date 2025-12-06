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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["course_id"])) {
    $course_id = (int)$_POST["course_id"];
    
    // Check for dependencies
    $check = "SELECT 
        (SELECT COUNT(*) FROM Classes WHERE course_id = ?) AS class_count,
        (SELECT COUNT(*) FROM Prerequisites WHERE course_id = ? OR required_course_id = ?) AS prereq_count";
    
    $stmt = mysqli_prepare($link, $check);
    mysqli_stmt_bind_param($stmt, "iii", $course_id, $course_id, $course_id);
    mysqli_stmt_execute($stmt);
    
    // Use mysqli_stmt_get_result() instead of mysql_fetch_assoc()
    $result = mysqli_stmt_get_result($stmt);
    $counts = mysqli_fetch_assoc($result);
    
    if ($counts['class_count'] > 0 || $counts['prereq_count'] > 0) {
        $_SESSION["error"] = "Cannot delete - course has " . 
                            ($counts['class_count'] ? "{$counts['class_count']} classes" : "") .
                            ($counts['class_count'] && $counts['prereq_count'] ? " and " : "") .
                            ($counts['prereq_count'] ? "{$counts['prereq_count']} prerequisite relationships" : "");
    } else {
        $delete = "DELETE FROM Courses WHERE course_id = ?";
        $stmt = mysqli_prepare($link, $delete);
        mysqli_stmt_bind_param($stmt, "i", $course_id);
        mysqli_stmt_execute($stmt);
        $_SESSION["success"] = "Course deleted successfully";
    }
    mysqli_stmt_close($stmt);
}

header("Location: course.php");
exit;
?>