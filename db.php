<?php
// Database connection
$servername = "localhost";
$username = "urkbtgxv0tn9n";
$password = "zon92vfrjcxu";
$dbname = "dbadtc9b6kgr53";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
