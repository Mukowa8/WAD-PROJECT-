<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit(); }
$raw = json_decode(file_get_contents('php://input'), true);
$title = trim($raw['title'] ?? 'My Schedule');
$data = $raw['data'] ?? null;
if (!$data) { http_response_code(400); echo json_encode(['error'=>'No schedule data']); exit(); }
include '../includes/db_connect.php';
$stmt = $conn->prepare('INSERT INTO schedules (user_id, title, data) VALUES (?, ?, ?)');
$json = json_encode($data);
$stmt->bind_param('iss', $_SESSION['user_id'], $title, $json);
if ($stmt->execute()) {
  echo json_encode(['success'=>true, 'id'=>$stmt->insert_id]);
} else {
  http_response_code(500);
  echo json_encode(['error'=>'Save failed']);
}
?>