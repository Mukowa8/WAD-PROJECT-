<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit(); }
$raw = json_decode(file_get_contents('php://input'), true);
$id = intval($raw['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit(); }
include '../includes/db_connect.php';
$stmt = $conn->prepare('DELETE FROM schedules WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
if ($stmt->execute()) echo json_encode(['success'=>true]); else { http_response_code(500); echo json_encode(['error'=>'Delete failed']); }
?>