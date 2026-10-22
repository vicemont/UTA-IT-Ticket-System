<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$host = "localhost";
$db_user = "root";       
$db_pass = "";           
$db_name = "uta_support"; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $open = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();
    $progress = $conn->query("SELECT COUNT(*) FROM tickets WHERE status = 'in_progress'")->fetchColumn();
    $resolved = $conn->query("SELECT COUNT(*) FROM tickets WHERE status IN ('resolved', 'closed')")->fetchColumn();

    echo json_encode([
        'open'        => (int)$open,
        'in_progress' => (int)$progress,
        'resolved'    => (int)$resolved
    ]);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
//not working yet at all
?>
