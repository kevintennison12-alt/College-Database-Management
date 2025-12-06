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
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["create"])){
        // Sanitize inputs
        $course_id = sanitizeInput($_POST["course_id"], $link);
        $faculty_id = sanitizeInput($_POST["faculty_id"], $link);
        $semester = sanitizeInput($_POST["semester"], $link);
        $year = sanitizeInput($_POST["year"], $link);
        $room_number = sanitizeInput($_POST["room_number"], $link);
        $schedule = sanitizeInput($_POST["schedule"], $link);
        $max_capacity = sanitizeInput($_POST["max_capacity"], $link);

        // Validate inputs
        if(!is_numeric($year) || $year < 2020 || $year > 2030){
            $error = "Year must be between 2020 and 2030";
        } elseif(!is_numeric($max_capacity) || $max_capacity < 1){
            $error = "Capacity must be at least 1";
        } else {
            // Insert new class
            $sql = "INSERT INTO Classes (course_id, faculty_id, semester, year, room_number, schedule, max_capacity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "iissssi", 
                    $course_id, $faculty_id, $semester, $year, 
                    $room_number, $schedule, $max_capacity);
                
                if(mysqli_stmt_execute($stmt)){
                    $success = "Class created successfully";
                    // Clear form
                    $course_id = $faculty_id = $semester = $year = $room_number = $schedule = $max_capacity = "";
                } else {
                    $error = "Error creating class: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch all classes with related info
$classes = array();
$sql = "SELECT cl.*, c.course_code, c.title AS course_title, 
               f.first_name AS faculty_first, f.last_name AS faculty_last,
               d.department_name
        FROM Classes cl
        JOIN Courses c ON cl.course_id = c.course_id
        JOIN Faculty f ON cl.faculty_id = f.faculty_id
        JOIN Departments d ON f.department_id = d.department_id
        ORDER BY cl.year DESC, cl.semester, c.course_code";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $classes[] = $row;
    }
    mysqli_free_result($result);
}

// Fetch courses for dropdown
$courses = array();
$sql = "SELECT * FROM Courses ORDER BY course_code";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $courses[] = $row;
    }
    mysqli_free_result($result);
}

// Fetch faculty for dropdown
$faculty = array();
$sql = "SELECT * FROM Faculty ORDER BY last_name, first_name";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $faculty[] = $row;
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
    <title>Class Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
        
        .class-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }
        
        .badge-semester {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 10px;
        }
        
        .badge-spring {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-fall {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-summer {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .action-btns .btn {
            margin-right: 5px;
            transition: all 0.2s ease;
        }
        
        .action-btns .btn:hover {
            transform: scale(1.1);
        }
        
        .capacity-bar {
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            background-color: #e9ecef;
        }
        
        .capacity-fill {
            height: 100%;
            background-color: var(--primary-color);
            transition: width 0.5s ease;
        }
        
        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .fab:hover {
            transform: scale(1.1);
            background-color: var(--hover-color);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="class-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-chalkboard-teacher me-3"></i>Class Management</h1>
                    <p class="mb-0">Schedule and manage all class sections</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark fs-6">
                        <i class="fas fa-calendar-alt me-2"></i><?php echo date('F Y'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-4">
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
        
        <!-- Add Class Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-plus-circle me-2"></i>Schedule New Class</h5>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="course_id" class="form-label">Course*</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach($courses as $course): ?>
                                    <option value="<?php echo $course['course_id']; ?>"
                                        <?php if($course_id == $course['course_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="faculty_id" class="form-label">Instructor*</label>
                            <select class="form-select" id="faculty_id" name="faculty_id" required>
                                <option value="">Select Instructor</option>
                                <?php foreach($faculty as $prof): ?>
                                    <option value="<?php echo $prof['faculty_id']; ?>"
                                        <?php if($faculty_id == $prof['faculty_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="semester" class="form-label">Semester*</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select</option>
                                <option value="Spring" <?php if($semester == 'Spring') echo 'selected'; ?>>Spring</option>
                                <option value="Summer" <?php if($semester == 'Summer') echo 'selected'; ?>>Summer</option>
                                <option value="Fall" <?php if($semester == 'Fall') echo 'selected'; ?>>Fall</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Year*</label>
                            <input type="number" class="form-control" id="year" name="year" 
                                   value="<?php echo htmlspecialchars($year ?: date('Y')); ?>" 
                                   min="2020" max="2030" required>
                        </div>
                    </div>
                    
                    <div class="row mt-3 g-3">
                        <div class="col-md-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <input type="text" class="form-control" id="room_number" name="room_number" 
                                   value="<?php echo htmlspecialchars($room_number); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="schedule" class="form-label">Schedule*</label>
                            <input type="text" class="form-control" id="schedule" name="schedule" 
                                   value="<?php echo htmlspecialchars($schedule); ?>" 
                                   placeholder="e.g. MWF 10:00-10:50" required>
                        </div>
                        <div class="col-md-3">
                            <label for="max_capacity" class="form-label">Max Capacity*</label>
                            <input type="number" class="form-control" id="max_capacity" name="max_capacity" 
                                   value="<?php echo htmlspecialchars($max_capacity ?: 30); ?>" 
                                   min="1" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="create" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Classes Table -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-list me-2"></i>Scheduled Classes</h5>
                <div class="table-responsive">
                    <table id="classesTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Class ID</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Term</th>
                                <th>Schedule</th>
                                <th>Room</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($classes as $class): 
                                $enrollment_percent = ($class['current_enrollment'] / $class['max_capacity']) * 100;
                            ?>
                                <tr>
                                    <td><?php echo $class['class_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($class['course_code']); ?></strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($class['course_title']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($class['faculty_first'] . ' ' . $class['faculty_last']); ?>
                                        <div class="text-muted small"><?php echo htmlspecialchars($class['department_name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-semester badge-<?php echo strtolower($class['semester']); ?>">
                                            <?php echo htmlspecialchars($class['semester'] . ' ' . $class['year']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                                    <td><?php echo htmlspecialchars($class['room_number'] ?: 'TBD'); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 text-nowrap">
                                                <?php echo $class['current_enrollment']; ?>/<?php echo $class['max_capacity']; ?>
                                            </div>
                                            <div class="capacity-bar" style="width: 100px;">
                                                <div class="capacity-fill" style="width: <?php echo $enrollment_percent; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="action-btns">
                                        <a href="edit_class.php?id=<?php echo $class['class_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" action="delete_class.php" style="display:inline;">
                                            <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this class?')">
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

    <!-- Floating Action Button -->
    <a href="#" class="fab" data-bs-toggle="modal" data-bs-target="#addClassModal">
        <i class="fas fa-plus"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#classesTable').DataTable({
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 1 }, // Course
                    { responsivePriority: 2, targets: 7 }, // Actions
                    { responsivePriority: 3, targets: 2 }, // Instructor
                    { orderable: false, targets: 7 } // Disable sorting on actions
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search classes...",
                    lengthMenu: "Show _MENU_ classes per page",
                    zeroRecords: "No classes found",
                    info: "Showing _START_ to _END_ of _TOTAL_ classes",
                    infoEmpty: "No classes available",
                    infoFiltered: "(filtered from _MAX_ total classes)"
                }
            });
        });
    </script>
</body>
</html>