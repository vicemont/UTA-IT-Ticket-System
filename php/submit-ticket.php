<?php
// Database Connection Configuration
$host = "localhost";
$db_user = "root";       // Replace with your database username
$db_pass = "";           // Replace with your database password
$db_name = "uta_support"; // Replace with your actual database name

// Establish connection using PDO (safer and modern)
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab and sanitize inputs from the form 'name' attributes
    $title = trim($_POST['title']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $status = isset($_POST['status']) ? $_POST['status'] : 'open'; // Defaults to open if hidden input fails

    // Check if optional due date was provided; if empty string, map it to database NULL
    $dueDate = !empty($_POST['dueDate']) ? $_POST['dueDate'] : null;

    // Basic validation check
    if (!empty($title) && !empty($description) && !empty($priority)) {
        
        // Prepare an SQL statement (Prevents SQL Injection attacks)
        $sql = "INSERT INTO tickets (title, category, description, priority, status, dueDate) 
        VALUES (:title, :category, :description, :priority, :status, :dueDate)";
        $stmt = $conn->prepare($sql);
        
        try {
            // Execute and bind parameters
            $stmt->execute([
                ':title'       => $title,
                ':category'    => $category,
                ':description' => $description,
                ':priority'    => $priority,
                ':status'      => $status,
                ':dueDate'     => $dueDate
            ]);
            
            // Redirect back to your dashboard with a success indicator
            header("Location: /userdashboard.html?status=success");
            exit();
            
        } catch(PDOException $e) {
            echo "Error saving ticket: " . $e->getMessage();
        }
        
    } else {
        echo "Please fill out all required fields.";
    }
} else {
    // Redirect away if someone tries to access this script directly without submitting the form
    header("Location: userdashboard.html");
    exit();
}
?>
