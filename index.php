<?php
session_start();
include 'database.php';

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM ads ORDER BY category";
$categories_result = $conn->query($categories_query);

// Filter by category if set
$category_filter = "";
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = $conn->real_escape_string($_GET['category']);
    $category_filter = "WHERE category = '$category'";
}

// Search functionality
$search_filter = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $conn->real_escape_string($_GET['search']);
    if ($category_filter == "") {
        $search_filter = "WHERE title LIKE '%$search%' OR description LIKE '%$search%'";
    } else {
        $search_filter = "AND (title LIKE '%$search%' OR description LIKE '%$search%')";
    }
}

// Get ads with filters
$sql = "SELECT * FROM ads $category_filter $search_filter ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Clone - Buy and Sell Anything</title>
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
        
        /* Search Section */
        .search-section {
            background-color: #ffffff;
            padding: 20px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .search-container {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #002f34;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .category-select {
            padding: 12px 15px;
            border: 2px solid #002f34;
            border-radius: 4px;
            font-size: 16px;
            min-width: 200px;
        }
        
        .search-btn {
            background-color: #002f34;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #00474f;
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
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .ad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 40px 0;
            font-size: 18px;
            color: #777;
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

    <section class="search-section">
        <div class="container">
            <form action="index.php" method="GET">
                <div class="search-container">
                    <input type="text" name="search" class="search-input" placeholder="Find Cars, Mobile Phones, and more..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <select name="category" class="category-select">
                        <option value="">All Categories</option>
                        <?php while($category_row = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $category_row['category']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category_row['category']) ? 'selected' : ''; ?>>
                                <?php echo $category_row['category']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
        </div>
    </section>

    <main class="container">
        <?php if($result->num_rows > 0): ?>
            <div class="ads-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="ad-card" onclick="redirectToAdDetails(<?php echo $row['id']; ?>)">
                        <img src="<?php echo !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="ad-image">
                        <div class="ad-info">
                            <div class="ad-price">₹<?php echo number_format($row['price']); ?></div>
                            <h3 class="ad-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="ad-location"><?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="ad-date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                            <span class="ad-category"><?php echo htmlspecialchars($row['category']); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h2>No ads found</h2>
                <p>Try changing your search criteria or post an ad yourself!</p>
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
    </script>
</body>
</html>
