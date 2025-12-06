<?php
$conn = new mysqli("localhost", "root", "", "college_dbms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$course_code = $_POST['course_code'];
$title = $_POST['title'];
$credits = !empty($_POST['credits']) ? $_POST['credits'] : NULL;
$department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : NULL;
$description = !empty($_POST['description']) ? $_POST['description'] : NULL;

$stmt = $conn->prepare("INSERT INTO courses (course_code, title, credits, department_id, description) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiis", $course_code, $title, $credits, $department_id, $description);

if ($stmt->execute()) {
    echo "<script>alert('Course added successfully!'); window.location.href='course.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
