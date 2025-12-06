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

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $class_id = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
    $enrollment_date = $_POST['enrollment_date'] ?? date('Y-m-d');
    $grade = isset($_POST['grade']) ? trim($_POST['grade']) : NULL;

    // Validate required fields
    if(!$student_id || !$class_id) {
        $_SESSION['error'] = "Invalid student or class selection";
        header("Location: enrollments.php");
        exit();
    }

    try {
        // Check for existing enrollment
        $check_sql = "SELECT 1 FROM Enrollments 
                      WHERE student_id = ? AND class_id = ? LIMIT 1";
        $stmt = $link->prepare($check_sql);
        $stmt->bind_param("ii", $student_id, $class_id);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $_SESSION['error'] = "Student already enrolled in this class";
            header("Location: enrollments.php");
            exit();
        }
        $stmt->close();

        // Insert enrollment with grade (if provided)
        $insert_sql = "INSERT INTO Enrollments 
                      (student_id, class_id, enrollment_date, grade) 
                      VALUES (?, ?, ?, ?)";
        $stmt = $link->prepare($insert_sql);
        $stmt->bind_param("iiss", $student_id, $class_id, $enrollment_date, $grade);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Student enrolled successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request method";
}

header("Location: enrollments.php");
exit();
?>