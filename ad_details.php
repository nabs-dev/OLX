<?php
session_start();
include 'database.php';

// Check if ad ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$ad_id = $conn->real_escape_string($_GET['id']);

// Get ad details
$ad_query = "SELECT a.*, u.username, u.phone FROM ads a 
             JOIN users u ON a.user_id = u.id 
             WHERE a.id = '$ad_id'";
$ad_result = $conn->query($ad_query);

if($ad_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$ad = $ad_result->fetch_assoc();

// Get other ads from the same user
$user_id = $ad['user_id'];
$other_ads_query = "SELECT * FROM ads WHERE user_id = '$user_id' AND id != '$ad_id' ORDER BY created_at DESC LIMIT 4";
$other_ads_result = $conn->query($other_ads_query);

// Get similar ads (same category)
$category = $ad['category'];
$similar_ads_query = "SELECT * FROM ads WHERE category = '$category' AND id != '$ad_id' ORDER BY created_at DESC LIMIT 4";
$similar_ads_result = $conn->query($similar_ads_query);

// Handle contact form
$message_sent = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=ad_details.php?id=$ad_id");
        exit();
    }
    
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $ad['user_id'];
    $message = $conn->real_escape_string($_POST['message']);
    
    // Validate input
    if (empty($message)) {
        $error = "Message cannot be empty";
    } elseif ($sender_id == $receiver_id) {
        $error = "You cannot send a message to yourself";
    } else {
        // Insert message into database
        $insert_query = "INSERT INTO messages (sender_id, receiver_id, ad_id, message) 
                        VALUES ('$sender_id', '$receiver_id', '$ad_id', '$message')";
        
        if ($conn->query($insert_query) === TRUE) {
            $message_sent = true;
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - OLX Clone</title>
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
        
        /* Ad Details Section */
        .ad-details-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        @media (max-width: 768px) {
            .ad-details-section {
                grid-template-columns: 1fr;
            }
        }
        
        .ad-image-container {
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .ad-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background-color: #f8f8f8;
        }
        
        .ad-info-container {
            background-color: white;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .ad-price {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #002f34;
        }
        
        .ad-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #002f34;
        }
        
        .ad-location, .ad-date {
            font-size: 14px;
            color: #777;
            margin-bottom: 5px;
        }
        
        .ad-category {
            display: inline-block;
            background-color: #23e5db;
            color: #002f34;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin: 10px 0;
        }
        
        .ad-description {
            margin: 20px 0;
            line-height: 1.6;
            color: #444;
        }
        
        .seller-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .seller-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: #002f34;
        }
        
        .seller-name {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .contact-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-height: 100px;
            resize: vertical;
        }
        
        .form-textarea:focus {
            border-color: #23e5db;
            outline: none;
        }
        
        .contact-btn {
            width: 100%;
            background-color: #002f34;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .contact-btn:hover {
            background-color: #00474f;
        }
        
        .phone-btn {
            width: 100%;
            background-color: #23e5db;
            color: #002f34;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        
        .phone-icon {
            width: 18px;
            height: 18px;
        }
        
        /* Similar Ads Section */
        .similar-ads-section {
            margin: 40px 0;
        }
        
        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #002f34;
        }
        
        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .ad-card {
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .ad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .ad-card-image {
            height: 180px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .ad-card-info {
            padding: 15px;
        }
        
        .ad-card-price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #002f34;
        }
        
        .ad-card-title {
            font-size: 14px;
            margin-bottom: 5px;
            color: #002f34;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .ad-card-location {
            font-size: 12px;
            color: #777;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="my_ads.php">My Ads</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Sign Up</a>
                <?php endif; ?>
            </div>
            <button class="post-btn" onclick="redirectToPostAd()">+ SELL</button>
        </div>
    </header>

    <main class="container">
        <div class="ad-details-section">
            <div class="ad-image-container">
                <img src="<?php echo !empty($ad['image']) ? $ad['image'] : 'https://via.placeholder.com/800x600?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
                
                <div style="padding: 20px;">
                    <h1 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h1>
                    <p class="ad-location"><?php echo htmlspecialchars($ad['location']); ?></p>
                    <p class="ad-date">Posted on <?php echo date('d M Y', strtotime($ad['created_at'])); ?></p>
                    <span class="ad-category"><?php echo htmlspecialchars($ad['category']); ?></span>
                    
                    <h3 style="margin-top: 20px; margin-bottom: 10px;">Description</h3>
                    <div class="ad-description">
                        <?php echo nl2br(htmlspecialchars($ad['description'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="ad-info-container">
                <div class="ad-price">₹<?php echo number_format($ad['price']); ?></div>
                
                <div class="seller-info">
                    <h3 class="seller-title">Seller Information</h3>
                    <p class="seller-name"><?php echo htmlspecialchars($ad['username']); ?></p>
                    
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ad['user_id']): ?>
                        <button class="phone-btn" onclick="showPhone()">
                            <svg class="phone-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Show Phone Number
                        </button>
                        <div id="phoneNumber" style="display: none; margin-top: 10px; text-align: center; font-size: 18px; font-weight: bold;">
                            <?php echo htmlspecialchars($ad['phone']); ?>
                        </div>
                        
                        <div class="contact-form">
                            <h3 class="seller-title">Contact Seller</h3>
                            
                            <?php if(!empty($error)): ?>
                                <div class="error-message"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if($message_sent): ?>
                                <div class="success-message">Your message has been sent to the seller!</div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $ad_id; ?>" method="POST">
                                <div class="form-group">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea id="message" name="message" class="form-textarea" placeholder="I'm interested in your ad. Is it still available?" required></textarea>
                                </div>
                                
                                <button type="submit" name="send_message" class="contact-btn">Send Message</button>
                            </form>
                        </div>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <p style="margin: 15px 0; text-align: center;">
                            <a href="login.php?redirect=ad_details.php?id=<?php echo $ad_id; ?>" style="color: #23e5db; text-decoration: none; font-weight: bold;">Login</a> to contact the seller
                        </p>
                    <?php elseif($_SESSION['user_id'] == $ad['user_id']): ?>
                        <p style="margin: 15px 0; text-align: center; color: #777;">This is your ad</p>
                        <a href="edit_ad.php?id=<?php echo $ad_id; ?>" style="display: block; text-align: center; margin-top: 10px; color: #23e5db; text-decoration: none; font-weight: bold;">Edit Ad</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if($similar_ads_result->num_rows > 0): ?>
            <div class="similar-ads-section">
                <h2 class="section-title">Similar Ads</h2>
                <div class="ads-grid">
                    <?php while($similar_ad = $similar_ads_result->fetch_assoc()): ?>
                        <div class="ad-card" onclick="redirectToAdDetails(<?php echo $similar_ad['id']; ?>)">
                            <img src="<?php echo !empty($similar_ad['image']) ? $similar_ad['image'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($similar_ad['title']); ?>" class="ad-card-image">
                            <div class="ad-card-info">
                                <div class="ad-card-price">₹<?php echo number_format($similar_ad['price']); ?></div>
                                <h3 class="ad-card-title"><?php echo htmlspecialchars($similar_ad['title']); ?></h3>
                                <p class="ad-card-location"><?php echo htmlspecialchars($similar_ad['location']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if($other_ads_result->num_rows > 0 && $ad['user_id'] != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0)): ?>
            <div class="similar-ads-section">
                <h2 class="section-title">More from this Seller</h2>
                <div class="ads-grid">
                    <?php while($other_ad = $other_ads_result->fetch_assoc()): ?>
                        <div class="ad-card" onclick="redirectToAdDetails(<?php echo $other_ad['id']; ?>)">
                            <img src="<?php echo !empty($other_ad['image']) ? $other_ad['image'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($other_ad['title']); ?>" class="ad-card-image">
                            <div class="ad-card-info">
                                <div class="ad-card-price">₹<?php echo number_format($other_ad['price']); ?></div>
                                <h3 class="ad-card-title"><?php echo htmlspecialchars($other_ad['title']); ?></h3>
                                <p class="ad-card-location"><?php echo htmlspecialchars($other_ad['location']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
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

    <script>
        function redirectToPostAd() {
            <?php if(isset($_SESSION['user_id'])): ?>
                window.location.href = 'post_ad.php';
            <?php else: ?>
                window.location.href = 'login.php?redirect=post_ad.php';
            <?php endif; ?>
        }

        function redirectToAdDetails(adId) {
            window.location.href = 'ad_details.php?id=' + adId;
        }
        
        function showPhone() {
            document.getElementById('phoneNumber').style.display = 'block';
        }
    </script>
</body>
</html>
