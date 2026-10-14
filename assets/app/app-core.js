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
  themeToggle.innerHTML = materialIcon(isLight ? 'dark_mode' : 'light_mode');
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
