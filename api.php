<?php
header("Content-Type: application/json");

$host = 'localhost';
$db = 'employees';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

 if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET request: Fetch data from accounts and profiles
        $stmt = $pdo->query("SELECT 
                                accounts.username, accounts.pass, accounts.email, 
                                profile.full_name, profile.phone_number, profile.address 
                             FROM accounts 
                             LEFT JOIN profile ON accounts.account_id = profile.account_id");
        $data = $stmt->fetchAll();

        // Encode each record separately
        $output = [];
        foreach ($data as $record) {
            $output[] = json_encode($record);
        }
        echo implode("\n", $output);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // POST request: Insert data into accounts table
        $sql = "INSERT INTO accounts (username, pass, email) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['username'], $input['pass'], $input['email']]);
        
        // Get the last inserted account_id
        $account_id = $pdo->lastInsertId();

        // POST request: Insert data into profile table
        $sql = "INSERT INTO profile (account_id, full_name, phone_number, address) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$account_id, $input['full_name'], $input['phone_number'], $input['address']]);
        
        echo json_encode(['message' => 'User added successfully']);
    }
?>