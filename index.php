<?php
if (isset($_GET['action']) && $_GET['action'] === 'read') {
    header('Content-Type: application/json');
    require_once 'dbconfig.php';
    $result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $tasks  = [];
    while ($row = $result->fetch_assoc()) $tasks[] = $row;
    echo json_encode($tasks);
    $conn->close();
    exit;
}

$setup = new mysqli('localhost', 'root', '');
if (!$setup->connect_error) {
    $setup->query("CREATE DATABASE IF NOT EXISTS taskflow_db CHARACTER SET utf8 COLLATE utf8_general_ci");
    $setup->select_db('taskflow_db');
    $setup->query("CREATE TABLE IF NOT EXISTS tasks (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        title       VARCHAR(200)  NOT NULL,
        description TEXT,
        subject     VARCHAR(100)  DEFAULT '',
        priority    ENUM('High','Medium','Low') DEFAULT 'Medium',
        status      ENUM('Pending','Ongoing','Completed') DEFAULT 'Pending',
        due_date    DATE,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $setup->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TaskFlow ✨ Student Task Manager</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ── HEADER ── -->
<header>
  <a href="landing.php" class="logo" style="text-decoration:none">
    <div class="logo-icon">✓</div>
    TaskFlow ✨
  </a>
  <div class="header-stats">
    <div class="stat-pill">
      <div class="stat-dot" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)"></div>
      Pending: <strong id="statPending">0</strong>
    </div>
    <div class="stat-pill">
      <div class="stat-dot" style="background:linear-gradient(135deg,#fdba74,#f97316)"></div>
      Ongoing: <strong id="statOngoing">0</strong>
    </div>
    <div class="stat-pill">
      <div class="stat-dot" style="background:linear-gradient(135deg,#86efac,#22c55e)"></div>
      Done: <strong id="statDone">0</strong>
    </div>
  </div>
</header>

<div class="container">
  <div class="page-header">
    <p class="page-title">🎒 My Tasks</p>
    <p class="page-sub">Stay on top of your assignments, deadlines &amp; progress — all in one colorful place!</p>
  </div>

  <!-- ── APP LAYOUT ── -->
  <div class="app-layout">

    <!-- ══ SIDEBAR ══ -->
    <div class="sidebar">

      <!-- Add Task Form -->
      <div class="card card-add">
        <p class="card-title">🌟 Add New Task</p>
        <form id="addForm">
          <div class="form-group">
            <label for="title">Task Title *</label>
            <input type="text" id="title" name="title" placeholder="e.g. Finish IT report 📝" required>
          </div>
          <div class="form-group" style="position:relative">
            <label for="subject">Subject / Course <span id="subjectLearnBadge" style="display:none;font-size:.65rem;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#6d28d9;border:1.5px solid #c4b5fd;border-radius:50px;padding:.1rem .5rem;font-weight:800;margin-left:.4rem">✨ Learned!</span></label>
            <input type="text" id="subject" name="subject" placeholder="e.g. CPE 3207L 💻" autocomplete="off"
              oninput="handleSubjectInput(this.value)"
              onfocus="handleSubjectInput(this.value)"
              onblur="setTimeout(hideDropdown, 150)">
            <div id="subjectDropdown" class="subject-dropdown" style="display:none"></div>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Any notes or reminders..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="priority">Priority</label>
              <select id="priority" name="priority">
                <option value="High">🔴 High</option>
                <option value="Medium" selected>🟡 Medium</option>
                <option value="Low">🟢 Low</option>
              </select>
            </div>
            <div class="form-group">
              <label for="due_date">Due Date</label>
              <input type="date" id="due_date" name="due_date">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">🚀 Add Task</button>
        </form>
      </div>

      <!-- Motivational Tip Card (sidebar bottom) -->
      <div class="card dash-card dash-card-tip">
        <div class="tip-emoji" id="tipEmoji">💡</div>
        <div class="tip-text" id="tipText">Add your first task to get started!</div>
        <div class="tip-sub"  id="tipSub">Your progress will show here.</div>
      </div>

    </div><!-- /sidebar -->

    <!-- ══ MAIN COLUMN — Task Table ══ -->
    <div class="main-col">
      <div class="card">
        <p class="card-title">📋 All Tasks</p>
        <div class="controls">
          <input type="text" id="filterSearch" placeholder="🔍 Search tasks...">
          <select id="filterStatus">
            <option value="">All Status</option>
            <option value="Pending">📋 Pending</option>
            <option value="Ongoing">⏳ Ongoing</option>
            <option value="Completed">✅ Completed</option>
          </select>
          <select id="filterPriority">
            <option value="">All Priority</option>
            <option value="High">🔴 High</option>
            <option value="Medium">🟡 Medium</option>
            <option value="Low">🟢 Low</option>
          </select>
          <span class="sort-label">Sort:</span>
          <select id="sortCol">
            <option value="due_date">📅 Due Date</option>
            <option value="priority">🔥 Priority</option>
            <option value="status">📊 Status</option>
            <option value="title">🔤 Title</option>
            <option value="created_at">🕐 Date Added</option>
          </select>
          <select id="sortDir">
            <option value="asc">↑ Asc</option>
            <option value="desc">↓ Desc</option>
          </select>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th data-col="title">📝 Task</th>
                <th data-col="subject">📚 Subject</th>
                <th data-col="priority">🔥 Priority</th>
                <th data-col="status">📊 Status</th>
                <th data-col="due_date">📅 Due Date</th>
                <th>⚙️ Actions</th>
              </tr>
            </thead>
            <tbody id="taskTableBody"></tbody>
          </table>
        </div>
        <div id="emptyState" class="empty-state" style="display:none">
          <span class="icon">🎯</span>
          <p>No tasks yet — add your first one and let's go! 🚀</p>
        </div>
      </div>
    </div>

    <!-- ══ DASHBOARD ROW ══ -->
    <div class="dashboard-row">

      <!-- Progress Ring -->
      <div class="dash-card dash-card-progress">
        <div class="dash-card-title">🏆 Overall Progress</div>
        <div class="progress-ring-wrap">
          <svg class="progress-ring" viewBox="0 0 120 120">
            <circle class="ring-bg"   cx="60" cy="60" r="48" fill="none" stroke-width="10"/>
            <circle class="ring-fill" cx="60" cy="60" r="48" fill="none" stroke-width="10"
                    stroke-dasharray="301.6" stroke-dashoffset="301.6" id="ringFill"
                    stroke-linecap="round" transform="rotate(-90 60 60)"/>
          </svg>
          <div class="ring-center">
            <div class="ring-pct" id="ringPct">0%</div>
            <div class="ring-label">done</div>
          </div>
        </div>
        <div class="progress-legend">
          <div class="legend-item"><span class="legend-dot" style="background:#7c3aed"></span><span id="lgPending">0</span>&nbsp;Pending</div>
          <div class="legend-item"><span class="legend-dot" style="background:#f97316"></span><span id="lgOngoing">0</span>&nbsp;Ongoing</div>
          <div class="legend-item"><span class="legend-dot" style="background:#22c55e"></span><span id="lgDone">0</span>&nbsp;Done</div>
        </div>
      </div>

      <!-- Urgent Tasks -->
      <div class="dash-card dash-card-urgent">
        <div class="dash-card-title">🚨 Urgent — Due Soon</div>
        <div id="urgentList" class="urgent-list">
          <div class="no-urgent">✅ Nothing urgent right now!</div>
        </div>
      </div>

      <!-- Subject Breakdown -->
      <div class="dash-card dash-card-subjects">
        <div class="dash-card-title">📚 By Subject</div>
        <div id="subjectBars" class="subject-bars">
          <div class="no-urgent">Add tasks to see your subjects!</div>
        </div>
      </div>

      <!-- Priority Breakdown -->
      <div class="dash-card" style="border-top:4px solid #c4b5fd">
        <div class="dash-card-title">🎯 Priority Split</div>
        <div id="priorityBreakdown" class="subject-bars">
          <div class="no-urgent">No tasks yet!</div>
        </div>
        <div style="margin-top:1rem">
          <div class="dash-card-title" style="margin-bottom:.6rem">📈 Completion Rate</div>
          <div class="subject-bar-track" style="height:10px;margin-bottom:.4rem">
            <div class="subject-bar-fill" id="completionBar" style="width:0%;background:linear-gradient(90deg,#7c3aed,#22c55e)"></div>
          </div>
          <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-align:right" id="completionLabel">0 / 0 tasks</div>
        </div>
      </div>

    </div><!-- /dashboard-row -->

  </div><!-- /app-layout -->
</div><!-- /container -->

<!-- ── EDIT MODAL ── -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit Task</span>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="editForm">
      <input type="hidden" id="edit_id" name="id">
      <div class="form-group"><label>Task Title *</label><input type="text" id="edit_title" name="title" required></div>
      <div class="form-group" style="position:relative">
        <label>Subject / Course</label>
        <input type="text" id="edit_subject" name="subject" placeholder="e.g. CPE 3207L" autocomplete="off"
          oninput="handleSubjectInput(this.value, true)"
          onfocus="handleSubjectInput(this.value, true)"
          onblur="setTimeout(hideDropdown, 150)">
        <div id="editSubjectDropdown" class="subject-dropdown" style="display:none"></div>
      </div>
      <div class="form-group"><label>Description</label><textarea id="edit_description" name="description"></textarea></div>
      <div class="form-row">
        <div class="form-group">
          <label>Priority</label>
          <select id="edit_priority" name="priority">
            <option value="High">🔴 High</option>
            <option value="Medium">🟡 Medium</option>
            <option value="Low">🟢 Low</option>
          </select>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select id="edit_status" name="status">
            <option value="Pending">📋 Pending</option>
            <option value="Ongoing">⏳ Ongoing</option>
            <option value="Completed">✅ Completed</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Due Date</label><input type="date" id="edit_due_date" name="due_date"></div>
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
    </form>
  </div>
</div>

<div id="toast"></div>
<script src="script.js"></script>
</body>
</html>
