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
