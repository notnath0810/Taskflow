// ============================================
// TaskFlow - script.js
// ============================================

let tasks   = [];
let sortCol = 'due_date';
let sortDir = 'asc';

const taskTable    = document.getElementById('taskTableBody');
const emptyState   = document.getElementById('emptyState');
const modal        = document.getElementById('editModal');
const toast        = document.getElementById('toast');
const filterSearch = document.getElementById('filterSearch');
const filterStatus = document.getElementById('filterStatus');
const filterPrio   = document.getElementById('filterPriority');
const sortColSel   = document.getElementById('sortCol');
const sortDirSel   = document.getElementById('sortDir');
const statPending  = document.getElementById('statPending');
const statOngoing  = document.getElementById('statOngoing');
const statDone     = document.getElementById('statDone');

document.addEventListener('DOMContentLoaded', () => {
  loadTasks();
  document.getElementById('addForm').addEventListener('submit', e => { e.preventDefault(); addTask(); });
  document.getElementById('editForm').addEventListener('submit', e => { e.preventDefault(); saveEdit(); });
  filterSearch.addEventListener('input',  renderTable);
  filterStatus.addEventListener('change', renderTable);
  filterPrio.addEventListener('change',   renderTable);
  sortColSel.addEventListener('change',   () => { sortCol = sortColSel.value; renderTable(); });
  sortDirSel.addEventListener('change',   () => { sortDir = sortDirSel.value; renderTable(); });
  document.querySelectorAll('thead th[data-col]').forEach(th => {
    th.addEventListener('click', () => {
      sortDir = sortCol === th.dataset.col ? (sortDir === 'asc' ? 'desc' : 'asc') : 'asc';
      sortCol = th.dataset.col;
      sortColSel.value = sortCol;
      sortDirSel.value = sortDir;
      renderTable();
    });
  });
});

// ── LOAD ──────────────────────────────────
function loadTasks() {
  fetch('index.php?action=read')
    .then(r => r.json())
    .then(data => { tasks = data; renderTable(); updateStats(); updateDashboard(); })
    .catch(() => showToast('Failed to load tasks.', 'error'));
}

// ── RENDER TABLE ──────────────────────────
function renderTable() {
  let filtered = [...tasks];
  const q = filterSearch.value.toLowerCase();
  if (q) filtered = filtered.filter(t =>
    t.title.toLowerCase().includes(q) ||
    t.subject.toLowerCase().includes(q) ||
    (t.description||'').toLowerCase().includes(q));
  const st = filterStatus.value;
  if (st) filtered = filtered.filter(t => t.status === st);
  const pr = filterPrio.value;
  if (pr) filtered = filtered.filter(t => t.priority === pr);

  filtered.sort((a, b) => {
    let va = a[sortCol] ?? '', vb = b[sortCol] ?? '';
    if (sortCol === 'priority') { const o={High:0,Medium:1,Low:2}; va=o[va]??9; vb=o[vb]??9; }
    if (sortCol === 'status')   { const o={Pending:0,Ongoing:1,Completed:2}; va=o[va]??9; vb=o[vb]??9; }
    if (va < vb) return sortDir === 'asc' ? -1 : 1;
    if (va > vb) return sortDir === 'asc' ?  1 : -1;
    return 0;
  });

  document.querySelectorAll('thead th[data-col]').forEach(th => {
    th.classList.remove('sort-asc','sort-desc');
    if (th.dataset.col === sortCol) th.classList.add(sortDir==='asc'?'sort-asc':'sort-desc');
  });

  if (filtered.length === 0) { taskTable.innerHTML = ''; emptyState.style.display = 'block'; return; }
  emptyState.style.display = 'none';
  taskTable.innerHTML = filtered.map(rowHTML).join('');
}

function rowHTML(t) {
  const priBadge = `<span class="badge badge-${t.priority.toLowerCase()}">${priorityIcon(t.priority)} ${t.priority}</span>`;
  const stBadge  = `<span class="badge badge-${t.status.toLowerCase().replace(' ','')}">${statusIcon(t.status)} ${t.status}</span>`;
  const due      = formatDue(t.due_date);
  const isDone   = t.status === 'Completed';
  return `<tr>
    <td>
      <div class="task-title-cell" style="${isDone?'text-decoration:line-through;opacity:.55':''}">${escHtml(t.title)}</div>
      ${t.description?`<div class="task-desc">${escHtml(t.description)}</div>`:''}
    </td>
    <td>${escHtml(t.subject)}</td>
    <td>${priBadge}</td>
    <td>${stBadge}</td>
    <td>${due}</td>
    <td>
      <div class="actions">
        <button class="btn btn-edit btn-sm"     onclick="openEdit(${t.id})">✏️ Edit</button>
        ${!isDone?`<button class="btn btn-complete btn-sm" onclick="markComplete(${t.id})">✅ Done</button>`:''}
        <button class="btn btn-danger btn-sm"   onclick="deleteTask(${t.id},'${escHtml(t.title)}')">🗑</button>
      </div>
    </td>
  </tr>`;
}

// ── ADD ───────────────────────────────────
function addTask() {
  const form = document.getElementById('addForm');
  fetch('insert.php', { method:'POST', body: new FormData(form) })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('🚀 Task added!','success'); form.reset(); loadTasks(); }
      else showToast(res.message,'error');
    }).catch(() => showToast('Server error.','error'));
}

// ── DELETE ────────────────────────────────
function deleteTask(id, title) {
  if (!confirm(`Delete "${title}"? This cannot be undone.`)) return;
  const fd = new FormData(); fd.append('id', id);
  fetch('delete.php', { method:'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('🗑 Task deleted.','success'); loadTasks(); }
      else showToast(res.message,'error');
    }).catch(() => showToast('Server error.','error'));
}

// ── MARK COMPLETE ─────────────────────────
function markComplete(id) {
  const t = tasks.find(x => x.id == id);
  if (!t) return;
  const fd = new FormData();
  fd.append('id',t.id); fd.append('title',t.title); fd.append('description',t.description||'');
  fd.append('subject',t.subject||''); fd.append('priority',t.priority);
  fd.append('due_date',t.due_date||''); fd.append('status','Completed');
  fetch('update.php', { method:'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('🎉 Task completed! Great job!','success'); loadTasks(); }
      else showToast(res.message,'error');
    }).catch(() => showToast('Server error.','error'));
}

// ── EDIT MODAL ────────────────────────────
function openEdit(id) {
  const t = tasks.find(x => x.id == id);
  if (!t) return;
  document.getElementById('edit_id').value          = t.id;
  document.getElementById('edit_title').value       = t.title;
  document.getElementById('edit_description').value = t.description;
  document.getElementById('edit_subject').value     = t.subject;
  document.getElementById('edit_priority').value    = t.priority;
  document.getElementById('edit_due_date').value    = t.due_date;
  document.getElementById('edit_status').value      = t.status;
  modal.classList.add('open');
}
function closeModal() { modal.classList.remove('open'); }
document.getElementById('editModal').addEventListener('click', e => { if (e.target===modal) closeModal(); });

function saveEdit() {
  fetch('update.php', { method:'POST', body: new FormData(document.getElementById('editForm')) })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('✏️ Task updated!','success'); closeModal(); loadTasks(); }
      else showToast(res.message,'error');
    }).catch(() => showToast('Server error.','error'));
}

// ── HEADER STATS ─────────────────────────
function updateStats() {
  statPending.textContent = tasks.filter(t => t.status==='Pending').length;
  statOngoing.textContent = tasks.filter(t => t.status==='Ongoing').length;
  statDone.textContent    = tasks.filter(t => t.status==='Completed').length;
}

// ── DASHBOARD ─────────────────────────────
function updateDashboard() {
  const total    = tasks.length;
  const pending  = tasks.filter(t => t.status==='Pending').length;
  const ongoing  = tasks.filter(t => t.status==='Ongoing').length;
  const done     = tasks.filter(t => t.status==='Completed').length;
  const pct      = total === 0 ? 0 : Math.round((done / total) * 100);

  // ── Progress ring
  const circumference = 2 * Math.PI * 48; // 301.6
  const offset = circumference - (pct / 100) * circumference;
  const ring = document.getElementById('ringFill');
  if (ring) {
    ring.style.strokeDasharray  = circumference;
    ring.style.strokeDashoffset = offset;
    // Colour shifts from violet → green as progress grows
    ring.style.stroke = pct >= 80 ? '#22c55e' : pct >= 50 ? '#f97316' : '#7c3aed';
  }
  const ringPct = document.getElementById('ringPct');
  if (ringPct) ringPct.textContent = pct + '%';

  // Legend counts
  const lgP = document.getElementById('lgPending');
  const lgO = document.getElementById('lgOngoing');
  const lgD = document.getElementById('lgDone');
  if (lgP) lgP.textContent = pending;
  if (lgO) lgO.textContent = ongoing;
  if (lgD) lgD.textContent = done;

  // ── Urgent list (not completed, due within 3 days or overdue)
  const urgentList = document.getElementById('urgentList');
  if (urgentList) {
    const today = new Date(); today.setHours(0,0,0,0);
    const urgent = tasks
      .filter(t => t.status !== 'Completed' && t.due_date && t.due_date !== '0000-00-00')
      .map(t => ({ ...t, diff: Math.ceil((new Date(t.due_date+'T00:00:00') - today) / 86400000) }))
      .filter(t => t.diff <= 3)
      .sort((a,b) => a.diff - b.diff)
      .slice(0, 4);

    if (urgent.length === 0) {
      urgentList.innerHTML = '<div class="no-urgent">✅ Nothing urgent right now!</div>';
    } else {
      urgentList.innerHTML = urgent.map(t => {
        const dotColor = t.diff < 0 ? '#e11d48' : t.diff === 0 ? '#f97316' : '#fbbf24';
        const dueLabel = t.diff < 0
          ? `${Math.abs(t.diff)}d overdue ⚠️`
          : t.diff === 0 ? 'Due today! ⚡'
          : `${t.diff}d left`;
        return `<div class="urgent-item">
          <div class="urgent-item-dot" style="background:${dotColor}"></div>
          <div class="urgent-item-title">${escHtml(t.title)}</div>
          <div class="urgent-item-due">${dueLabel}</div>
        </div>`;
      }).join('');
    }
  }

  // ── Subject bars
  const subjectBars = document.getElementById('subjectBars');
  if (subjectBars) {
    const subjectMap = {};
    tasks.forEach(t => {
      const s = t.subject || 'No subject';
      if (!subjectMap[s]) subjectMap[s] = { total:0, done:0 };
      subjectMap[s].total++;
      if (t.status === 'Completed') subjectMap[s].done++;
    });
    const subjects = Object.entries(subjectMap).sort((a,b) => b[1].total - a[1].total).slice(0,5);
    const maxCount = subjects.length ? Math.max(...subjects.map(s => s[1].total)) : 1;
    const barColors = ['#7c3aed','#ec4899','#06b6d4','#f97316','#84cc16'];

    if (subjects.length === 0) {
      subjectBars.innerHTML = '<div class="no-urgent" style="margin-top:.5rem">Add tasks to see your subjects!</div>';
    } else {
      subjectBars.innerHTML = subjects.map(([name, data], i) => {
        const width = Math.round((data.total / maxCount) * 100);
        return `<div class="subject-bar-row">
          <div class="subject-bar-label">
            <span class="subject-bar-name">${escHtml(name)}</span>
            <span class="subject-bar-count">${data.done}/${data.total}</span>
          </div>
          <div class="subject-bar-track">
            <div class="subject-bar-fill" style="width:${width}%;background:${barColors[i%barColors.length]}"></div>
          </div>
        </div>`;
      }).join('');
    }
  }

  // ── Motivational tip card
  const tipEmoji = document.getElementById('tipEmoji');
  const tipText  = document.getElementById('tipText');
  const tipSub   = document.getElementById('tipSub');
  if (tipText) {
    let emoji, headline, sub;
    if (total === 0) {
      emoji = '🎯'; headline = "Let's get started!"; sub = 'Add your first task above.';
    } else if (pct === 100) {
      emoji = '🥳'; headline = 'You finished everything!'; sub = 'Absolute legend. Take a break! 🎉';
    } else if (pct >= 75) {
      emoji = '🔥'; headline = 'Almost there!'; sub = `Just ${total - done} task${total-done>1?'s':''} left — you got this!`;
    } else if (pct >= 50) {
      emoji = '⚡'; headline = 'Halfway done!'; sub = `Keep the momentum going — ${done} of ${total} complete.`;
    } else if (done > 0) {
      emoji = '💪'; headline = 'Good progress!'; sub = `${done} task${done>1?'s':''} done. Keep it up!`;
    } else if (ongoing > 0) {
      emoji = '⏳'; headline = 'Tasks in progress!'; sub = `You have ${ongoing} task${ongoing>1?'s':''} ongoing right now.`;
    } else {
      emoji = '📋'; headline = `${pending} task${pending>1?'s':''} pending.`; sub = 'Start with the highest priority first!';
    }
    tipEmoji.textContent = emoji;
    tipText.textContent  = headline;
    tipSub.textContent   = sub;
  }
}

// ── TOAST ─────────────────────────────────
let toastTimer;
function showToast(msg, type='success') {
  toast.textContent = msg;
  toast.className   = `show ${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
}

// ── HELPERS ───────────────────────────────
function escHtml(str) {
  return String(str??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function priorityIcon(p) { return p==='High'?'🔴':p==='Medium'?'🟡':'🟢'; }
function statusIcon(s)   { return s==='Completed'?'✅':s==='Ongoing'?'⏳':'📋'; }
function formatDue(dateStr) {
  if (!dateStr || dateStr==='0000-00-00') return '<span class="due-date">—</span>';
  const due=new Date(dateStr+'T00:00:00'), today=new Date();
  today.setHours(0,0,0,0);
  const diff=Math.ceil((due-today)/86400000);
  const label=due.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  if (diff<0)  return `<span class="due-date due-overdue">⚠ ${label}</span>`;
  if (diff<=3) return `<span class="due-date due-soon">⚡ ${label}</span>`;
  return `<span class="due-date">${label}</span>`;
}
