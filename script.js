// ============================================
// TaskFlow - script.js  (clean unified version)
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

// ── SINGLE DOMContentLoaded ───────────────
document.addEventListener('DOMContentLoaded', () => {
  loadTasks();

  // Add form
  document.getElementById('addForm').addEventListener('submit', e => {
    e.preventDefault();
    // Learn the subject first, then add
    const subjectVal = document.getElementById('subject').value.trim();
    const isNewSubject = subjectVal.length > 1 && !getLearnedSubjects()[subjectVal];
    if (subjectVal.length > 1) learnSubject(subjectVal);
    addTask(isNewSubject ? subjectVal : null);
  });

  // Edit form
  document.getElementById('editForm').addEventListener('submit', e => {
    e.preventDefault();
    const subjectVal = document.getElementById('edit_subject').value.trim();
    if (subjectVal.length > 1) learnSubject(subjectVal);
    saveEdit();
  });

  // Filters & sort
  filterSearch.addEventListener('input',  renderTable);
  filterStatus.addEventListener('change', renderTable);
  filterPrio.addEventListener('change',   renderTable);
  sortColSel.addEventListener('change',   () => { sortCol = sortColSel.value; renderTable(); });
  sortDirSel.addEventListener('change',   () => { sortDir = sortDirSel.value; renderTable(); });

  // Column header click-to-sort
  document.querySelectorAll('thead th[data-col]').forEach(th => {
    th.addEventListener('click', () => {
      sortDir = sortCol === th.dataset.col ? (sortDir === 'asc' ? 'desc' : 'asc') : 'asc';
      sortCol = th.dataset.col;
      sortColSel.value = sortCol;
      sortDirSel.value = sortDir;
      renderTable();
    });
  });

  // Close dropdown on Escape
  document.addEventListener('keydown', e => { if (e.key === 'Escape') hideDropdown(); });
});

// ── LOAD TASKS ────────────────────────────
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
  if (filterStatus.value) filtered = filtered.filter(t => t.status === filterStatus.value);
  if (filterPrio.value)   filtered = filtered.filter(t => t.priority === filterPrio.value);

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
  const isDone = t.status === 'Completed';
  return `<tr>
    <td>
      <div class="task-title-cell" style="${isDone?'text-decoration:line-through;opacity:.55':''}">${escHtml(t.title)}</div>
      ${t.description?`<div class="task-desc">${escHtml(t.description)}</div>`:''}
    </td>
    <td>${escHtml(t.subject)}</td>
    <td><span class="badge badge-${t.priority.toLowerCase()}">${priorityIcon(t.priority)} ${t.priority}</span></td>
    <td><span class="badge badge-${t.status.toLowerCase().replace(' ','')}">${statusIcon(t.status)} ${t.status}</span></td>
    <td>${formatDue(t.due_date)}</td>
    <td>
      <div class="actions">
        <button class="btn btn-edit btn-sm"     onclick="openEdit(${t.id})">✏️ Edit</button>
        ${!isDone?`<button class="btn btn-complete btn-sm" onclick="markComplete(${t.id})">✅ Done</button>`:''}
        <button class="btn btn-danger btn-sm"   onclick="deleteTask(${t.id},'${escHtml(t.title)}')">🗑</button>
      </div>
    </td>
  </tr>`;
}

// ── ADD TASK (Create) ─────────────────────
function addTask(newSubject) {
  const form = document.getElementById('addForm');
  fetch('insert.php', { method:'POST', body: new FormData(form) })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const msg = newSubject
          ? '🚀 Task added! 🧠 Learned "' + newSubject + '"'
          : '🚀 Task added!';
        showToast(msg, 'success');
        form.reset();
        loadTasks();
      } else showToast(res.message,'error');
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
  fd.append('id',t.id); fd.append('title',t.title);
  fd.append('description',t.description||''); fd.append('subject',t.subject||'');
  fd.append('priority',t.priority); fd.append('due_date',t.due_date||'');
  fd.append('status','Completed');
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
  const total   = tasks.length;
  const pending = tasks.filter(t => t.status==='Pending').length;
  const ongoing = tasks.filter(t => t.status==='Ongoing').length;
  const done    = tasks.filter(t => t.status==='Completed').length;
  const pct     = total === 0 ? 0 : Math.round((done / total) * 100);

  // Progress ring
  const circumference = 2 * Math.PI * 48;
  const ring = document.getElementById('ringFill');
  if (ring) {
    ring.style.strokeDasharray  = circumference;
    ring.style.strokeDashoffset = circumference - (pct / 100) * circumference;
    ring.style.stroke = pct >= 80 ? '#22c55e' : pct >= 50 ? '#f97316' : '#7c3aed';
  }
  const ringPct = document.getElementById('ringPct');
  if (ringPct) ringPct.textContent = pct + '%';
  const lgP = document.getElementById('lgPending'); if (lgP) lgP.textContent = pending;
  const lgO = document.getElementById('lgOngoing'); if (lgO) lgO.textContent = ongoing;
  const lgD = document.getElementById('lgDone');    if (lgD) lgD.textContent = done;

  // Urgent list
  const urgentList = document.getElementById('urgentList');
  if (urgentList) {
    const today = new Date(); today.setHours(0,0,0,0);
    const urgent = tasks
      .filter(t => t.status !== 'Completed' && t.due_date && t.due_date !== '0000-00-00')
      .map(t => ({ ...t, diff: Math.ceil((new Date(t.due_date+'T00:00:00') - today) / 86400000) }))
      .filter(t => t.diff <= 3)
      .sort((a,b) => a.diff - b.diff).slice(0, 4);
    urgentList.innerHTML = urgent.length === 0
      ? '<div class="no-urgent">✅ Nothing urgent right now!</div>'
      : urgent.map(t => {
          const dotColor = t.diff < 0 ? '#e11d48' : t.diff === 0 ? '#f97316' : '#fbbf24';
          const dueLabel = t.diff < 0 ? `${Math.abs(t.diff)}d overdue ⚠️` : t.diff === 0 ? 'Due today! ⚡' : `${t.diff}d left`;
          return `<div class="urgent-item">
            <div class="urgent-item-dot" style="background:${dotColor}"></div>
            <div class="urgent-item-title">${escHtml(t.title)}</div>
            <div class="urgent-item-due">${dueLabel}</div>
          </div>`;
        }).join('');
  }

  // Subject bars
  const subjectBars = document.getElementById('subjectBars');
  if (subjectBars) {
    const map = {};
    tasks.forEach(t => {
      const s = (t.subject||'').trim() || 'No Subject';
      if (!map[s]) map[s] = { total:0, done:0 };
      map[s].total++;
      if (t.status==='Completed') map[s].done++;
    });
    const subjects = Object.entries(map).sort((a,b)=>b[1].total-a[1].total).slice(0,5);
    const maxCount = subjects.length ? Math.max(...subjects.map(s=>s[1].total)) : 1;
    const barColors = ['#7c3aed','#ec4899','#06b6d4','#f97316','#84cc16'];
    subjectBars.innerHTML = subjects.length === 0
      ? '<div class="no-urgent">Add tasks to see your subjects!</div>'
      : subjects.map(([name, data], i) => `
          <div class="subject-bar-row">
            <div class="subject-bar-label">
              <span class="subject-bar-name">${escHtml(name)}</span>
              <span class="subject-bar-count">${data.done}/${data.total}</span>
            </div>
            <div class="subject-bar-track">
              <div class="subject-bar-fill" style="width:${Math.round((data.total/maxCount)*100)}%;background:${barColors[i%barColors.length]}"></div>
            </div>
          </div>`).join('');
  }

  // Priority breakdown
  const priBreakdown = document.getElementById('priorityBreakdown');
  if (priBreakdown) {
    const priData = [
      { label:'🔴 High',   count: tasks.filter(t=>t.priority==='High').length,   color:'#f94144' },
      { label:'🟡 Medium', count: tasks.filter(t=>t.priority==='Medium').length, color:'#fbbf24' },
      { label:'🟢 Low',    count: tasks.filter(t=>t.priority==='Low').length,    color:'#22c55e' },
    ];
    priBreakdown.innerHTML = total === 0
      ? '<div class="no-urgent">No tasks yet!</div>'
      : priData.map(p => `
          <div class="subject-bar-row">
            <div class="subject-bar-label">
              <span class="subject-bar-name">${p.label}</span>
              <span class="subject-bar-count">${p.count}</span>
            </div>
            <div class="subject-bar-track">
              <div class="subject-bar-fill" style="width:${Math.round((p.count/(total||1))*100)}%;background:${p.color}"></div>
            </div>
          </div>`).join('');
  }

  // Completion bar
  const cb = document.getElementById('completionBar');
  const cl = document.getElementById('completionLabel');
  if (cb) cb.style.width = pct + '%';
  if (cl) cl.textContent = `${done} / ${total} tasks`;

  // Motivational tip
  const tipEmoji = document.getElementById('tipEmoji');
  const tipText  = document.getElementById('tipText');
  const tipSub   = document.getElementById('tipSub');
  if (tipText) {
    let emoji, headline, sub;
    if (total === 0)    { emoji='🎯'; headline="Let's get started!"; sub='Add your first task above.'; }
    else if (pct===100) { emoji='🥳'; headline='You finished everything!'; sub='Absolute legend. Take a break! 🎉'; }
    else if (pct>=75)   { emoji='🔥'; headline='Almost there!'; sub=`Just ${total-done} task${total-done>1?'s':''} left!`; }
    else if (pct>=50)   { emoji='⚡'; headline='Halfway done!'; sub=`${done} of ${total} complete. Keep going!`; }
    else if (done>0)    { emoji='💪'; headline='Good progress!'; sub=`${done} task${done>1?'s':''} done. Keep it up!`; }
    else if (ongoing>0) { emoji='⏳'; headline='Tasks in progress!'; sub=`${ongoing} task${ongoing>1?'s':''} ongoing.`; }
    else                { emoji='📋'; headline=`${pending} task${pending>1?'s':''} pending.`; sub='Start with highest priority first!'; }
    tipEmoji.textContent = emoji;
    tipText.textContent  = headline;
    tipSub.textContent   = sub;
  }
}

// ── TOAST ─────────────────────────────────
let toastTimer;
function showToast(msg, type='success') {
  toast.textContent = msg; toast.className = `show ${type}`;
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
  if (!dateStr||dateStr==='0000-00-00') return '<span class="due-date">—</span>';
  const due=new Date(dateStr+'T00:00:00'), today=new Date();
  today.setHours(0,0,0,0);
  const diff=Math.ceil((due-today)/86400000);
  const label=due.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
  if (diff<0)  return `<span class="due-date due-overdue">⚠ ${label}</span>`;
  if (diff<=3) return `<span class="due-date due-soon">⚡ ${label}</span>`;
  return `<span class="due-date">${label}</span>`;
}

// ============================================
// SMART SUBJECT AUTOCOMPLETE
// Learns every subject typed, saves to localStorage
// ============================================

const STORAGE_KEY = 'taskflow_subjects_v2';

function getLearnedSubjects() {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); }
  catch { return {}; }
}

function learnSubject(subject) {
  const s = (subject || '').trim();
  if (s.length < 2) return;
  const learned = getLearnedSubjects();
  learned[s] = (learned[s] || 0) + 1;
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(learned)); }
  catch(e) { console.warn('TaskFlow: could not save subject', e); }
}

function getAllKnownSubjects() {
  const learned = getLearnedSubjects();
  tasks.forEach(t => {
    const s = (t.subject || '').trim();
    if (s.length > 1 && !learned[s]) learned[s] = 1;
  });
  return learned;
}

function handleSubjectInput(value, isEdit) {
  isEdit = !!isEdit;
  const dropdownId = isEdit ? 'editSubjectDropdown' : 'subjectDropdown';
  const dropdown   = document.getElementById(dropdownId);
  if (!dropdown) return;
  const known   = getAllKnownSubjects();
  const query   = value.trim().toLowerCase();
  let matches   = Object.entries(known)
    .filter(([s]) => query === '' || s.toLowerCase().includes(query))
    .sort((a, b) => b[1] - a[1])
    .slice(0, 8);
  if (matches.length === 0 && query.length === 0) { dropdown.style.display='none'; return; }
  let html = '<div class="subject-dropdown-header">🧠 Smart Suggestions</div>';
  if (matches.length === 0) {
    html += '<div class="subject-dropdown-item" onmousedown="event.preventDefault();pickSubject(document.getElementById('' + (isEdit?'edit_subject':'subject') + '').value,' + isEdit + ')"><div class="sdi-icon">🆕</div><div class="sdi-label">' + escHtml(value) + '</div><span class="sdi-new">+ New</span></div>';
  } else {
    matches.forEach(([subject, count]) => {
      const safe = subject.replace(/\/g,'\\').replace(/'/g,"\'");
      html += '<div class="subject-dropdown-item" onmousedown="event.preventDefault();pickSubject('' + safe + '',' + isEdit + ')"><div class="sdi-icon">📚</div><div class="sdi-label">' + highlightMatch(subject, query) + '</div><span class="sdi-count">' + count + '×</span></div>';
    });
    const exactMatch = matches.some(([s]) => s.toLowerCase() === query);
    if (query.length > 1 && !exactMatch) {
      html += '<div class="subject-dropdown-item" onmousedown="event.preventDefault();pickSubject(document.getElementById('' + (isEdit?'edit_subject':'subject') + '').value,' + isEdit + ')"><div class="sdi-icon">✏️</div><div class="sdi-label">' + escHtml(value) + '</div><span class="sdi-new">+ New</span></div>';
    }
  }
  dropdown.innerHTML = html;
  dropdown.style.display = 'block';
}

function pickSubject(subject, isEdit) {
  const input = document.getElementById(isEdit ? 'edit_subject' : 'subject');
  if (input) { input.value = subject; input.focus(); }
  hideDropdown();
}

function hideDropdown() {
  ['subjectDropdown','editSubjectDropdown'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
}

function highlightMatch(text, query) {
  if (!query) return escHtml(text);
  const idx = text.toLowerCase().indexOf(query.toLowerCase());
  if (idx === -1) return escHtml(text);
  return escHtml(text.slice(0,idx))
    + '<strong style="color:var(--violet);background:rgba(124,58,237,.1);border-radius:3px;padding:0 1px">' + escHtml(text.slice(idx,idx+query.length)) + '</strong>'
    + escHtml(text.slice(idx+query.length));
}
