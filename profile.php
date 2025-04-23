<?php
session_start();
include 'database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Handle profile update
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if username or email already exists (excluding current user)
    $check_query = "SELECT * FROM users WHERE (username = '$username' OR email = '$email') AND id != '$user_id'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error = "Username or email already exists";
    } else {
        // Update profile
        $update_query = "UPDATE users SET username = '$username', email = '$email', phone = '$phone' WHERE id = '$user_id'";
        
        // If password change is requested
        if (!empty($current_password) && !empty($new_password)) {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                if ($new_password !== $confirm_password) {
                    $error = "New passwords do not match";
                } elseif (strlen($new_password) < 6) {
                    $error = "New password must be at least 6 characters long";
                } else {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET username = '$username', email = '$email', phone = '$phone', password = '$hashed_password' WHERE id = '$user_id'";
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
        
        if (empty($error)) {
            if ($conn->query($update_query) === TRUE) {
                $success = "Profile updated successfully";
                
                // Update session username if changed
                if ($username !== $user['username']) {
                    $_SESSION['username'] = $username;
                }
                
                // Refresh user data
                $user_result = $conn->query($user_query);
                $user = $user_result->fetch_assoc();
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Get user stats
$ads_count_query = "SELECT COUNT(*) as total FROM ads WHERE user_id = '$user_id'";
$ads_count_result = $conn->query($ads_count_query);
$ads_count = $ads_count_result->fetch_assoc()['total'];

$messages_count_query = "SELECT COUNT(*) as total FROM messages WHERE receiver_id = '$user_id'";
$messages_count_result = $conn->query($messages_count_query);
$messages_count = $messages_count_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - OLX Clone</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
        header {
            background-color: #ffffff;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #002f34;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #23e5db;
        }
        
        .post-btn {
            background-color: #002f34;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .post-btn:hover {
            background-color: #00474f;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0;
        }
        
        .page-title {
            font-size: 24px;
            color: #002f34;
        }
        
        /* Profile Section */
        .profile-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .profile-section {
                grid-template-columns: 1fr;
            }
        }
        
        .profile-stats {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stats-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: #002f34;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }
        
        .stat-card {
            text-align: center;
            padding: 15px;
            border-radius: 6px;
            background-color: #f8f9fa;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #002f34;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #777;
        }
        
        .profile-info {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: #002f34;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
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
        
        .password-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .password-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: #002f34;
        }
        
        .submit-btn {
            background-color: #002f34;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #00474f;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        /* Footer */
        footer {
            background-color: #002f34;
            color: white;
            padding: 30px 0;
            margin-top: 40px;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section ul li a:hover {
            color: #23e5db;
        }
        
        .copyright {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">OLX<span>Clone</span></a>
            <div class="nav-links">
                <a href="my_ads.php">My Ads</a>
                <a href="profile.php" class="active">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
            <button class="post-btn" onclick="window.location.href='post_ad.php'">+ SELL</button>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
        </div>
        
        <div class="profile-section">
            <div class="profile-stats">
                <h2 class="stats-title">Account Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $ads_count; ?></div>
                        <div class="stat-label">Active Ads</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $messages_count; ?></div>
                        <div class="stat-label">Messages</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)); ?></div>
                        <div class="stat-label">Days Active</div>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px; color: #002f34;">Quick Links</h3>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 10px;">
                            <a href="my_ads.php" style="color: #23e5db; text-decoration: none; display: flex; align-items: center;">
                                <span style="margin-right: 8px;">→</span> View My Ads
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <a href="post_ad.php" style="color: #23e5db; text-decoration: none; display: flex; align-items: center;">
                                <span style="margin-right: 8px;">→</span> Post a New Ad
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="profile-info">
                <h2 class="profile-title">Edit Profile</h2>
                
                <?php if(!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="password-section">
                        <h3 class="password-title">Change Password</h3>
                        <p style="margin-bottom: 15px; color: #777; font-size: 14px;">Leave these fields empty if you don't want to change your password.</p>
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container footer-container">
            <div class="footer-section">
                <h3>POPULAR CATEGORIES</h3>
                <ul>
                    <li><a href="index.php?category=Cars">Cars</a></li>
                    <li><a href="index.php?category=Flats">Flats for rent</a></li>
                    <li><a href="index.php?category=Mobile+Phones">Mobile Phones</a></li>
                    <li><a href="index.php?category=Jobs">Jobs</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>ABOUT US</h3>
                <ul>
                    <li><a href="#">About OLX Clone</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">OLX People</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>OLX</h3>
                <ul>
                    <li><a href="#">Help</a></li>
                    <li><a href="#">Sitemap</a></li>
                    <li><a href="#">Legal & Privacy information</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 OLX Clone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
