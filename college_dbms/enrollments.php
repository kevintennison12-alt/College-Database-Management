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

// Fetch all enrollments with related data including grade
$enrollments = [];
$sql = "SELECT e.enrollment_id, e.enrollment_date, e.grade,
               s.student_id, s.first_name AS student_first, s.last_name AS student_last,
               c.class_id, co.course_code, co.title AS course_title,
               f.first_name AS faculty_first, f.last_name AS faculty_last
        FROM Enrollments e
        JOIN Students s ON e.student_id = s.student_id
        JOIN Classes c ON e.class_id = c.class_id
        JOIN Courses co ON c.course_id = co.course_id
        JOIN Faculty f ON c.faculty_id = f.faculty_id
        ORDER BY e.enrollment_date DESC";

if($result = mysqli_query($link, $sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $enrollments[] = $row;
    }
    mysqli_free_result($result);
}

// Fetch all students for dropdown
$students = [];
$sql = "SELECT student_id, first_name, last_name FROM Students ORDER BY last_name, first_name";
if($result = mysqli_query($link, $sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    mysqli_free_result($result);
}

// Fetch all classes for dropdown
$classes = [];
$sql = "SELECT c.class_id, co.course_code, co.title, 
               f.first_name AS faculty_first, f.last_name AS faculty_last
        FROM Classes c
        JOIN Courses co ON c.course_id = co.course_id
        JOIN Faculty f ON c.faculty_id = f.faculty_id
        ORDER BY co.course_code";
if($result = mysqli_query($link, $sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row;
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
    <title>Enrollment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .enrollment-card {
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }
        .enrollment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-enrollment {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 10px;
        }
        .capacity-indicator {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        .capacity-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #28a745;
        }
        /* Grade badge colors */
        .grade-A { background-color: #28a745; color: white; }
        .grade-B { background-color: #17a2b8; color: white; }
        .grade-C { background-color: #6c757d; color: white; }
        .grade-D { background-color: #ffc107; color: #212529; }
        .grade-F { background-color: #dc3545; color: white; }
        .grade-I { background-color: #6610f2; color: white; }
        .grade-W { background-color: #fd7e14; color: white; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5">
                <i class="fas fa-user-graduate me-2"></i> Enrollment Management
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                <i class="fas fa-plus me-1"></i> New Enrollment
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Current Enrollments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="enrollmentsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Enrollment ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Date</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($enrollments as $enrollment): ?>
                                <tr>
                                    <td><?= $enrollment['enrollment_id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($enrollment['student_last'] . ', ' . $enrollment['student_first']) ?></strong>
                                        <div class="text-muted small">ID: <?= $enrollment['student_id'] ?></div>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($enrollment['course_code']) ?></strong>
                                        <div class="text-muted small"><?= htmlspecialchars($enrollment['course_title']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($enrollment['faculty_last'] . ', ' . $enrollment['faculty_first']) ?></td>
                                    <td><?= date('M j, Y', strtotime($enrollment['enrollment_date'])) ?></td>
                                    <td>
                                        <?php if($enrollment['grade']): ?>
                                            <span class="badge grade-<?= $enrollment['grade'] ?>">
                                                <?= $enrollment['grade'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="enrollment_view.php?id=<?= $enrollment['enrollment_id'] ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="enrollment_delete.php" method="POST">
                                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['enrollment_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="Delete" onclick="return confirm('Remove this enrollment?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Enrollment Modal -->
    <div class="modal fade" id="addEnrollmentModal" tabindex="-1" aria-labelledby="addEnrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addEnrollmentModalLabel">
                        <i class="fas fa-user-plus me-2"></i> Enroll Student
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="enrollment_create.php" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="student_id" class="form-label">Student*</label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach($students as $student): ?>
                                        <option value="<?= $student['student_id'] ?>">
                                            <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="class_id" class="form-label">Class*</label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php foreach($classes as $class): ?>
                                        <option value="<?= $class['class_id'] ?>">
                                            <?= htmlspecialchars($class['course_code'] . ' - ' . $class['title'] . ' (' . $class['faculty_last'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="enrollment_date" class="form-label">Enrollment Date*</label>
                                <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="grade" class="form-label">Initial Grade (optional)</label>
                                <select class="form-select" id="grade" name="grade">
                                    <option value="">No grade</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                    <option value="I">Incomplete</option>
                                    <option value="W">Withdrawn</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enroll Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#enrollmentsTable').DataTable({
                responsive: true,
                order: [[4, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search enrollments...",
                    lengthMenu: "Show _MENU_ enrollments per page",
                    zeroRecords: "No matching enrollments found",
                    info: "Showing _START_ to _END_ of _TOTAL_ enrollments",
                    infoEmpty: "No enrollments available",
                    infoFiltered: "(filtered from _MAX_ total enrollments)"
                }
            });
            
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>