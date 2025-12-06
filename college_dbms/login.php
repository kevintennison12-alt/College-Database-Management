<?php
// login.php
require_once "config.php"; // Handles session_start() and DB connection

// Initialize variables
$username = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Validate credentials (in a real app, query database)
    if ($username === "admin" && $password === "securepassword") {
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        
        // Redirect to secure page
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

// Close connection if exists
if (isset($link)) {
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College DBMS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('image1.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(0,123,255,0.25);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header bg-primary text-white text-center">
            <h3 class="mb-0">PESITM Login Portal</h3>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
