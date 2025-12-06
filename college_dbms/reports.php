<?php
require_once "config.php";

// Report 1: Student count by department
$departmentStats = array();
$sql = "SELECT d.department_name, COUNT(s.student_id) as student_count 
        FROM Departments d 
        LEFT JOIN Students s ON d.department_id = s.major_department_id 
        GROUP BY d.department_name";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $departmentStats[] = $row;
    }
    mysqli_free_result($result);
}

// Report 2: Course enrollment statistics
$enrollmentStats = array();
$sql = "SELECT c.course_code, c.title, 
               AVG(CASE 
                   WHEN e.grade = 'A' THEN 4.0
                   WHEN e.grade = 'A-' THEN 3.7
                   WHEN e.grade = 'B+' THEN 3.3
                   WHEN e.grade = 'B' THEN 3.0
                   WHEN e.grade = 'B-' THEN 2.7
                   WHEN e.grade = 'C+' THEN 2.3
                   WHEN e.grade = 'C' THEN 2.0
                   ELSE NULL
               END) as avg_gpa,
               COUNT(e.enrollment_id) as enrollment_count
        FROM Courses c
        LEFT JOIN Classes cl ON c.course_id = cl.course_id
        LEFT JOIN Enrollment e ON cl.class_id = e.class_id
        GROUP BY c.course_id";
if($result = mysqli_query($link, $sql)){
    while($row = mysqli_fetch_assoc($result)){
        $enrollmentStats[] = $row;
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
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Reports</h2>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Students by Department</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Department Statistics</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Student Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($departmentStats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['department_name']); ?></td>
                                        <td><?php echo $stat['student_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Course Enrollment and GPA Statistics</h5>
                    </div>
                    <div class="card-body">
                        <table id="enrollmentTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Title</th>
                                    <th>Average GPA</th>
                                    <th>Enrollment Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($enrollmentStats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['title']); ?></td>
                                        <td><?php echo number_format($stat['avg_gpa'], 2); ?></td>
                                        <td><?php echo $stat['enrollment_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#enrollmentTable').DataTable();
            
            // Department chart
            var departmentCtx = document.getElementById('departmentChart').getContext('2d');
            var departmentChart = new Chart(departmentCtx, {
                type: 'bar',
                data: {
                    labels: [<?php echo implode(',', array_map(function($stat) { return "'" . addslashes($stat['department_name']) . "'"; }, $departmentStats)); ?>],
                    datasets: [{
                        label: 'Number of Students',
                        data: [<?php echo implode(',', array_column($departmentStats, 'student_count')); ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>