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


// Check if course ID is provided
if(!isset($_GET["id"]) || empty(trim($_GET["id"]))){
    header("location: course.php");
    exit;
}

$course_id = trim($_GET["id"]);
$course_code = $title = $credits = $department_id = $description = "";
$error = $success = "";

// Fetch course data
$sql = "SELECT * FROM Courses WHERE course_id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $course_code = $row["course_code"];
            $title = $row["title"];
            $credits = $row["credits"];
            $department_id = $row["department_id"];
            $description = $row["description"];
        } else {
            header("location: course.php");
            exit;
        }
    } else {
        $error = "Error fetching course data.";
    }
    mysqli_stmt_close($stmt);
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])){
    // Sanitize inputs
    $course_code = sanitizeInput($_POST["course_code"], $link);
    $title = sanitizeInput($_POST["title"], $link);
    $credits = sanitizeInput($_POST["credits"], $link);
    $department_id = sanitizeInput($_POST["department_id"], $link);
    $description = sanitizeInput($_POST["description"], $link);

    // Validate inputs
    if(!preg_match('/^[A-Z]{2,4}\d{3}$/', $course_code)){
        $error = "Course code must be in format ABC123 (2-4 letters followed by 3 digits)";
    } elseif(!is_numeric($credits) || $credits < 1 || $credits > 5){
        $error = "Credits must be between 1 and 5";
    } else {
        // Check if course code is changed and if new code exists
        if($course_code != $_POST["original_code"]){
            $check_code = "SELECT course_id FROM Courses WHERE course_code = ?";
            if($stmt = mysqli_prepare($link, $check_code)){
                mysqli_stmt_bind_param($stmt, "s", $course_code);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error = "This course code is already taken";
                }
                mysqli_stmt_close($stmt);
            }
        }

        if(empty($error)){
            // Update course
            $sql = "UPDATE Courses SET course_code=?, title=?, credits=?, department_id=?, description=? WHERE course_id=?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ssiisi", 
                    $course_code, $title, $credits, $department_id, $description, $course_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "Course updated successfully.";
                } else {
                    $error = "Error updating course: " . mysqli_error($link);
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
    <title>Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Same CSS as in course.php */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --hover-color: #2980b9;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
        }
        
        .form-section {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .course-code {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .credits-badge {
            background-color: #e8f4fc;
            color: var(--primary-color);
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Course
            </h2>
            <a href="course.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Courses
            </a>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($course_code); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="course_code" class="form-label">Course Code*</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" 
                                   value="<?php echo htmlspecialchars($course_code); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="title" class="form-label">Course Title*</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($title); ?>" required>
                        </div>
                        <div class="col-md-1">
                            <label for="credits" class="form-label">Credits*</label>
                            <input type="number" class="form-control" id="credits" name="credits" 
                                   value="<?php echo htmlspecialchars($credits); ?>" min="1" max="5" required>
                        </div>
                        <div class="col-md-3">
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
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" name="update" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="course.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>