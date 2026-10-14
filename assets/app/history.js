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
    task_created: 'add',
    task_updated: 'edit',
    task_status: 'task_alt',
    task_deleted: 'delete',
    note_created: 'note_add',
    note_updated: 'edit',
    note_deleted: 'delete'
  };
  const type = String(h.action || '').startsWith('task_') ? 'task' : String(h.action || '').startsWith('note_') ? 'note' : 'other';
  const action = String(h.action || '').replace(/[^a-z_]/g, '');
  return `<div class="history-row history-action-${action}">
    <span class="history-icon history-icon-${type}" aria-hidden="true">${materialIcon(icons[h.action] || 'info')}</span>
    <div class="history-body">
      <div class="history-details">${esc(h.details)}</div>
      <div class="history-time">${timeAgo(h.created_at)}</div>
    </div>
  </div>`;
}
