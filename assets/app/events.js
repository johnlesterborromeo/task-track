/* ================= Delegated clicks ================= */
document.addEventListener('click', async e => {
  const viewToggle = e.target.closest('#dash-task-view-toggle');
  const viewMenu = $('#dash-task-view-menu');
  if (viewToggle) {
    viewMenu.hidden = !viewMenu.hidden;
    viewToggle.setAttribute('aria-expanded', String(!viewMenu.hidden));
    if (!viewMenu.hidden) viewMenu.querySelector('[aria-checked="true"]')?.focus();
    return;
  }

  const viewChoice = e.target.closest('[data-dashboard-task-view]');
  if (viewChoice) {
    localStorage.setItem('tasktrack-dashboard-task-view', viewChoice.dataset.dashboardTaskView);
    viewMenu.hidden = true;
    $('#dash-task-view-toggle').setAttribute('aria-expanded', 'false');
    const upcoming = state.tasks.filter(task => task.status !== 'done')
      .sort((a, b) => String(a.due_date || '9999').localeCompare(String(b.due_date || '9999')))
      .slice(0, 6);
    renderUpcomingTasks(upcoming);
    return;
  }

  if (!e.target.closest('.dashboard-task-view-control')) {
    viewMenu.hidden = true;
    $('#dash-task-view-toggle').setAttribute('aria-expanded', 'false');
  }

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

  const checklistToggle = e.target.closest('.task-checklist-toggle');
  if (checklistToggle) {
    e.stopPropagation();
    await send('toggle_checklist_item', { id: checklistToggle.dataset.itemId });
    loadView();
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

document.addEventListener('keydown', e => {
  const menu = $('#dash-task-view-menu');
  if (e.key !== 'Escape' || menu.hidden) return;
  menu.hidden = true;
  const toggle = $('#dash-task-view-toggle');
  toggle.setAttribute('aria-expanded', 'false');
  toggle.focus();
});
