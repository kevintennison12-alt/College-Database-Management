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


// Form submission handling
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create"])){
    $course_code = $_POST["course_code"];
    $title = $_POST["title"];
    $credits = $_POST["credits"];
    $department_id = $_POST["department_id"];
    $description = $_POST["description"];

    $sql = "INSERT INTO Courses (course_code, title, credits, department_id, description) 
            VALUES (?, ?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ssiis", $course_code, $title, $credits, $department_id, $description);
        mysqli_stmt_execute($stmt);
        $success = "Course added successfully";
        $course_code = $title = $credits = $department_id = $description = "";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all courses with department names
$courses = array();
$sql = "SELECT c.*, d.department_name FROM Courses c LEFT JOIN Departments d ON c.department_id = d.department_id";
$result = mysqli_query($link, $sql);
while($row = mysqli_fetch_assoc($result)){
    $courses[] = $row;
}

// Fetch departments for dropdown
$departments = array();
$sql = "SELECT * FROM Departments";
$result = mysqli_query($link, $sql);
while($row = mysqli_fetch_assoc($result)){
    $departments[] = $row;
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .course-code {
            font-weight: bold;
            color: #3498db;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <h2 class="mb-4">Course Management</h2>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Add Course Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add New Course</h5>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="course_code" placeholder="Course Code" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="title" placeholder="Course Title" required>
                        </div>
                        <div class="col-md-1">
                            <input type="number" class="form-control" name="credits" placeholder="Credits" min="1" max="5" required>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>">
                                        <?php echo $dept['department_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="create" class="btn btn-primary w-100">Add</button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <textarea class="form-control" name="description" placeholder="Description"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Courses Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">All Courses</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Credits</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($courses as $course): ?>
                                <tr>
                                    <td><span class="course-code"><?php echo $course['course_code']; ?></span></td>
                                    <td><?php echo $course['title']; ?></td>
                                    <td><?php echo $course['credits']; ?></td>
                                    <td><?php echo $course['department_name']; ?></td>
                                    <td>
                                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" action="delete_course.php" style="display:inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>