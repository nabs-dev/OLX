<?php
session_start();
include 'database.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Check if username exists
        $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $result = $conn->query($query);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to homepage or previous page
                if(isset($_GET['redirect'])) {
                    echo "<script>window.location.href = '" . $_GET['redirect'] . "';</script>";
                } else {
                    echo "<script>window.location.href = 'index.php';</script>";
                }
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Username not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OLX Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f2f4f5;
            color: #002f34;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            flex: 1;
        }
        
        /* Header Styles */
        header {
            background-color: #ffffff;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #002f34;
            text-decoration: none;
        }
        
        .logo span {
            color: #23e5db;
        }
        
        /* Form Styles */
        .login-container {
            max-width: 450px;
            margin: 60px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #002f34;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #002f34;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            border-color: #23e5db;
            outline: none;
        }
        
        .submit-btn {
            width: 100%;
            background-color: #002f34;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #00474f;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
        }
        
        .signup-link a {
            color: #23e5db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        /* Footer */
        footer {
            background-color: #002f34;
            color: white;
            padding: 20px 0;
            margin-top: auto;
        }
        
        .copyright {
            text-align: center;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">OLX<span>Clone</span></a>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <h2 class="form-title">Login to Your Account</h2>
            
            <?php if(!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . (isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''); ?>" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="signup.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">Sign Up</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 OLX Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
