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

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: enrollments.php");
    exit();
}

$enrollment_id = $_GET['id'];
$enrollment = [];

// Fetch enrollment details
$sql = "SELECT e.*, 
               s.student_id, s.first_name AS student_first, s.last_name AS student_last, s.email AS student_email,
               c.class_id, co.course_code, co.title AS course_title, co.credits,
               f.faculty_id, f.first_name AS faculty_first, f.last_name AS faculty_last, f.email AS faculty_email
        FROM Enrollments e
        JOIN Students s ON e.student_id = s.student_id
        JOIN Classes c ON e.class_id = c.class_id
        JOIN Courses co ON c.course_id = co.course_id
        JOIN Faculty f ON c.faculty_id = f.faculty_id
        WHERE e.enrollment_id = ?";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $enrollment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$enrollment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$enrollment) {
    header("Location: enrollments.php");
    exit();
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="enrollments.php">Enrollments</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    Enrollment Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Student Information</h5>
                            </div>
                            <div class="card-body">
                                <h4><?= htmlspecialchars($enrollment['student_last'] . ', ' . $enrollment['student_first']) ?></h4>
                                <p class="mb-1"><strong>Student ID:</strong> <?= $enrollment['student_id'] ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($enrollment['student_email']) ?></p>
                                <a href="student_view.php?id=<?= $enrollment['student_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    View Student Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i> Class Information</h5>
                            </div>
                            <div class="card-body">
                                <h4><?= htmlspecialchars($enrollment['course_code'] . ' - ' . $enrollment['course_title']) ?></h4>
                                <p class="mb-1"><strong>Instructor:</strong> <?= htmlspecialchars($enrollment['faculty_last'] . ', ' . $enrollment['faculty_first']) ?></p>
                                <p class="mb-1"><strong>Credits:</strong> <?= $enrollment['credits'] ?></p>
                                <a href="class_view.php?id=<?= $enrollment['class_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    View Class Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Enrollment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Enrollment ID:</strong> <?= $enrollment['enrollment_id'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Enrollment Date:</strong> <?= date('F j, Y', strtotime($enrollment['enrollment_date'])) ?></p>
                            </div>
                            <div class="col-md-4">
                                <form action="enrollment_delete.php" method="POST" class="d-inline">
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['enrollment_id'] ?>">
                                    <button type="submit" class="btn btn-danger float-end" 
                                            onclick="return confirm('Remove this enrollment?')">
                                        <i class="fas fa-user-minus me-1"></i> Remove Enrollment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>