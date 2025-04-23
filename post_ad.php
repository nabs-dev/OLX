<?php
session_start();
include 'database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=post_ad.php");
    exit();
}

$error = "";
$success = "";
$categories = array("Mobile Phones", "Cars", "Motorcycles", "Electronics", "Furniture", "Fashion", "Books, Sports & Hobbies", "Properties", "Jobs", "Services", "Pets", "Other");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $location = $conn->real_escape_string($_POST['location']);
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($title) || empty($description) || empty($price) || empty($category) || empty($location)) {
        $error = "All fields are required";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number";
    } else {
        // Handle image upload
        $image_path = "";
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if(in_array($file_ext, $allowed_types)) {
                // Create unique filename
                $new_file_name = uniqid('ad_', true) . '.' . $file_ext;
                $upload_path = 'uploads/' . $new_file_name;
                
                // Create uploads directory if it doesn't exist
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                if(move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    $error = "Failed to upload image";
                }
            } else {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed";
            }
        }
        
        if(empty($error)) {
            // Insert ad into database
            $insert_query = "INSERT INTO ads (user_id, title, description, price, category, image, location) 
                            VALUES ('$user_id', '$title', '$description', '$price', '$category', '$image_path', '$location')";
            
            if ($conn->query($insert_query) === TRUE) {
                $ad_id = $conn->insert_id;
                $success = "Your ad has been posted successfully!";
                
                // Redirect to the ad details page
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'ad_details.php?id=$ad_id';
                    }, 2000);
                </script>";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post an Ad - OLX Clone</title>
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
        
        /* Form Styles */
        .post-ad-container {
            max-width: 800px;
            margin: 40px auto;
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
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: #23e5db;
            outline: none;
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
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
        
        .image-preview {
            width: 100%;
            max-height: 300px;
            border-radius: 4px;
            margin-top: 10px;
            display: none;
            object-fit: contain;
            border: 1px solid #ddd;
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
            <div class="nav-links">
                <a href="my_ads.php">My Ads</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="post-ad-container">
            <h2 class="form-title">Post a New Ad</h2>
            
            <?php if(!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" id="title" name="title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price" class="form-label">Price (₹)</label>
                    <input type="number" id="price" name="price" class="form-input" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-textarea" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" id="location" name="location" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/*" onchange="previewImage(this)">
                    <img id="imagePreview" class="image-preview">
                </div>
                
                <button type="submit" class="submit-btn">Post Ad</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="copyright">
                <p>&copy; 2023 OLX Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function previewImage(input) {
            var preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
