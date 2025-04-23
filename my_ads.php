<?php
session_start();
include 'database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my_ads.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle ad deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $ad_id = $conn->real_escape_string($_GET['delete']);
    
    // Check if ad belongs to user
    $check_query = "SELECT * FROM ads WHERE id = '$ad_id' AND user_id = '$user_id'";
    $check_result = $conn->query($check_query);
    
    if($check_result->num_rows > 0) {
        $ad = $check_result->fetch_assoc();
        
        // Delete image file if exists
        if(!empty($ad['image']) && file_exists($ad['image'])) {
            unlink($ad['image']);
        }
        
        // Delete ad from database
        $delete_query = "DELETE FROM ads WHERE id = '$ad_id'";
        $conn->query($delete_query);
        
        // Delete related messages
        $delete_messages_query = "DELETE FROM messages WHERE ad_id = '$ad_id'";
        $conn->query($delete_messages_query);
        
        // Redirect to refresh page
        header("Location: my_ads.php?deleted=1");
        exit();
    }
}

// Get user's ads
$ads_query = "SELECT * FROM ads WHERE user_id = '$user_id' ORDER BY created_at DESC";
$ads_result = $conn->query($ads_query);

// Get user info
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads - OLX Clone</title>
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
        
        /* User Info */
        .user-info {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .user-info h3 {
            margin-bottom: 15px;
            color: #002f34;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .user-detail {
            margin-bottom: 10px;
        }
        
        .user-detail-label {
            font-weight: 500;
            color: #777;
            margin-bottom: 5px;
        }
        
        .user-detail-value {
            color: #002f34;
        }
        
        /* Ads Grid */
        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .ad-card {
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .ad-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .ad-info {
            padding: 15px;
        }
        
        .ad-price {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #002f34;
        }
        
        .ad-title {
            font-size: 16px;
            margin-bottom: 8px;
            color: #002f34;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .ad-location {
            font-size: 14px;
            color: #777;
            margin-bottom: 5px;
        }
        
        .ad-date {
            font-size: 12px;
            color: #999;
        }
        
        .ad-category {
            display: inline-block;
            background-color: #23e5db;
            color: #002f34;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .ad-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .ad-action-btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .view-btn {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .view-btn:hover {
            background-color: #c8e6c9;
        }
        
        .edit-btn {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .edit-btn:hover {
            background-color: #bbdefb;
        }
        
        .delete-btn {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .delete-btn:hover {
            background-color: #ffcdd2;
        }
        
        /* No Ads */
        .no-ads {
            text-align: center;
            padding: 40px 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .no-ads h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #002f34;
        }
        
        .no-ads p {
            color: #777;
            margin-bottom: 20px;
        }
        
        /* Alert Message */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
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
        
        /* Confirmation Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .modal-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #002f34;
        }
        
        .modal-text {
            margin-bottom: 20px;
            color: #444;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .cancel-btn {
            background-color: #f5f5f5;
            color: #444;
            border: 1px solid #ddd;
        }
        
        .cancel-btn:hover {
            background-color: #e0e0e0;
        }
        
        .confirm-btn {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .confirm-btn:hover {
            background-color: #ffcdd2;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">OLX<span>Clone</span></a>
            <div class="nav-links">
                <a href="my_ads.php" class="active">My Ads</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
            <button class="post-btn" onclick="window.location.href='post_ad.php'">+ SELL</button>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <h1 class="page-title">My Ads</h1>
        </div>
        
        <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                Ad has been successfully deleted.
            </div>
        <?php endif; ?>
        
        <div class="user-info">
            <h3>Account Information</h3>
            <div class="user-details">
                <div class="user-detail">
                    <div class="user-detail-label">Username</div>
                    <div class="user-detail-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="user-detail">
                    <div class="user-detail-label">Email</div>
                    <div class="user-detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="user-detail">
                    <div class="user-detail-label">Phone</div>
                    <div class="user-detail-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></div>
                </div>
                <div class="user-detail">
                    <div class="user-detail-label">Member Since</div>
                    <div class="user-detail-value"><?php echo date('d M Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>
        
        <?php if($ads_result->num_rows > 0): ?>
            <div class="ads-grid">
                <?php while($ad = $ads_result->fetch_assoc()): ?>
                    <div class="ad-card">
                        <img src="<?php echo !empty($ad['image']) ? $ad['image'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
                        <div class="ad-info">
                            <div class="ad-price">₹<?php echo number_format($ad['price']); ?></div>
                            <h3 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <p class="ad-location"><?php echo htmlspecialchars($ad['location']); ?></p>
                            <p class="ad-date">Posted on <?php echo date('d M Y', strtotime($ad['created_at'])); ?></p>
                            <span class="ad-category"><?php echo htmlspecialchars($ad['category']); ?></span>
                            
                            <div class="ad-actions">
                                <a href="ad_details.php?id=<?php echo $ad['id']; ?>" class="ad-action-btn view-btn">View</a>
                                <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="ad-action-btn edit-btn">Edit</a>
                                <button class="ad-action-btn delete-btn" onclick="confirmDelete(<?php echo $ad['id']; ?>, '<?php echo addslashes($ad['title']); ?>')">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-ads">
                <h3>You haven't posted any ads yet</h3>
                <p>Start selling your items by posting your first ad!</p>
                <button class="post-btn" onclick="window.location.href='post_ad.php'">+ Post an Ad</button>
            </div>
        <?php endif; ?>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Deletion</h3>
            <p class="modal-text" id="deleteModalText">Are you sure you want to delete this ad?</p>
            <div class="modal-actions">
                <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="modal-btn confirm-btn" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

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
        // Delete confirmation modal
        function confirmDelete(adId, adTitle) {
            document.getElementById('deleteModalText').textContent = `Are you sure you want to delete "${adTitle}"?`;
            document.getElementById('confirmDeleteBtn').onclick = function() {
                window.location.href = 'my_ads.php?delete=' + adId;
            };
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
