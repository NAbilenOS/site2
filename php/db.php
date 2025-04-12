<?php
$host = "localhost";
$dbname = "ecommerce_project";
$username = "root";
$password = "Nabilmysql";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(["success" => false, "error" => "Erreur de connexion à la base de données"]));
}
