<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College DBMS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            min-height: 100vh;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: white;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: #1abc9c;
            border-radius: 5px;
        }
        .main-content {
            padding: 30px;
        }
        .dashboard-header {
            font-weight: 600;
            margin-bottom: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card .card-body {
            padding: 30px;
        }
        .card .display-4 {
            font-weight: 600;
        }
        .nav-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 sidebar d-md-block">
            <div class="nav-title">College DBMS</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                <li class="nav-item"><a class="nav-link" href="faculty.php">Faculty</a></li>
                <li class="nav-item"><a class="nav-link" href="course.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="classes.php">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="enrollments.php">Enrollments</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <h1 class="dashboard-header">Welcome to the Dashboard</h1>
            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Students</h5>
                            <?php
                            require_once "config.php";
                            $sql = "SELECT COUNT(*) as total FROM Students";
                            $result = mysqli_query($link, $sql);
                            $row = mysqli_fetch_assoc($result);
                            echo "<p class='card-text display-4'>" . $row['total'] . "</p>";
                            mysqli_free_result($result);
                            ?>
                            <a href="students.php" class="text-white">View All</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Faculty</h5>
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM Faculty";
                            $result = mysqli_query($link, $sql);
                            $row = mysqli_fetch_assoc($result);
                            echo "<p class='card-text display-4'>" . $row['total'] . "</p>";
                            mysqli_free_result($result);
                            ?>
                            <a href="faculty.php" class="text-white">View All</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Courses</h5>
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM Courses";
                            $result = mysqli_query($link, $sql);
                            $row = mysqli_fetch_assoc($result);
                            echo "<p class='card-text display-4'>" . $row['total'] . "</p>";
                            mysqli_free_result($result);
                            mysqli_close($link);
                            ?>
                            <a href="course.php" class="text-white">View All</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
