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
            // Check if email exists
            $check_email = "SELECT faculty_id FROM Faculty WHERE email = ?";
            if($stmt = mysqli_prepare($link, $check_email)){
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error = "This email is already registered";
                } else {
                    // Insert new faculty
                    $sql = "INSERT INTO Faculty (first_name, last_name, email, phone, department_id, hire_date, salary) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    if($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "ssssisd", 
                            $first_name, $last_name, $email, $phone, 
                            $department_id, $hire_date, $salary);
                        
                        if(mysqli_stmt_execute($stmt)){
                            $success = "Faculty member added successfully";
                            // Clear form
                            $first_name = $last_name = $email = $phone = $department_id = $hire_date = $salary = "";
                        } else {
                            $error = "Error adding faculty: " . mysqli_error($link);
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch all faculty with department names
$faculty_list = array();
$sql = "SELECT f.*, d.department_name 
        FROM Faculty f 
        LEFT JOIN Departments d ON f.department_id = d.department_id
        ORDER BY f.last_name, f.first_name";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $faculty_list[] = $row;
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
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .form-section {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Faculty Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                <i class="fas fa-plus me-2"></i>Add Faculty
            </button>
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
        
        <!-- Faculty Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="facultyTable" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Faculty</th>
                                <th>Contact</th>
                                <th>Department</th>
                                <th>Hire Date</th>
                                <th>Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($faculty_list as $faculty): ?>
                                <tr>
                                    <td><?php echo $faculty['faculty_id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <?php echo substr($faculty['first_name'], 0, 1) . substr($faculty['last_name'], 0, 1); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars($faculty['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($faculty['phone'] ?: 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($faculty['department_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($faculty['hire_date'])); ?>
                                    </td>
                                    <td>
                                        $<?php echo number_format($faculty['salary'], 2); ?>
                                    </td>
                                    <td class="action-btns">
                                        <a href="edit_faculty.php?id=<?php echo $faculty['faculty_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" action="delete_faculty.php" style="display:inline;">
                                            <input type="hidden" name="faculty_id" value="<?php echo $faculty['faculty_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this faculty member?')">
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

    <!-- Add Faculty Modal -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFacultyModalLabel">Add New Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
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
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" id="salary" name="salary" 
                                               value="<?php echo htmlspecialchars($salary); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create" class="btn btn-primary">Add Faculty</button>
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
            $('#facultyTable').DataTable({
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 1 }, // Faculty name
                    { responsivePriority: 2, targets: 6 }, // Actions
                    { responsivePriority: 3, targets: 3 }, // Department
                    { orderable: false, targets: 6 } // Disable sorting on actions column
                ]
            });
            
            // Auto-format phone number
            $('#phone').on('input', function() {
                var number = $(this).val().replace(/[^\d]/g, '');
                if (number.length > 3 && number.length <= 6) {
                    number = number.replace(/(\d{3})(\d{1,3})/, '$1-$2');
                } else if (number.length > 6) {
                    number = number.replace(/(\d{3})(\d{3})(\d{1,4})/, '$1-$2-$3');
                }
                $(this).val(number);
            });
        });
    </script>
</body>
</html>