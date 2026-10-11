/* TaskTrack front-end application */
const $  = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];

const api  = (action) => fetch('api.php?action=' + action).then(r => r.json());
const send = (action, data) => fetch('api.php?action=' + action, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(data)
}).then(r => r.json());
const sendFile = (action, data) => fetch('api.php?action=' + action, {
  method: 'POST',
  body: data
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
