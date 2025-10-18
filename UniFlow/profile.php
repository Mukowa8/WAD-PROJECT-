<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}
include 'includes/db_connect.php';
$user_id = $_SESSION['user_id'];

$schedules = [];
$stmt = $conn->prepare('SELECT id, title, data, created_at FROM schedules WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $row['data'] = json_decode($row['data'], true);
  $schedules[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Schedules - UniFlow</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <header>
    <h1>UniFlow</h1>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main style="padding:20px;">
    <h2>Saved Schedules</h2>
    <div id="savedList">
      <?php foreach ($schedules as $s): ?>
        <div class="saved-item">
          <div>
            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
            <div class="meta"><?php echo $s['created_at']; ?></div>
          </div>
          <div>
            <button class="loadBtn" data-id="<?php echo $s['id']; ?>">Load</button>
            <button class="delBtn" data-id="<?php echo $s['id']; ?>">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.loadBtn').forEach(b=>{
      b.addEventListener('click', async ()=>{
        const id = b.dataset.id;
        const res = await fetch('api/get_schedules.php');
        const json = await res.json();
        if (json.success) {
          const sch = json.schedules.find(x=>x.id==id);
          if (sch) {
            localStorage.setItem('uniflow_draft', JSON.stringify(sch.data));
            window.location='dashboard.php';
          }
        }
      });
    });
    document.querySelectorAll('.delBtn').forEach(b=>{
      b.addEventListener('click', async ()=>{
        if (!confirm('Delete this schedule?')) return;
        const id = b.dataset.id;
        const res = await fetch('api/delete_schedule.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
        const json = await res.json();
        if (json.success) window.location.reload(); else alert(json.error||'Delete failed');
      });
    });
  });
  </script>
</body>
</html>