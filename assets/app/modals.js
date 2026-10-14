/* ================= Modals ================= */
function openModal(sel) { $(sel).classList.add('open'); }
function closeModals()  {
  $$('.modal-backdrop').forEach(m => m.classList.remove('open'));
  profileToggle.setAttribute('aria-expanded', 'false');
}

$$('[data-close]').forEach(b => b.addEventListener('click', closeModals));
$$('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModals(); }));
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeModals();
    $('#dashboard-reminder-popover').hidden = true;
    $('#reminder-open-tasks').setAttribute('aria-expanded', 'false');
    accountMenu.hidden = true;
    profileToggle.setAttribute('aria-expanded', 'false');
    sidebar.classList.remove('mobile-nav-open');
    sidebarMenuToggle.setAttribute('aria-expanded', 'false');
    sidebarMenuToggle.setAttribute('aria-label', 'Show navigation');
  }
});

function openTaskModal(task = null) {
  const f = $('#task-form');
  const isComplete = task?.status === 'done';
  f.reset();
  f.dataset.readOnly = String(isComplete);
  f.id.value          = task ? task.id : '';
  f.title.value       = task ? task.title : '';
  f.description.value = task ? (task.description || '') : '';
  f.priority.value    = task ? task.priority : 'medium';
  f.due_date.value    = task && task.due_date ? task.due_date : '';
  renderTaskChecklistEditor(task ? (task.checklist || []) : [], isComplete);
  $('#task-modal-title').textContent = isComplete ? task.title : task ? 'Edit Task' : 'New Task';
  $('#task-modal-description').textContent = isComplete
    ? 'Completed task details.'
    : 'Give your task a name and set its priority and due date.';
  $('#task-edit-fields').hidden = isComplete;
  $('#task-readonly-details').hidden = !isComplete;
  if (isComplete) {
    $('#task-readonly-description').textContent = task.description || 'No description.';
    $('#task-readonly-priority').textContent = task.priority;
    $('#task-readonly-due').textContent = fmtDate(task.due_date);
    const checklist = task.checklist || [];
    $('#task-readonly-checklist-wrap').hidden = checklist.length === 0;
    $('#task-readonly-checklist').innerHTML = checklist.map(item =>
      `<li${item.completed ? ' class="is-complete"' : ''}><span class="task-readonly-checklist-icon">${materialIcon(item.completed ? 'task_alt' : 'check_box_outline_blank')}</span>${esc(item.title)}</li>`
    ).join('');
  }
  $('#btn-task-save').hidden = isComplete;
  $('#btn-task-close').hidden = !isComplete;
  $('#btn-delete-task').style.display = task ? '' : 'none';
  const statusButton = $('#btn-task-status');
  statusButton.hidden = !task || isComplete;
  statusButton.classList.toggle('is-complete', task && task.status === 'in_progress');
  statusButton.textContent = task && task.status === 'in_progress' ? 'Complete task' : 'Start task';
  $('#task-checklist-add').hidden = isComplete;
  f.title.disabled = isComplete;
  f.description.disabled = isComplete;
  f.priority.disabled = isComplete;
  f.due_date.disabled = isComplete;
  openModal('#task-modal');
  (isComplete ? $('#btn-task-close') : f.title).focus();
}

function renderTaskChecklistEditor(items, readOnly = false) {
  $('#task-checklist-edit-items').innerHTML = items.map(item => `
    <div class="task-checklist-edit-row">
      <input class="task-checklist-edit-completed" type="checkbox" aria-label="Checklist item completed"${item.completed ? ' checked' : ''}${readOnly ? ' disabled' : ''}>
      <input class="task-checklist-edit-title" type="text" maxlength="150" placeholder="Checklist item" value="${esc(item.title)}"${readOnly ? ' disabled' : ''}>
      <button type="button" class="task-checklist-remove" aria-label="Remove checklist item"${readOnly ? ' hidden' : ''}>&times;</button>
    </div>`).join('');
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
$('#task-checklist-add').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'task-checklist-edit-row';
  row.innerHTML = '<input class="task-checklist-edit-completed" type="checkbox" aria-label="Checklist item completed"><input class="task-checklist-edit-title" type="text" maxlength="150" placeholder="Checklist item"><button type="button" class="task-checklist-remove" aria-label="Remove checklist item">&times;</button>';
  $('#task-checklist-edit-items').append(row);
  $('.task-checklist-edit-title', row).focus();
});
$('#task-checklist-edit-items').addEventListener('click', e => {
  const remove = e.target.closest('.task-checklist-remove');
  if (remove) remove.closest('.task-checklist-edit-row').remove();
});

$('#task-form').addEventListener('submit', async e => {
  e.preventDefault();
  const f = e.target;
  if (f.dataset.readOnly === 'true') return;
  const data = {
    title:       f.title.value.trim(),
    description: f.description.value,
    priority:    f.priority.value,
    due_date:    f.due_date.value || null,
    checklist:   $$('.task-checklist-edit-row', $('#task-checklist-edit-items')).map(row => ({
      title: $('.task-checklist-edit-title', row).value.trim(),
      completed: $('.task-checklist-edit-completed', row).checked
    })).filter(item => item.title)
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
  const id = $('#task-form').id.value;
  const task = state.tasks.find(item => String(item.id) === String(id));
  const message = task?.status === 'done'
    ? 'Delete this completed task? This cannot be undone.'
    : 'Delete this task? This cannot be undone.';
  if (!confirm(message)) return;
  await send('delete_task', { id });
  closeModals();
  loadView();
});

$('#btn-task-status').addEventListener('click', async () => {
  const id = $('#task-form').id.value;
  const task = state.tasks.find(item => String(item.id) === String(id));
  if (!task || task.status === 'done') return;
  const status = task.status === 'todo' ? 'in_progress' : 'done';
  if (status === 'done' && !confirm('Are you sure you want to complete this task?')) return;
  await send('update_task', { id: task.id, status });
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
