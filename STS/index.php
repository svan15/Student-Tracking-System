<?php
    session_start();
    include 'connect.php'; // Include database connection

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check user credentials in the User table
        $stmt = $connection->prepare("SELECT * FROM User WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];

    // If user is a school, get their hei_id and store it in session
    if ($user['user_type'] == 'school') {
        $hei_query = $connection->prepare("SELECT hei_id FROM tbl_heis WHERE user_id = ?");
        $hei_query->bind_param("i", $user['id']);
        $hei_query->execute();
        $hei_result = $hei_query->get_result();
        
        if ($hei_result->num_rows === 1) {
            $hei_data = $hei_result->fetch_assoc();
            $_SESSION['hei_id'] = $hei_data['hei_id'];
        } else {
            // Log the error but still allow login
            error_log("Warning: School user (ID: {$user['id']}) has no associated HEI record");
        }
        $hei_query->close();
    }

    // Redirect to appropriate dashboard
        if ($user['user_type'] == 'admin') {
            header("Location: ched_admin.php");
            exit();
        } else if ($user['user_type'] == 'ched') {
            header("Location: ched_HEI.php");
            exit();
        } else {
            header("Location: school_dashboard.php");
            exit();
        }
            exit();
        } else {
            $error = "Invalid password!";
        }
        } else {
            $error = "User not found!";
        }
        $stmt->close();
        $connection->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</head>
<body class="d-flex flex-column vh-100 bg-light">
    <header class="fixed-top w-100 bg-primary shadow-sm d-flex align-items-center p-2">
        <img src="img/CHED-LOGO_orig.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <img src="img/Bagong-Pilipinas.png" alt="Logo" class="me-2 rounded-circle" width="50" height="50">
        <h3 class="text-white mb-0">Students Tracking System</h3>
    </header>
    <div class="container d-flex justify-content-center align-items-center flex-grow-1">
        <div class="card shadow p-4" style="width: 350px;">
            <h2 class="text-center text-dark mb-3">Login</h2>
            
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="index.php">
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3 input-group">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                        <i class="fa fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>

                <!-- Register Link -->
                <p class="mt-3 text-center">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
