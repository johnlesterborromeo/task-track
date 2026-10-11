/* ================= Task rows ================= */
function upcomingTaskCard(t) {
  const overdue = isOverdue(t) ? '<span class="badge overdue">Overdue</span>' : '';
  const description = Array.from(String(t.description || ''));
  const shortDescription = description.length > 20
    ? description.slice(0, 20).join('') + '...'
    : description.join('');
  return `<div class="task-row upcoming-task-card status-${t.status}" data-id="${t.id}">
    <div class="upcoming-task-head">
      <div class="task-title">${esc(t.title)}</div>
      <span class="task-created-date">${createdDate(t.created_at)}</span>
    </div>
    <div class="upcoming-task-meta">
      <span class="badge prio-${t.priority}">${esc(t.priority)}</span>
      <span class="badge status-badge">${STATUS[t.status]}</span>
    </div>
    ${checklistProgress(t)}
    <div class="upcoming-task-description">${esc(shortDescription)}</div>
    <div class="upcoming-task-due">${overdue}Due date: ${fmtDate(t.due_date)}</div>
  </div>`;
}

function taskRow(t) {
  const od = isOverdue(t) ? '<span class="badge overdue">Overdue</span>' : '';
  const isComplete = t.status === 'done';
  return `<div class="task-row status-${t.status}" data-id="${t.id}">
    <button class="status-dot" type="button" aria-label="Move task to ${STATUS[NEXT_STATUS[t.status]]}" title="Move to ${STATUS[NEXT_STATUS[t.status]]}"${isComplete ? ' disabled' : ''}></button>
    <div class="task-main">
      <div class="task-title">${esc(t.title)} ${od}</div>
      <div class="task-desc">${esc(t.description || '')}</div>
      ${checklistProgress(t)}
      ${checklistEntries(t)}
      <div class="task-meta">
        <span class="badge prio-${t.priority}">${t.priority}</span>
        <span class="due">${fmtDate(t.due_date)}</span>
      </div>
    </div>
    <div class="task-row-aside">
      <span class="task-created-date">${createdDate(t.created_at)}</span>
      <span class="badge status-badge">${STATUS[t.status]}</span>
    </div>
  </div>`;
}

function createdDate(value) {
  if (!value) return '';
  return `Created ${fmtDate(String(value).slice(0, 10))}`;
}

function checklistProgress(task) {
  const items = task.checklist || [];
  if (!items.length) return '';
  const completed = items.filter(item => item.completed).length;
  const percent = Math.round(completed * 100 / items.length);
  return `<div class="task-checklist-progress">
    <div class="task-checklist-progress-label"><span>Task progress</span><span>${percent}%</span></div>
    <div class="task-checklist-progress-track" role="progressbar" aria-label="Task progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${percent}"><span style="width:${percent}%"></span></div>
  </div>`;
}

function checklistEntries(task) {
  const items = task.checklist || [];
  if (!items.length) return '';
  return `<div class="task-checklist-entries">${items.map(item => `
    <label class="task-checklist-entry">
      <input class="task-checklist-toggle" type="checkbox" data-item-id="${item.id}"${item.completed ? ' checked' : ''}${task.status === 'done' ? ' disabled' : ''}>
      <span${item.completed ? ' class="is-complete"' : ''}>${esc(item.title)}</span>
    </label>`).join('')}</div>`;
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
