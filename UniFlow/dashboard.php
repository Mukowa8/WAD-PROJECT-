<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UniFlow Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <header>
    <h1>UniFlow Timetable</h1>
    <nav>
      <span style="color:#fff; margin-right:12px;">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
      <a href="profile.php">My Profiles</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <section class="dashboard">
    <div class="course-panel">
      <h3>Available Courses</h3>
      <p>Drag a course and drop it into the timetable.</p>
      <div id="courseList"></div>

      <hr>
      <input id="newCourseName" placeholder="New course (e.g. MATH101)">
      <input id="newCourseDuration" type="number" min="1" max="4" placeholder="Duration (hours)">
      <button id="addCourse">Add Course</button>
    </div>

    <div class="timetable">
      <h3>Weekly Schedule</h3>
      <table>
        <thead>
          <tr>
            <th>Time</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
          </tr>
        </thead>
        <tbody id="timetableBody">
        </tbody>
      </table>

      <div class="actions">
        <button onclick="clearDraft()">Clear Draft</button>
        <button class="save-btn" id="saveServerBtn">Save to Server</button>
      </div>
    </div>
  </section>

  <script src="assets/js/app.js"></script>
</body>
</html>