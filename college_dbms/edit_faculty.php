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


// Check if faculty ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: faculty.php");
    exit;
}

$faculty_id = trim($_GET["id"]);
$first_name = $last_name = $email = $phone = $department_id = $hire_date = $salary = "";
$error = $success = "";

// Fetch faculty data
$sql = "SELECT * FROM Faculty WHERE faculty_id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $faculty_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $first_name = $row["first_name"];
            $last_name = $row["last_name"];
            $email = $row["email"];
            $phone = $row["phone"];
            $department_id = $row["department_id"];
            $hire_date = $row["hire_date"];
            $salary = $row["salary"];
        } else {
            header("location: faculty.php");
            exit;
        }
    } else {
        $error = "Error fetching faculty data.";
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])){
    // Sanitize inputs
    $first_name = sanitizeInput($_POST["first_name"], $link);
    $last_name = sanitizeInput($_POST["last_name"], $link);
    $email = sanitizeInput($_POST["email"], $link);
    $phone = sanitizeInput($_POST["phone"], $link);
    $department_id = sanitizeInput($_POST["department_id"], $link);
    $hire_date = sanitizeInput($_POST["hire_date"], $link);
    $salary = sanitizeInput($_POST["salary"], $link);

    // Validate inputs
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format";
    } elseif(!is_numeric($salary) || $salary <= 0){
        $error = "Salary must be a positive number";
    } else {
        // Check if email is changed and if new email exists
        if($email != $_POST["original_email"]){
            $check_email = "SELECT faculty_id FROM Faculty WHERE email = ?";
            if($stmt = mysqli_prepare($link, $check_email)){
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error = "This email is already taken.";
                }
                mysqli_stmt_close($stmt);
            }
        }

        if(empty($error)){
            // Update faculty
            $sql = "UPDATE Faculty SET first_name=?, last_name=?, email=?, phone=?, department_id=?, hire_date=?, salary=? WHERE faculty_id=?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ssssisdi", 
                    $first_name, $last_name, $email, $phone, 
                    $department_id, $hire_date, $salary, $faculty_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "Faculty member updated successfully.";
                } else {
                    $error = "Error updating faculty: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch departments for dropdown
$departments = array();
$sql = "SELECT * FROM Departments ORDER BY department_name";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $departments[] = $row;
    }
    mysqli_free_result($result);
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .form-section {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Faculty Member</h2>
            <a href="faculty.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Faculty
            </a>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="form-section">
            <form method="post">
                <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name*</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name*</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department*</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>"
                                        <?php if($department_id == $dept['department_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date*</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" 
                                   value="<?php echo htmlspecialchars($hire_date); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6