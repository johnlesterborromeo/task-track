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
