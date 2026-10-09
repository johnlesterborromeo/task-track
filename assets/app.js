/* TaskTrack front-end application */
const $  = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

const api  = (action) => fetch('api.php?action=' + action).then(r => r.json());
const send = (action, data) => fetch('api.php?action=' + action, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(data)
}).then(r => r.json());

const esc = s => String(s ?? '').replace(/[&<>"']/g,
  c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

const STATUS      = { todo: 'To Do', in_progress: 'In Progress', done: 'Done' };
const NEXT_STATUS = { todo: 'in_progress', in_progress: 'done', done: 'todo' };

const themeToggle = $('#theme-toggle');
function setTheme(theme) {
  const isLight = theme === 'light';
  document.documentElement.dataset.theme = theme;
  themeToggle.innerHTML = isLight
    ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.9 13A8.5 8.5 0 0 1 11 3.1 8.5 8.5 0 1 0 20.9 13Z"/></svg>'
    : '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
  themeToggle.setAttribute('aria-label', `Switch to ${isLight ? 'dark' : 'light'} mode`);
  themeToggle.setAttribute('aria-pressed', String(isLight));
  localStorage.setItem('tasktrack-theme', theme);
}

setTheme(document.documentElement.dataset.theme === 'light' ? 'light' : 'dark');
themeToggle.addEventListener('click', () => {
  setTheme(document.documentElement.dataset.theme === 'light' ? 'dark' : 'light');
});

const state = {
  view: 'dashboard',
  tasks: [],
  notes: [],
  filter: 'all',
  taskSearch: '',
  taskPriority: 'all',
  taskDueDate: '',
  cal: new Date()
};

/* ================= Navigation ================= */
const sidebar = $('.sidebar');
const sidebarMenuToggle = $('#sidebar-menu-toggle');
$('#brand-home').addEventListener('click', () => showView('dashboard'));
sidebarMenuToggle.addEventListener('click', () => {
  const isOpen = sidebar.classList.toggle('mobile-nav-open');
  sidebarMenuToggle.setAttribute('aria-expanded', String(isOpen));
  sidebarMenuToggle.setAttribute('aria-label', isOpen ? 'Hide navigation' : 'Show navigation');
});

$$('.nav-btn').forEach(btn => btn.addEventListener('click', () => {
  showView(btn.dataset.view);
  sidebar.classList.remove('mobile-nav-open');
  sidebarMenuToggle.setAttribute('aria-expanded', 'false');
  sidebarMenuToggle.setAttribute('aria-label', 'Show navigation');
}));
$('#dash-see-tasks').addEventListener('click', () => showView('tasks'));
$('#dash-see-history').addEventListener('click', () => showView('history'));
$('#dash-see-notes').addEventListener('click', () => showView('notes'));

function showView(view) {
  if (!$$('.view').some(section => section.id === 'view-' + view)) {
    view = 'dashboard';
  }
  state.view = view;
  sessionStorage.setItem('tasktrack-current-view', view);
  $$('.nav-btn').forEach(b => b.classList.toggle('active', b.dataset.view === view));
  $$('.view').forEach(s => s.classList.toggle('active', s.id === 'view-' + view));
  $('#view-title').textContent = view.charAt(0).toUpperCase() + view.slice(1);
  loadView();
}

function loadView() {
  if (state.view === 'dashboard') return loadDashboard();
  if (state.view === 'tasks')     return loadTasks();
  if (state.view === 'notes')     return loadNotes();
  if (state.view === 'calendar')  return loadCalendar();
  if (state.view === 'history')   return loadHistory();
}

/* ================= Helpers ================= */
function fmtDate(d) {
  if (!d) return 'No due date';
  return new Date(d + 'T00:00:00').toLocaleDateString(undefined,
    { month: 'short', day: 'numeric', year: 'numeric' });
}

function isOverdue(t) {
  if (!t.due_date || t.status === 'done') return false;
  const today = new Date(); today.setHours(0, 0, 0, 0);
  return new Date(t.due_date + 'T00:00:00') < today;
}

function daysUntilDue(dueDate) {
  const [year, month, day] = dueDate.split('-').map(Number);
  const today = new Date();
  const todayUtc = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());
  return (Date.UTC(year, month - 1, day) - todayUtc) / 86400000;
}

function timeAgo(ts) {
  const then = new Date(String(ts).replace(' ', 'T'));
  const s = Math.floor((Date.now() - then) / 1000);
  if (s < 60) return 'just now';
  const m = Math.floor(s / 60); if (m < 60) return m + 'm ago';
  const h = Math.floor(m / 60); if (h < 24) return h + 'h ago';
  const d = Math.floor(h / 24); if (d < 30) return d + 'd ago';
  return then.toLocaleDateString();
}

/* ================= Dashboard ================= */
async function loadDashboard() {
  const [stats, tasks, activity, notes] = await Promise.all([
    api('stats'), api('tasks'), api('history'), api('notes')
  ]);
  state.tasks = tasks;
  state.notes = notes;
  renderReminders(tasks);

  const cards = [
    { label: 'Total tasks', value: stats.total,       cls: '',        icon: '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5h6M9 10h6M9 14h6M9 18h4"/>' },
    { label: 'In progress', value: stats.in_progress, cls: 'c-amber', icon: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>' },
    { label: 'Completed',   value: stats.done,       cls: 'c-green',  icon: '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>' },
    { label: 'Overdue',     value: stats.overdue,    cls: 'c-red',    icon: '<path d="M12 3 2.8 19a1.4 1.4 0 0 0 1.2 2h16a1.4 1.4 0 0 0 1.2-2L12 3Z"/><path d="M12 9v5m0 3h.01"/>' }
  ];
  $('#stat-cards').innerHTML = cards.map(c =>
    `<div class="stat-card ${c.cls}">
      <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true">${c.icon}</svg>
      <div class="stat-value">${c.value}</div>
      <div class="stat-label">${c.label}</div>
    </div>`
  ).join('');
  renderDailyProgress(tasks);

  const upcoming = tasks.filter(t => t.status !== 'done')
    .sort((a, b) => String(a.due_date || '9999').localeCompare(String(b.due_date || '9999')))
    .slice(0, 6);
  $('#dash-upcoming').innerHTML = upcoming.length
    ? `<div class="upcoming-task-list">${upcoming.map(upcomingTaskCard).join('')}</div>`
    : '<p class="empty">Nothing on your plate. Enjoy!</p>';

  $('#dash-activity').innerHTML = `<div class="dashboard-history-list dashboard-preview-list">${
    activity.length
      ? activity.slice(0, 3).map(historyRow).join('')
      : '<p class="empty">No activity yet.</p>'
  }</div>`;
  $('.dashboard-notes-panel').classList.toggle('is-empty', notes.length === 0);
  $('#dash-notes').innerHTML = `<div class="dashboard-notes-list dashboard-preview-list">${
    notes.length
      ? notes.slice(0, 3).map(dashboardNoteCard).join('')
      : '<p class="empty">No notes yet. Add one to get started.</p>'
  }</div>`;
}

function renderDailyProgress(tasks) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const days = Array.from({ length: 7 }, (_, index) => {
    const date = new Date(today);
    date.setDate(today.getDate() - (6 - index));
    const key = [
      date.getFullYear(),
      String(date.getMonth() + 1).padStart(2, '0'),
      String(date.getDate()).padStart(2, '0')
    ].join('-');
    const count = tasks.filter(task =>
      task.status === 'done' && String(task.completed_at || '').slice(0, 10) === key
    ).length;
    return { date, count };
  });
  const maxCount = Math.max(1, ...days.map(day => day.count));
  const todayCount = days[days.length - 1].count;
  const weekTotal = days.reduce((total, day) => total + day.count, 0);

  $('#daily-progress').innerHTML = `
    <div class="daily-progress-head">
      <div>
        <span class="daily-progress-eyebrow">COMPLETION OVERVIEW</span>
        <h3>Daily Progress Tracker</h3>
      </div>
      <div class="daily-progress-summary">
        <div class="daily-progress-stat">
          <strong>${weekTotal}</strong>
          <span>Last 7 days</span>
        </div>
        <div class="daily-progress-stat is-today">
          <strong>${todayCount}</strong>
          <span>Today</span>
        </div>
      </div>
    </div>
    <div class="daily-progress-chart" role="img" aria-label="Completed tasks by day over the last 7 days, ${weekTotal} total">
      ${days.map((day, index) => {
        const height = day.count ? Math.max(8, Math.round(day.count / maxCount * 100)) : 2;
        const weekday = day.date.toLocaleDateString(undefined, { weekday: 'short' });
        const dateNumber = day.date.toLocaleDateString(undefined, { day: 'numeric' });
        const fullDate = day.date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        return `<div class="daily-progress-day${index === days.length - 1 ? ' is-today' : ''}" aria-label="${fullDate}: ${day.count} completed">
          <span class="daily-progress-count">${day.count}</span>
          <div class="daily-progress-track"><div class="daily-progress-bar${day.count ? '' : ' is-zero'}" style="height:${height}%"></div></div>
          <span class="daily-progress-label">${weekday}<span>${dateNumber}</span></span>
        </div>`;
      }).join('')}
    </div>`;
}

function renderReminders(tasks) {
  const reminders = tasks
    .filter(t => t.status !== 'done' && t.due_date && daysUntilDue(t.due_date) <= 3)
    .sort((a, b) => String(a.due_date).localeCompare(String(b.due_date)));
  const count = $('#reminder-count');
  const bell = $('#reminder-open-tasks');

  count.textContent = String(reminders.length);
  count.hidden = reminders.length === 0;
  bell.setAttribute('aria-label', reminders.length
    ? `${reminders.length} task reminder${reminders.length === 1 ? '' : 's'}`
    : 'No reminders');
  $('#dashboard-reminders').innerHTML = reminders.map(reminderItem).join('');
}

function reminderItem(task) {
  const days = daysUntilDue(task.due_date);
  const message = days < 0 ? 'Overdue' : days === 0 ? 'Due today'
    : days === 1 ? 'Due tomorrow' : `Due in ${days} days`;
  const overdueClass = days < 0 ? ' overdue' : '';
  return `<div class="reminder-item${overdueClass}" data-id="${task.id}">
    <span class="reminder-indicator" aria-hidden="true"></span>
    <span class="reminder-title">${esc(task.title)}</span>
    <span class="reminder-due">${message}</span>
  </div>`;
}

function dashboardNoteCard(note) {
  return `<div class="note-card dashboard-note-card" data-id="${note.id}">
    <span class="history-icon dashboard-note-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20h4l11-11a2.1 2.1 0 0 0-4-4L4 16v4Zm9.5-13.5 4 4"/></svg></span>
    <div class="dashboard-note-content">
      <div class="note-title">${esc(note.title)}</div>
      <div class="note-body">${esc(note.content || '')}</div>
      <div class="note-time">edited ${timeAgo(note.updated_at)}</div>
    </div>
  </div>`;
}

/* ================= Task rows ================= */
function upcomingTaskCard(t) {
  const overdue = isOverdue(t) ? '<span class="badge overdue">Overdue</span>' : '';
  const completeAction = t.status === 'in_progress';
  return `<div class="task-row upcoming-task-card status-${t.status}" data-id="${t.id}">
    <div class="upcoming-task-head">
      <div class="task-title">${esc(t.title)}</div>
    </div>
    <div class="upcoming-task-meta">
      <span class="badge prio-${t.priority}">${esc(t.priority)}</span>
      <span class="badge status-badge">${STATUS[t.status]}</span>
    </div>
    <div class="upcoming-task-description">${esc(t.description || '')}</div>
    <div class="upcoming-task-due">${overdue}Due date: ${fmtDate(t.due_date)}</div>
    <button class="upcoming-start${completeAction ? ' is-complete' : ''}" type="button">${completeAction ? 'Complete' : 'Start task'}</button>
  </div>`;
}

function taskRow(t) {
  const od = isOverdue(t) ? '<span class="badge overdue">Overdue</span>' : '';
  return `<div class="task-row status-${t.status}" data-id="${t.id}">
    <button class="status-dot" type="button" aria-label="Move task to ${STATUS[NEXT_STATUS[t.status]]}" title="Move to ${STATUS[NEXT_STATUS[t.status]]}"></button>
    <div class="task-main">
      <div class="task-title">${esc(t.title)} ${od}</div>
      <div class="task-desc">${esc(t.description || '')}</div>
      <div class="task-meta">
        <span class="badge prio-${t.priority}">${t.priority}</span>
        <span class="due">${fmtDate(t.due_date)}</span>
      </div>
    </div>
    <span class="badge status-badge">${STATUS[t.status]}</span>
  </div>`;
}

/* ================= Tasks view ================= */
async function loadTasks() {
  state.tasks = await api('tasks');
  renderTasks();
}

function renderTasks() {
  const query = state.taskSearch.trim().toLocaleLowerCase();
  const list = state.tasks.filter(task => {
    const matchesStatus = state.filter === 'all' || task.status === state.filter;
    const matchesTitle = !query || String(task.title || '').toLocaleLowerCase().includes(query);
    const matchesPriority = state.taskPriority === 'all' || task.priority === state.taskPriority;
    const matchesDueDate = !state.taskDueDate || task.due_date === state.taskDueDate;
    return matchesStatus && matchesTitle && matchesPriority && matchesDueDate;
  });
  $('#task-list').innerHTML = list.length
    ? list.map(t => taskRow(t)).join('')
    : `<p class="empty">${state.tasks.length ? 'No tasks match these filters.' : 'No tasks here. Create one!'}</p>`;
}

$('#task-filters').addEventListener('click', e => {
  const btn = e.target.closest('button');
  if (!btn) return;
  state.filter = btn.dataset.f;
  $$('#task-filters button').forEach(b => b.classList.toggle('active', b === btn));
  renderTasks();
});
$('#task-search').addEventListener('input', e => {
  state.taskSearch = e.target.value;
  renderTasks();
});
$('#task-priority-filter').addEventListener('change', e => {
  state.taskPriority = e.target.value;
  renderTasks();
});
$('#task-due-filter').addEventListener('change', e => {
  state.taskDueDate = e.target.value;
  renderTasks();
});
/* ================= Notes ================= */
async function loadNotes() {
  state.notes = await api('notes');
  $('#note-count').textContent = state.notes.length + ' note' + (state.notes.length === 1 ? '' : 's');
  $('#notes-grid').innerHTML = state.notes.length
    ? state.notes.map(n => `<div class="note-card" data-id="${n.id}">
        <div class="note-title">${esc(n.title)}</div>
        <div class="note-body">${esc(n.content || '')}</div>
        <div class="note-time">edited ${timeAgo(n.updated_at)}</div>
      </div>`).join('')
    : '<p class="empty">No notes yet. Jot something down!</p>';
}

/* ================= Calendar ================= */
async function loadCalendar() {
  const y = state.cal.getFullYear(), m = state.cal.getMonth();
  $('#cal-title').textContent = state.cal.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
  const ym = y + '-' + String(m + 1).padStart(2, '0');
  const tasks = await api('tasks&month=' + ym);

  const first     = new Date(y, m, 1);
  const startDay  = (first.getDay() + 6) % 7;           // Monday-first
  const daysInMon = new Date(y, m + 1, 0).getDate();
  const today     = new Date(); today.setHours(0, 0, 0, 0);

  const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  let html = weekdays
    .map(d => `<div class="cal-dow">${d}</div>`).join('');
  let agenda = '';
  for (let i = 0; i < startDay; i++) html += '<div class="cal-cell empty"></div>';

  for (let d = 1; d <= daysInMon; d++) {
    const ds = ym + '-' + String(d).padStart(2, '0');
    const dayTasks = tasks.filter(t => t.due_date === ds);
    const date = new Date(y, m, d);
    const isToday = date.getTime() === today.getTime();
    const dayClass = isToday ? ' today' : '';
    const taskMarkup = dayTasks.slice(0, 3).map(calendarTask).join('');
    const moreMarkup = dayTasks.length > 3 ? `<div class="cal-more">+${dayTasks.length - 3} more</div>` : '';
    html += `<div class="cal-cell${dayClass}">
      <div class="cal-date">${d}</div>
      ${taskMarkup}
      ${moreMarkup}
    </div>`;
    if (dayTasks.length) {
      agenda += `<section class="cal-agenda-day${dayClass}">
        <div class="cal-agenda-date"><span class="cal-agenda-weekday">${date.toLocaleDateString(undefined, { weekday: 'short' })}</span><span class="cal-agenda-number">${d}</span></div>
        <div class="cal-agenda-tasks">${dayTasks.map(calendarTask).join('')}</div>
      </section>`;
    }
  }
  $('#cal-grid').innerHTML = html;
  $('#cal-agenda').innerHTML = agenda || '<p class="cal-agenda-empty">No tasks scheduled this month.</p>';
}

function calendarTask(task) {
  return `<button class="cal-task status-${task.status}" type="button" data-id="${task.id}" title="${esc(task.title)}">${esc(task.title)}</button>`;
}

$('#cal-prev').addEventListener('click', () => { state.cal.setMonth(state.cal.getMonth() - 1); loadCalendar(); });
$('#cal-next').addEventListener('click', () => { state.cal.setMonth(state.cal.getMonth() + 1); loadCalendar(); });
$('#cal-today').addEventListener('click', () => { state.cal = new Date(); loadCalendar(); });

/* ================= History ================= */
async function loadHistory() {
  const rows = await api('history');
  $('#history-count').textContent = `${rows.length} ${rows.length === 1 ? 'activity' : 'activities'}`;
  $('#history-list').innerHTML = rows.length
    ? rows.map(historyRow).join('')
    : '<p class="empty">No history yet.</p>';
}

$('#btn-clear-history').addEventListener('click', async () => {
  if (!confirm('Clear all history? This cannot be undone.')) return;
  await send('clear_history', {});
  loadView();
});

function historyRow(h) {
  const icons = {
    task_created: '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>',
    task_updated: '<svg viewBox="0 0 24 24"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-4-4L4 16v4Zm9.5-13.5 4 4"/></svg>',
    task_status: '<svg viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></svg>',
    task_deleted: '<svg viewBox="0 0 24 24"><path d="M4 7h16m-10 4v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg>',
    note_created: '<svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6zM14 3v5h5M9 12h7m-7 4h7"/></svg>',
    note_updated: '<svg viewBox="0 0 24 24"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-4-4L4 16v4Zm9.5-13.5 4 4"/></svg>',
    note_deleted: '<svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v5M14 3v5h5M6 3v18h8m-4-9h5m-5 4h3m3 4 2 2 4-5"/></svg>'
  };
  const type = String(h.action || '').startsWith('task_') ? 'task' : String(h.action || '').startsWith('note_') ? 'note' : 'other';
  const action = String(h.action || '').replace(/[^a-z_]/g, '');
  return `<div class="history-row history-action-${action}">
    <span class="history-icon history-icon-${type}" aria-hidden="true">${icons[h.action] || '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>'}</span>
    <div class="history-body">
      <div class="history-details">${esc(h.details)}</div>
      <div class="history-time">${timeAgo(h.created_at)}</div>
    </div>
  </div>`;
}

/* ================= Modals ================= */
function openModal(sel) { $(sel).classList.add('open'); }
function closeModals()  { $$('.modal-backdrop').forEach(m => m.classList.remove('open')); }

$$('[data-close]').forEach(b => b.addEventListener('click', closeModals));
$$('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModals(); }));
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeModals();
    $('#dashboard-reminder-popover').hidden = true;
    $('#reminder-open-tasks').setAttribute('aria-expanded', 'false');
    sidebar.classList.remove('mobile-nav-open');
    sidebarMenuToggle.setAttribute('aria-expanded', 'false');
    sidebarMenuToggle.setAttribute('aria-label', 'Show navigation');
  }
});

function openTaskModal(task = null) {
  const f = $('#task-form');
  f.reset();
  f.id.value          = task ? task.id : '';
  f.title.value       = task ? task.title : '';
  f.description.value = task ? (task.description || '') : '';
  f.priority.value    = task ? task.priority : 'medium';
  f.due_date.value    = task && task.due_date ? task.due_date : '';
  $('#task-modal-title').textContent = task ? 'Edit Task' : 'New Task';
  $('#btn-delete-task').style.display = task ? '' : 'none';
  openModal('#task-modal');
  f.title.focus();
}

function openReminderTaskCard(task) {
  $('#reminder-task-title').textContent = task.title;
  $('#reminder-task-description').textContent = task.description || 'No description';

  const priority = $('#reminder-task-priority');
  priority.className = `badge prio-${task.priority}`;
  priority.textContent = task.priority;

  const dueDate = $('#reminder-task-due');
  dueDate.textContent = fmtDate(task.due_date);
  dueDate.classList.toggle('overdue', isOverdue(task));
  openModal('#reminder-task-modal');
  $('[data-close]', $('#reminder-task-modal')).focus();
}

function openNoteModal(note = null) {
  const f = $('#note-form');
  f.reset();
  f.id.value      = note ? note.id : '';
  f.title.value   = note ? note.title : '';
  f.content.value = note ? (note.content || '') : '';
  $('#btn-delete-note').style.display = note ? '' : 'none';
  openModal('#note-modal');
  f.title.focus();
}

function openNoteDetail(note) {
  $('#note-detail-title').textContent = note.title;
  $('#note-detail-content').textContent = note.content || '';
  $('#note-detail-modal').dataset.noteId = note.id;
  openModal('#note-detail-modal');
  $('#btn-edit-note').focus();
}

$('#btn-add-task').addEventListener('click', () => openTaskModal());
$('#dash-add-task').addEventListener('click', () => openTaskModal());
$('#btn-add-note').addEventListener('click', () => openNoteModal());
$('#dash-add-note').addEventListener('click', () => openNoteModal());

$('#task-form').addEventListener('submit', async e => {
  e.preventDefault();
  const f = e.target;
  const data = {
    title:       f.title.value.trim(),
    description: f.description.value,
    priority:    f.priority.value,
    due_date:    f.due_date.value || null
  };
  if (f.id.value) {
    data.id = f.id.value;
    await send('update_task', data);
  } else {
    await send('add_task', data);
  }
  closeModals();
  loadView();
});

$('#btn-delete-task').addEventListener('click', async () => {
  if (!confirm('Delete this task?')) return;
  await send('delete_task', { id: $('#task-form').id.value });
  closeModals();
  loadView();
});

$('#note-form').addEventListener('submit', async e => {
  e.preventDefault();
  const f = e.target;
  const data = { title: f.title.value.trim(), content: f.content.value };
  if (f.id.value) data.id = f.id.value;
  await send('save_note', data);
  closeModals();
  loadView();
});

$('#btn-delete-note').addEventListener('click', async () => {
  if (!confirm('Delete this note?')) return;
  await deleteNote($('#note-form').id.value);
});

async function deleteNote(id) {
  await send('delete_note', { id });
  closeModals();
  loadView();
}

$('#btn-edit-note').addEventListener('click', () => {
  const id = $('#note-detail-modal').dataset.noteId;
  const note = state.notes.find(item => String(item.id) === String(id));
  if (note) {
    closeModals();
    openNoteModal(note);
  }
});

$('#btn-delete-note-detail').addEventListener('click', async () => {
  if (!confirm('Delete this note?')) return;
  await deleteNote($('#note-detail-modal').dataset.noteId);
});

/* ================= Delegated clicks ================= */
document.addEventListener('click', async e => {
  if (e.target.closest('#reminder-open-tasks')) {
    const bell = $('#reminder-open-tasks');
    const popover = $('#dashboard-reminder-popover');
    popover.hidden = !popover.hidden;
    bell.setAttribute('aria-expanded', String(!popover.hidden));
    return;
  }

  if (!e.target.closest('.reminder-control')) {
    $('#dashboard-reminder-popover').hidden = true;
    $('#reminder-open-tasks').setAttribute('aria-expanded', 'false');
  }

  const reminder = e.target.closest('.reminder-item');
  if (reminder) {
    const task = state.tasks.find(x => String(x.id) === String(reminder.dataset.id));
    if (task) {
      $('#dashboard-reminder-popover').hidden = true;
      $('#reminder-open-tasks').setAttribute('aria-expanded', 'false');
      openReminderTaskCard(task);
    }
    return;
  }

  const startButton = e.target.closest('.upcoming-start');
  if (startButton) {
    const id = startButton.closest('.upcoming-task-card').dataset.id;
    const task = state.tasks.find(x => String(x.id) === String(id));
    if (task && task.status !== 'done') {
      const status = task.status === 'todo' ? 'in_progress' : 'done';
      await send('update_task', { id: task.id, status });
      loadView();
    }
    return;
  }

  const dot = e.target.closest('.status-dot');
  if (dot) {
    e.stopPropagation();
    const id = dot.closest('.task-row').dataset.id;
    const t = state.tasks.find(x => String(x.id) === String(id));
    if (t) {
      await send('update_task', { id: t.id, status: NEXT_STATUS[t.status] });
      loadView();
    }
    return;
  }

  const calTask = e.target.closest('.cal-task');
  if (calTask) {
    state.tasks = await api('tasks');
    const t = state.tasks.find(x => String(x.id) === String(calTask.dataset.id));
    if (t) openReminderTaskCard(t);
    return;
  }

  const row = e.target.closest('.task-row');
  if (row) {
    const t = state.tasks.find(x => String(x.id) === String(row.dataset.id));
    if (t) openTaskModal(t);
    return;
  }

  const note = e.target.closest('.note-card');
  if (note) {
    const n = state.notes.find(x => String(x.id) === String(note.dataset.id));
    if (n) openNoteDetail(n);
  }
});

/* ================= Init ================= */
showView(sessionStorage.getItem('tasktrack-current-view') || 'dashboard');
