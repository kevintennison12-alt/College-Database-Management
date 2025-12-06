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


// Check if student ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: students.php");
    exit;
}

$student_id = trim($_GET["id"]);
$first_name = $last_name = $email = $phone = $enrollment_date = $major_department_id = "";
$error = $success = "";

// Fetch student data
$sql = "SELECT * FROM Students WHERE student_id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $first_name = $row["first_name"];
            $last_name = $row["last_name"];
            $email = $row["email"];
            $phone = $row["phone"];
            $enrollment_date = $row["enrollment_date"];
            $major_department_id = $row["major_department_id"];
        } else {
            header("location: students.php");
            exit;
        }
    } else {
        $error = "Error fetching student data.";
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])){
    // Validate and sanitize inputs
    $first_name = sanitizeInput($_POST["first_name"], $link);
    $last_name = sanitizeInput($_POST["last_name"], $link);
    $email = sanitizeInput($_POST["email"], $link);
    $phone = sanitizeInput($_POST["phone"], $link);
    $enrollment_date = sanitizeInput($_POST["enrollment_date"], $link);
    $major_department_id = sanitizeInput($_POST["major_department_id"], $link);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email is changed and if new email already exists
        if($email != $_POST["original_email"]){
            $check_email = "SELECT student_id FROM Students WHERE email = ?";
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
            // Update student
            $sql = "UPDATE Students SET first_name=?, last_name=?, email=?, phone=?, enrollment_date=?, major_department_id=? WHERE student_id=?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sssssii", $first_name, $last_name, $email, $phone, $enrollment_date, $major_department_id, $student_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "Student updated successfully.";
                } else {
                    $error = "Error updating student: " . mysqli_error($link);
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
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Edit Student</h2>
        
        <!-- Success/Error Messages -->
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Student Edit Form -->
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
                            <label for="enrollment_date" class="form-label">Enrollment Date*</label>
                            <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                   value="<?php echo htmlspecialchars($enrollment_date); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="major_department_id" class="form-label">Major Department*</label>
                            <select class="form-select" id="major_department_id" name="major_department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>" 
                                        <?php if($major_department_id == $dept['department_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update" class="btn btn-primary">Update Student</button>
                <a href="students.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>