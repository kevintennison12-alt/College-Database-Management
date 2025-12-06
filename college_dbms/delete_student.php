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

// Initialize variables
$faculty_id = $first_name = $last_name = $email = $phone = $department_id = $hire_date = $salary = "";
$error = $success = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["create"])){
        // Validate and sanitize inputs
        $first_name = sanitizeInput($_POST["first_name"], $link);
        $last_name = sanitizeInput($_POST["last_name"], $link);
        $email = sanitizeInput($_POST["email"], $link);
        $phone = sanitizeInput($_POST["phone"], $link);
        $department_id = sanitizeInput($_POST["department_id"], $link);
        $hire_date = sanitizeInput($_POST["hire_date"], $link);
        $salary = sanitizeInput($_POST["salary"], $link);

        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (!is_numeric($salary) || $salary <= 0) {
            $error = "Salary must be a positive number";
        } else {
            // Check if email already exists
            $check_email = "SELECT faculty_id FROM Faculty WHERE email = ?";
            if($stmt = mysqli_prepare($link, $check_email)){
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error = "This email is already taken.";
                } else {
                    // Insert new faculty
                    $sql = "INSERT INTO Faculty (first_name, last_name, email, phone, department_id, hire_date, salary) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    if($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "ssssisd", $first_name, $last_name, $email, $phone, $department_id, $hire_date, $salary);
                        
                        if(mysqli_stmt_execute($stmt)){
                            $success = "Faculty member created successfully.";
                            // Clear form
                            $first_name = $last_name = $email = $phone = $department_id = $hire_date = $salary = "";
                        } else {
                            $error = "Error creating faculty: " . mysqli_error($link);
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch all faculty
$faculty = array();
$sql = "SELECT f.*, d.department_name FROM Faculty f LEFT JOIN Departments d ON f.department_id = d.department_id";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $faculty[] = $row;
    }
    mysqli_free_result($result);
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
    <title>Faculty Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        <h2>Faculty Management</h2>
        
        <!-- Success/Error Messages -->
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Faculty Input Form -->
        <div class="form-section">
            <h4>Add New Faculty Member</h4>
            <form method="post">
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="salary" class="form-label">Salary*</label>
                            <input type="number" step="0.01" class="form-control" id="salary" name="salary" 
                                   value="<?php echo htmlspecialchars($salary); ?>" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="create" class="btn btn-primary">Add Faculty</button>
            </form>
        </div>
        
        <!-- Faculty Table -->
        <h4>Current Faculty</h4>
        <table id="facultyTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Hire Date</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($faculty as $fac): ?>
                    <tr>
                        <td><?php echo $fac['faculty_id']; ?></td>
                        <td><?php echo htmlspecialchars($fac['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($fac['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($fac['email']); ?></td>
                        <td><?php echo htmlspecialchars($fac['phone']); ?></td>
                        <td><?php echo $fac['department_name'] ?? 'N/A'; ?></td>
                        <td><?php echo $fac['hire_date']; ?></td>
                        <td>$<?php echo number_format($fac['salary'], 2); ?></td>
                        <td>
                            <a href="edit_faculty.php?id=<?php echo $fac['faculty_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="delete_faculty.php" style="display:inline;">
                                <input type="hidden" name="faculty_id" value="<?php echo $fac['faculty_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this faculty member?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#facultyTable').DataTable();
        });
    </script>
</body>
</html>