<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit(); }
include '../includes/db_connect.php';
$stmt = $conn->prepare('SELECT id, title, data, created_at FROM schedules WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$list = [];
while ($row = $res->fetch_assoc()) {
  $row['data'] = json_decode($row['data'], true);
  $list[] = $row;
}
echo json_encode(['success'=>true, 'schedules'=>$list]);
?>