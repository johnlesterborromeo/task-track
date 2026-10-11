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
    </div>
  </div>`;
}
