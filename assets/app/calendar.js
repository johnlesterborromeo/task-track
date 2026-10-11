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
