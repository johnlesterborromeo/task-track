<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TaskTrack</title>
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg?v=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mochiy+Pop+One&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
  const savedTheme = localStorage.getItem('tasktrack-theme');
  if (savedTheme === 'light' || savedTheme === 'dark') {
    document.documentElement.dataset.theme = savedTheme;
  }
</script>
<link rel="stylesheet" href="assets/style.css?v=117">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <button class="brand" id="brand-home" type="button" aria-label="TaskTrack dashboard"><span class="brand-name"><span class="brand-task"><span class="brand-t">T</span><span class="brand-a">a</span><span class="brand-s">s</span><span class="brand-k">k</span></span><span class="brand-track">Track</span></span></button>
    <div class="user-chip">
      <span class="profile-avatar" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
      </span>
      <span class="user-name"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>
    </div>
    <button class="sidebar-menu-toggle" id="sidebar-menu-toggle" type="button" aria-label="Show navigation" aria-expanded="false" aria-controls="sidebar-nav">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <nav class="nav" id="sidebar-nav">
      <button class="nav-btn active" data-view="dashboard"><span class="nav-ico">▦</span> Dashboard</button>
      <button class="nav-btn" data-view="tasks"><span class="nav-ico">☰</span> Tasks</button>
      <button class="nav-btn" data-view="notes"><span class="nav-ico">✎</span> Notes</button>
      <button class="nav-btn" data-view="calendar"><span class="nav-ico">▤</span> Calendar</button>
      <button class="nav-btn" data-view="history"><span class="nav-ico">↺</span> History</button>
    </nav>
    <div class="sidebar-foot">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false" aria-label="Switch to light mode"></button>
      <a class="logout" href="logout.php">Log out</a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 id="view-title">Dashboard</h1>
      <div class="topbar-actions">
        <div class="reminder-control">
          <button class="reminder-bell" id="reminder-open-tasks" type="button" aria-label="No reminders" aria-expanded="false" aria-controls="dashboard-reminder-popover">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
            <span class="reminder-count" id="reminder-count" hidden></span>
          </button>
          <div class="reminder-popover" id="dashboard-reminder-popover" hidden>
            <div class="reminder-popover-head">Reminders</div>
            <div class="reminder-list" id="dashboard-reminders"></div>
          </div>
        </div>
      </div>
    </header>
    <div class="content">

      <section id="view-dashboard" class="view active">
        <div class="dashboard-top">
          <div class="dashboard-main-stack">
            <div class="stat-cards" id="stat-cards"></div>
            <div class="panel">
              <div class="panel-head">
                <h3>Upcoming tasks</h3>
                <button class="btn view-all-btn" id="dash-see-tasks">View all</button>
              </div>
              <div id="dash-upcoming"></div>
              <button class="btn primary dashboard-task-create-btn" id="dash-add-task">New Task</button>
            </div>
            <div class="panel daily-progress-panel" id="daily-progress"></div>
          </div>
          <div class="dashboard-side-stack">
            <div class="panel dashboard-notes-panel">
              <div class="panel-head"><h3>Recent notes</h3><button class="btn view-all-btn" id="dash-see-notes">View all</button></div>
              <div id="dash-notes"></div>
              <button class="btn primary note-create-btn dashboard-note-create-btn" id="dash-add-note">New Note</button>
            </div>
            <div class="panel dashboard-history-panel">
              <div class="panel-head"><h3>Recent activity</h3><button class="btn view-all-btn" id="dash-see-history">View all</button></div>
              <div id="dash-activity"></div>
            </div>
          </div>
        </div>
      </section>

      <section id="view-tasks" class="view">
        <div class="toolbar task-toolbar">
          <div class="task-filter-tools">
            <label class="task-search">
              <span class="sr-only">Search tasks by title</span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
              <input id="task-search" type="search" placeholder="Search tasks..." autocomplete="off">
            </label>
            <label class="task-select-filter">
              <span class="sr-only">Filter tasks by priority</span>
              <select id="task-priority-filter">
                <option value="all">All priorities</option>
                <option value="high">High priority</option>
                <option value="medium">Medium priority</option>
                <option value="low">Low priority</option>
              </select>
            </label>
            <label class="task-date-filter">
              <span class="sr-only">Filter tasks by due date</span>
              <input id="task-due-filter" type="date" aria-label="Filter tasks by due date">
            </label>
          </div>
          <div class="filters" id="task-filters">
            <button data-f="all" class="active">All</button>
            <button data-f="todo">To Do</button>
            <button data-f="in_progress">In Progress</button>
            <button data-f="done">Done</button>
          </div>
          <button class="btn primary" id="btn-add-task">+ New Task</button>
        </div>
        <div id="task-list" class="task-list"></div>
      </section>

      <section id="view-notes" class="view">
        <div class="toolbar">
          <span class="muted" id="note-count"></span>
          <button class="btn primary note-create-btn" id="btn-add-note">New Note</button>
        </div>
        <div id="notes-grid" class="notes-grid"></div>
      </section>

      <section id="view-calendar" class="view">
        <div class="calendar-panel panel">
          <div class="cal-head">
            <div class="cal-heading">
              <span class="cal-eyebrow">Task schedule</span>
              <h2 id="cal-title" aria-live="polite"></h2>
            </div>
            <div class="cal-controls">
              <button class="btn cal-today" id="cal-today" type="button">Today</button>
              <button class="btn cal-nav-btn" id="cal-prev" type="button" aria-label="Previous month">‹ <span>Prev</span></button>
              <button class="btn cal-nav-btn" id="cal-next" type="button" aria-label="Next month"><span>Next</span> ›</button>
            </div>
          </div>
          <div class="cal-grid" id="cal-grid"></div>
          <div class="cal-agenda" id="cal-agenda"></div>
        </div>
      </section>

      <section id="view-history" class="view">
        <div class="history-page-head">
          <div>
            <span class="history-eyebrow">Your activity</span>
            <h2>History</h2>
            <p>Review your recent task and note changes.</p>
          </div>
          <button class="btn danger history-clear-btn" id="btn-clear-history">Clear history</button>
        </div>
        <div class="history-summary" id="history-count"></div>
        <div id="history-list" class="history-list"></div>
      </section>

    </div>
  </main>
</div>

<!-- Task modal -->
<div class="modal-backdrop" id="task-modal">
  <form class="modal task-modal" id="task-form" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">
    <div class="task-form-heading">
      <span class="task-form-eyebrow">TASK DETAILS</span>
      <h3 id="task-modal-title">New Task</h3>
      <p>Give your task a name and set its priority and due date.</p>
    </div>
    <input type="hidden" name="id">
    <label>Title <input name="title" required maxlength="150"></label>
    <label>Description <textarea name="description" rows="3"></textarea></label>
    <div class="row">
      <label>Priority
        <select name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>
      </label>
      <label>Due date <input type="date" name="due_date"></label>
    </div>
    <div class="task-checklist-editor">
      <div class="task-checklist-editor-head">
        <span>Checklist <span class="muted">(optional)</span></span>
        <button type="button" class="btn small" id="task-checklist-add">+ Add item</button>
      </div>
      <div class="task-checklist-edit-items" id="task-checklist-edit-items"></div>
    </div>
    <div class="task-modal-actions">
      <button class="btn primary">Save</button>
      <button type="button" class="btn task-action-button task-modal-status-button" id="btn-task-status" hidden></button>
      <button type="button" class="btn danger" id="btn-delete-task">Delete</button>
    </div>
  </form>
</div>

<div class="modal-backdrop" id="reminder-task-modal">
  <section class="modal reminder-task-card" role="dialog" aria-modal="true" aria-labelledby="reminder-task-title">
    <div class="reminder-task-kicker"><span class="reminder-task-kicker-dot"></span> Task reminder</div>
    <div class="reminder-task-head">
      <h3 id="reminder-task-title"></h3>
      <button class="reminder-task-close" type="button" data-close aria-label="Close task details">&times;</button>
    </div>
    <div class="reminder-task-description-block">
      <div class="reminder-task-label">Description</div>
      <div class="reminder-task-description" id="reminder-task-description"></div>
    </div>
    <div class="reminder-task-meta">
      <div class="reminder-task-detail">
        <span class="reminder-task-label">Priority</span>
        <span class="badge" id="reminder-task-priority"></span>
      </div>
      <div class="reminder-task-detail reminder-task-due-detail">
        <span class="reminder-task-label">Due date</span>
        <span class="reminder-task-due" id="reminder-task-due"></span>
      </div>
    </div>
  </section>
</div>

<!-- Note modal -->
<div class="modal-backdrop" id="note-modal">
  <form class="modal" id="note-form">
    <h3>Note</h3>
    <input type="hidden" name="id">
    <label>Title <input name="title" required maxlength="150"></label>
    <label>Content <textarea name="content" rows="8"></textarea></label>
    <div class="modal-actions">
      <button type="button" class="btn danger" id="btn-delete-note">Delete</button>
      <span class="spacer"></span>
      <button type="button" class="btn" data-close>Cancel</button>
      <button class="btn primary">Save</button>
    </div>
  </form>
</div>

<div class="modal-backdrop" id="note-detail-modal">
  <section class="modal note-detail-card" role="dialog" aria-modal="true" aria-labelledby="note-detail-title">
    <h3 id="note-detail-title"></h3>
    <div class="note-detail-content" id="note-detail-content"></div>
    <div class="modal-actions note-detail-actions">
      <button type="button" class="btn primary" id="btn-edit-note">Edit</button>
      <button type="button" class="btn danger" id="btn-delete-note-detail">Delete</button>
    </div>
  </section>
</div>

<script src="assets/app.js?v=37"></script>
</body>
</html>
