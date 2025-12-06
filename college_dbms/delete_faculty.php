<?php
require_once "config.php";
requireLogin();

// Check if faculty ID is provided
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["faculty_id"])){
    $faculty_id = trim($_POST["faculty_id"]);
    
    // First, check if faculty teaches any classes
    $check_classes = "SELECT class_id FROM Classes WHERE faculty_id = ?";
    if($stmt = mysqli_prepare($link, $check_classes)){
        mysqli_stmt_bind_param($stmt, "i", $faculty_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0){
            // Faculty teaches classes - don't delete
            $_SESSION["error"] = "Cannot delete faculty because they are assigned to classes. Reassign classes first.";
        } else {
            // No classes - safe to delete
            $sql = "DELETE FROM Faculty WHERE faculty_id = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $faculty_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $_SESSION["success"] = "Faculty member deleted successfully.";
                } else {
                    $_SESSION["error"] = "Error deleting faculty: " . mysqli_error($link);
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
header("location: faculty.php");
exit;
?>