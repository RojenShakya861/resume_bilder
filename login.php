<?php
require_once '../include/config.php';

if (is_logged_in()) {
    redirect('../index.php');
}

$error = '';
$login_type = isset($_GET['type']) ? $_GET['type'] : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type'];
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $login_type]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($login_type === 'admin') {
            redirect('../admin/dashboard.php');
        } else {
            redirect('../index.php');
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Resume Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #6c5ce7;
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: #6c5ce7;
            border: none;
            padding: 10px;
        }
        .btn-primary:hover {
            background: #5b4bc4;
        }
        .form-control {
            padding: 10px;
            border-radius: 10px;
        }
        .login-type-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .login-type-toggle .btn {
            border-radius: 20px;
            padding: 8px 20px;
            margin: 0 5px;
        }
        .login-type-toggle .btn.active {
            background: #6c5ce7;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Resume Builder</h3>
                <p class="mb-0">Login to your account</p>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="login-type-toggle">
                    <a href="?type=user" class="btn <?php echo $login_type === 'user' ? 'btn-primary active' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-user"></i> User Login
                    </a>
                    <a href="?type=admin" class="btn <?php echo $login_type === 'admin' ? 'btn-primary active' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="login_type" value="<?php echo $login_type; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo $login_type === 'admin' ? 'Admin Login' : 'User Login'; ?>
                    </button>
                </form>
                
                <?php if ($login_type === 'user'): ?>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 