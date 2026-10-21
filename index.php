<?php
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TaskTrack</title>
<link rel="icon" type="image/png" href="assets/T.png">
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
<link rel="stylesheet" href="assets/design/style.css?v=161">
<link rel="stylesheet" href="assets/design/dashboard.css?v=158">
<link rel="stylesheet" href="assets/design/tasks.css?v=153">
<link rel="stylesheet" href="assets/design/notes.css?v=156">
<link rel="stylesheet" href="assets/design/calendar.css?v=152">
<link rel="stylesheet" href="assets/design/history.css?v=152">
<link rel="stylesheet" href="assets/design/modals.css?v=152">
<link rel="stylesheet" href="assets/design/auth.css?v=151">
<link rel="stylesheet" href="assets/design/responsive.css?v=152">
</head>
<body>
<svg class="material-icon-definitions" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
  <symbol id="material-icon-account_circle" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 3a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7Zm0 14.2a7.2 7.2 0 0 1-5.8-2.9c.1-1.9 3.9-3 5.8-3s5.7 1.1 5.8 3a7.2 7.2 0 0 1-5.8 2.9Z"/></symbol>
  <symbol id="material-icon-expand_more" viewBox="0 0 24 24"><path d="m7.4 9.2 4.6 4.6 4.6-4.6L18 10.6l-6 6-6-6z"/></symbol>
  <symbol id="material-icon-menu" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2Zm0-5h18v-2H3v2Zm0-7v2h18V6H3Z"/></symbol>
  <symbol id="material-icon-dashboard" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/></symbol>
  <symbol id="material-icon-checklist" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM10 17H7v-2h3v2Zm0-4H7v-2h3v2Zm0-4H7V7h3v2Zm7 8h-5v-2h5v2Zm0-4h-5v-2h5v2Zm0-4h-5V7h5v2Z"/></symbol>
  <symbol id="material-icon-description" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 2.5L17.5 8H14V4.5ZM18 20H6V4h6v6h6v10ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/></symbol>
  <symbol id="material-icon-calendar_month" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 16H5V9h14v11ZM7 11h4v4H7v-4Z"/></symbol>
  <symbol id="material-icon-history" viewBox="0 0 24 24"><path d="M13 3a9 9 0 0 0-9 9H1l4 4 4-4H6a7 7 0 1 1 2.1 5l-1.4 1.4A9 9 0 1 0 13 3Zm-1 5v5l4.2 2.5 1-1.6-3.2-1.9V8h-2Z"/></symbol>
  <symbol id="material-icon-notifications" viewBox="0 0 24 24"><path d="M12 22a2.2 2.2 0 0 0 2.2-2.2H9.8A2.2 2.2 0 0 0 12 22Zm7-5-2-2.2V10a5 5 0 0 0-4-4.9V4a1 1 0 0 0-2 0v1.1A5 5 0 0 0 7 10v4.8L5 17v1h14v-1Z"/></symbol>
  <symbol id="material-icon-tune" viewBox="0 0 24 24"><path d="M3 17v2h6v-2H3Zm0-12v2h10V5H3Zm10 16v-2h8v-2h-8v-2h-2v6h2ZM7 9v2H3v2h4v2h2V9H7Zm14 4v-2h-6V9h-2v6h2v-2h6Zm-4-8V3h-2v2h-3v2h3v2h2V7h4V5h-4Z"/></symbol>
  <symbol id="material-icon-format_list_bulleted" viewBox="0 0 24 24"><path d="M4 10.5A1.5 1.5 0 1 0 4 13.5a1.5 1.5 0 0 0 0-3ZM7 12h14v-2H7v2Zm0 7h14v-2H7v2Zm-3-2.5A1.5 1.5 0 1 0 4 19.5a1.5 1.5 0 0 0 0-3ZM4 3.5A1.5 1.5 0 1 0 4 6.5a1.5 1.5 0 0 0 0-3ZM7 6h14V4H7v2Z"/></symbol>
  <symbol id="material-icon-view_kanban" viewBox="0 0 24 24"><path d="M3 3h8v18H3V3Zm10 0h8v11h-8V3Zm0 13h8v5h-8v-5Z"/></symbol>
  <symbol id="material-icon-grid_view" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3Zm10 0h8v8h-8V3ZM3 13h8v8H3v-8Zm10 0h8v8h-8v-8Z"/></symbol>
  <symbol id="material-icon-timeline" viewBox="0 0 24 24"><path d="M3 3h2v18H3V3Zm4 3h5v2H7V6Zm5 5h5v2h-5v-2Zm-3 5h5v2H9v-2Zm3-9a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm5 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm-3 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/></symbol>
  <symbol id="material-icon-search" viewBox="0 0 24 24"><path d="M15.5 14h-.8l-.3-.3a6.5 6.5 0 1 0-.7.7l.3.3v.8l5 5 1.5-1.5-5-5ZM10 14a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/></symbol>
  <symbol id="material-icon-edit" viewBox="0 0 24 24"><path d="M3 17.3V21h3.7L17.6 10l-3.7-3.7L3 17.3ZM20.7 6.9a1 1 0 0 0 0-1.4l-2.2-2.2a1 1 0 0 0-1.4 0l-1.8 1.8 3.7 3.7 1.7-1.9Z"/></symbol>
  <symbol id="material-icon-visibility" viewBox="0 0 24 24"><path d="M12 4.5C6.5 4.5 2.1 8 1 12c1.1 4 5.5 7.5 11 7.5s9.9-3.5 11-7.5c-1.1-4-5.5-7.5-11-7.5Zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></symbol>
  <symbol id="material-icon-visibility_off" viewBox="0 0 24 24"><path d="m2.3 1 20.7 20.7-1.3 1.3-3.8-3.8a12.7 12.7 0 0 1-5.9 1.3C6.5 20.5 2.1 17 1 13c.7-2.5 2.4-4.7 4.8-6.1L1 2.3 2.3 1ZM12 6.5c5.5 0 9.9 3.5 11 7.5a11 11 0 0 1-3.6 5.1l-3-3a5 5 0 0 0-6.5-6.5l-2-2A12.7 12.7 0 0 1 12 6.5Z"/></symbol>
  <symbol id="material-icon-light_mode" viewBox="0 0 24 24"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-1-6h2v3h-2v-3Zm0 17h2v3h-2v-3ZM2 11h3v2H2v-2Zm17 0h3v2h-3v-2ZM4.2 5.6l1.4-1.4 2.1 2.1-1.4 1.4-2.1-2.1Zm12.1 12.1 1.4-1.4 2.1 2.1-1.4 1.4-2.1-2.1ZM18.4 4.2l1.4 1.4-2.1 2.1-1.4-1.4 2.1-2.1ZM6.3 16.3l1.4 1.4-2.1 2.1-1.4-1.4 2.1-2.1Z"/></symbol>
  <symbol id="material-icon-dark_mode" viewBox="0 0 24 24"><path d="M9.4 2.5a9.5 9.5 0 1 0 12.1 12.1A8 8 0 0 1 9.4 2.5Z"/></symbol>
  <symbol id="material-icon-assignment" viewBox="0 0 24 24"><path d="M19 3h-4.2a3 3 0 0 0-5.6 0H5a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7-1a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm5 17H7v-2h10v2Zm0-4H7v-2h10v2Zm0-4H7V9h10v2Z"/></symbol>
  <symbol id="material-icon-schedule" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 11h5v-2h-4V6h-2v6a1 1 0 0 0 1 1Z"/></symbol>
  <symbol id="material-icon-task_alt" viewBox="0 0 24 24"><path d="M22 5.2 11 16.2l-4-4-1.4 1.4 5.4 5.4L23.4 6.6 22 5.2ZM2 12a10 10 0 1 0 10-10v2a8 8 0 1 1-8 8H2Z"/></symbol>
  <symbol id="material-icon-check_box_outline_blank" viewBox="0 0 24 24"><path d="M19 5v14H5V5h14Zm0-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></symbol>
  <symbol id="material-icon-check" viewBox="0 0 24 24"><path d="m9 16.2-4.2-4.2-1.4 1.4L9 19 21 7l-1.4-1.4z"/></symbol>
  <symbol id="material-icon-warning" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2v2Zm0-4h-2v-5h2v5Z"/></symbol>
  <symbol id="material-icon-add" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2Z"/></symbol>
  <symbol id="material-icon-arrow_back_ios_new" viewBox="0 0 24 24"><path d="M15.4 4 7.4 12l8 8 1.4-1.4-6.6-6.6 6.6-6.6L15.4 4Z"/></symbol>
  <symbol id="material-icon-arrow_forward_ios" viewBox="0 0 24 24"><path d="m8.6 4-1.4 1.4 6.6 6.6-6.6 6.6L8.6 20l8-8-8-8Z"/></symbol>
  <symbol id="material-icon-close" viewBox="0 0 24 24"><path d="m19 6.4-1.4-1.4L12 10.6 6.4 5 5 6.4l5.6 5.6L5 17.6 6.4 19l5.6-5.6 5.6 5.6 1.4-1.4-5.6-5.6L19 6.4Z"/></symbol>
  <symbol id="material-icon-delete" viewBox="0 0 24 24"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12ZM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4Z"/></symbol>
  <symbol id="material-icon-note_add" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 3.5L17.5 9H14V5.5ZM18 20H6V4h6v7h6v9Zm-7-2h2v-3h3v-2h-3v-3h-2v3H8v2h3v3Z"/></symbol>
  <symbol id="material-icon-info" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 15h-2v-6h2v6Zm0-8h-2V7h2v2Z"/></symbol>
</svg>
<div class="app">
  <aside class="sidebar">
    <button class="brand" id="brand-home" type="button" aria-label="TaskTrack dashboard"><span class="brand-name"><span class="brand-task"><span class="brand-t">T</span><span class="brand-a">a</span><span class="brand-s">s</span><span class="brand-k">k</span></span><span class="brand-track">Track</span></span></button>
    <div class="profile-control">
      <button class="user-chip" id="profile-toggle" type="button" data-username="<?= htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8') ?>" aria-expanded="false" aria-controls="account-menu" aria-label="Account options">
        <span class="profile-avatar" aria-hidden="true">
          <svg class="material-icon" viewBox="0 0 24 24"><use href="#material-icon-account_circle"></use></svg>
          <img id="sidebar-profile-picture" alt="" hidden>
        </span>
        <span class="user-name"><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username'], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="profile-chevron material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-expand_more"></use></svg>
      </button>
      <div class="account-choice-menu" id="account-menu" hidden>
        <button class="account-choice" id="show-profile-information" type="button">Profile information</button>
        <button class="account-choice" id="manage-account" type="button">Manage account</button>
        <a class="logout account-choice-logout" href="logout.php">Log out</a>
      </div>
    </div>
    <button class="sidebar-menu-toggle" id="sidebar-menu-toggle" type="button" aria-label="Show navigation" aria-expanded="false" aria-controls="sidebar-nav">
      <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-menu"></use></svg>
    </button>
    <nav class="nav" id="sidebar-nav">
      <button class="nav-btn active" data-view="dashboard"><span class="nav-ico"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-dashboard"></use></svg></span> Dashboard</button>
      <button class="nav-btn" data-view="tasks"><span class="nav-ico"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-checklist"></use></svg></span> Tasks</button>
      <button class="nav-btn" data-view="notes"><span class="nav-ico"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-description"></use></svg></span> Notes</button>
      <button class="nav-btn" data-view="calendar"><span class="nav-ico"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-calendar_month"></use></svg></span> Calendar</button>
      <button class="nav-btn" data-view="history"><span class="nav-ico"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-history"></use></svg></span> History</button>
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
            <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-notifications"></use></svg>
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
                <div class="panel-actions">
                  <button class="btn view-all-btn" id="dash-see-tasks">View all</button>
                  <div class="dashboard-task-view-control">
                    <button class="dashboard-task-view-toggle" id="dash-task-view-toggle" type="button" aria-label="Choose task appearance" aria-haspopup="menu" aria-expanded="false" aria-controls="dash-task-view-menu" title="Choose task appearance">
                      <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-tune"></use></svg>
                    </button>
                    <div class="dashboard-task-view-menu" id="dash-task-view-menu" role="menu" aria-label="Task appearance" hidden>
                      <button type="button" role="menuitemradio" data-dashboard-task-view="list">
                        <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-format_list_bulleted"></use></svg>
                        <span>List View</span><span class="dashboard-task-view-check" aria-hidden="true"><svg class="material-icon" viewBox="0 0 24 24"><use href="#material-icon-check"></use></svg></span>
                      </button>
                      <button type="button" role="menuitemradio" data-dashboard-task-view="board">
                        <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-view_kanban"></use></svg>
                        <span>Board View</span><span class="dashboard-task-view-check" aria-hidden="true"><svg class="material-icon" viewBox="0 0 24 24"><use href="#material-icon-check"></use></svg></span>
                      </button>
                      <button type="button" role="menuitemradio" data-dashboard-task-view="grid">
                        <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-grid_view"></use></svg>
                        <span>Grid View</span><span class="dashboard-task-view-check" aria-hidden="true"><svg class="material-icon" viewBox="0 0 24 24"><use href="#material-icon-check"></use></svg></span>
                      </button>
                      <button type="button" role="menuitemradio" data-dashboard-task-view="timeline">
                        <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-timeline"></use></svg>
                        <span>Timeline View</span><span class="dashboard-task-view-check" aria-hidden="true"><svg class="material-icon" viewBox="0 0 24 24"><use href="#material-icon-check"></use></svg></span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div id="dash-upcoming"></div>
              <button class="btn primary dashboard-task-create-btn has-icon" id="dash-add-task"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-add"></use></svg>New Task</button>
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
              <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-search"></use></svg>
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
          <button class="btn primary has-icon" id="btn-add-task"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-add"></use></svg>New Task</button>
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
              <button class="btn cal-nav-btn has-icon" id="cal-prev" type="button" aria-label="Previous month"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-arrow_back_ios_new"></use></svg><span>Prev</span></button>
              <button class="btn cal-nav-btn has-icon" id="cal-next" type="button" aria-label="Next month"><span>Next</span><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-arrow_forward_ios"></use></svg></button>
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
      <p id="task-modal-description">Give your task a name and set its priority and due date.</p>
    </div>
    <input type="hidden" name="id">
    <div id="task-edit-fields">
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
          <button type="button" class="btn small has-icon" id="task-checklist-add"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-add"></use></svg>Add item</button>
        </div>
        <div class="task-checklist-edit-items" id="task-checklist-edit-items"></div>
      </div>
    </div>
    <div class="task-readonly-details" id="task-readonly-details" hidden>
      <p class="task-readonly-description" id="task-readonly-description"></p>
      <dl class="task-readonly-meta">
        <div><dt>Priority</dt><dd id="task-readonly-priority"></dd></div>
        <div><dt>Due date</dt><dd id="task-readonly-due"></dd></div>
      </dl>
      <div class="task-readonly-checklist" id="task-readonly-checklist-wrap" hidden>
        <h4>Checklist</h4>
        <ul id="task-readonly-checklist"></ul>
      </div>
    </div>
    <div class="task-modal-actions">
      <button class="btn primary" id="btn-task-save">Save</button>
      <button type="button" class="btn task-action-button task-modal-status-button" id="btn-task-status" hidden></button>
      <button type="button" class="btn danger has-icon" id="btn-delete-task"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-delete"></use></svg>Delete</button>
      <button type="button" class="btn" id="btn-task-close" data-close hidden>Close</button>
    </div>
  </form>
</div>

<div class="modal-backdrop" id="reminder-task-modal">
  <section class="modal reminder-task-card" role="dialog" aria-modal="true" aria-labelledby="reminder-task-title">
    <div class="reminder-task-kicker"><span class="reminder-task-kicker-dot"></span> Task reminder</div>
    <div class="reminder-task-head">
      <h3 id="reminder-task-title"></h3>
      <button class="reminder-task-close" type="button" data-close aria-label="Close task details"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-close"></use></svg></button>
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

<div class="modal-backdrop" id="profile-information-modal">
  <section class="modal account-modal profile-information-modal" role="dialog" aria-modal="true" aria-labelledby="profile-information-title">
    <div class="account-modal-header">
      <div class="account-avatar" id="profile-info-avatar" aria-hidden="true"><span class="account-avatar-fallback"></span><img alt="" hidden></div>
      <div>
        <h3 id="profile-information-title">Profile information</h3>
        <p class="account-identity-name" id="profile-info-name"></p>
        <p class="account-identity-username" id="profile-info-username"></p>
      </div>
      <button class="reminder-task-close" type="button" data-close aria-label="Close profile information"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-close"></use></svg></button>
    </div>
    <div class="profile-info-bio">
      <h4>Bio</h4>
      <p id="profile-info-bio">No bio added yet.</p>
    </div>
    <p class="auth-error" id="profile-info-error" role="alert" hidden></p>
    <div class="modal-actions">
      <button class="btn primary" id="profile-info-manage" type="button">Manage account</button>
    </div>
  </section>
</div>

<div class="modal-backdrop" id="account-modal">
  <section class="modal account-modal" role="dialog" aria-modal="true" aria-labelledby="account-modal-title">
    <div class="account-modal-header">
      <div class="account-avatar" id="account-avatar" aria-hidden="true"><span class="account-avatar-fallback"></span><img alt="" hidden></div>
      <div>
        <h3 id="account-modal-title">Manage account</h3>
        <p class="account-identity-name" id="account-identity-name"></p>
        <p class="account-identity-username" id="account-identity-username"></p>
      </div>
      <button class="reminder-task-close" type="button" data-close aria-label="Close account details"><svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-close"></use></svg></button>
    </div>
    <div class="account-tabs" role="tablist" aria-label="Account settings">
      <button class="account-tab active" id="profile-tab" type="button" role="tab" aria-selected="true" aria-controls="profile-form" data-account-tab="profile">Profile information</button>
      <button class="account-tab" id="security-tab" type="button" role="tab" aria-selected="false" aria-controls="password-form" data-account-tab="security" tabindex="-1">Account security</button>
    </div>
    <form id="profile-form" class="account-form" role="tabpanel" aria-labelledby="profile-tab">
      <h4>Profile information</h4>
      <div class="profile-picture-editor">
        <div class="profile-picture-wrap">
          <div class="account-avatar profile-picture-preview" id="profile-picture-preview" aria-hidden="true"><span class="account-avatar-fallback"></span><img alt="" hidden></div>
          <label class="profile-picture-select" for="profile-picture-input" aria-label="Change profile picture" title="Change profile picture">
            <svg class="material-icon" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-edit"></use></svg>
          </label>
          <input id="profile-picture-input" class="sr-only" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
        </div>
        <div class="profile-picture-actions">
          <button class="profile-picture-remove" id="remove-profile-picture" type="button" hidden>Remove picture</button>
        </div>
      </div>
      <p class="auth-error" id="profile-picture-error" role="alert" hidden></p>
      <p class="form-success" id="profile-picture-success" role="status" hidden>Profile picture updated.</p>
      <label for="profile-username">Username</label>
      <input id="profile-username" name="username" required maxlength="50" autocomplete="username">
      <label for="profile-display-name">Display name</label>
      <input id="profile-display-name" name="display_name" required maxlength="80" autocomplete="name">
      <label for="profile-bio">Bio</label>
      <textarea id="profile-bio" name="bio" rows="3" maxlength="500" placeholder="Tell us a little about yourself"></textarea>
      <p class="auth-error" id="profile-error" role="alert" hidden></p>
      <p class="form-success" id="profile-success" role="status" hidden>Profile updated successfully.</p>
      <button class="btn primary" type="submit">Save profile</button>
    </form>
    <form id="password-form" class="account-form" role="tabpanel" aria-labelledby="security-tab" hidden>
      <h4>Account security</h4>
      <label for="current-password">Current password</label>
      <div class="password-input-wrap">
        <input id="current-password" name="current_password" type="password" required autocomplete="current-password">
        <button class="password-visibility-toggle" type="button" data-toggle-password="current-password" aria-label="Show current password" aria-pressed="false">
          <svg class="material-icon eye-open" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility"></use></svg>
          <svg class="material-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility_off"></use></svg>
        </button>
      </div>
      <label for="new-password">New password</label>
      <div class="password-input-wrap">
        <input id="new-password" name="new_password" type="password" required minlength="8" autocomplete="new-password">
        <button class="password-visibility-toggle" type="button" data-toggle-password="new-password" aria-label="Show new password" aria-pressed="false">
          <svg class="material-icon eye-open" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility"></use></svg>
          <svg class="material-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility_off"></use></svg>
        </button>
      </div>
      <label for="confirm-password">Confirm new password</label>
      <div class="password-input-wrap">
        <input id="confirm-password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
        <button class="password-visibility-toggle" type="button" data-toggle-password="confirm-password" aria-label="Show confirmation password" aria-pressed="false">
          <svg class="material-icon eye-open" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility"></use></svg>
          <svg class="material-icon eye-closed" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-visibility_off"></use></svg>
        </button>
      </div>
      <p class="auth-error" id="password-error" role="alert" hidden></p>
      <p class="form-success" id="password-success" role="status" hidden>Password updated successfully.</p>
      <button class="btn primary" type="submit">Update password</button>
    </form>
  </section>
</div>

<script src="assets/app/icons.js?v=1"></script>
<script src="assets/app/app-core.js?v=58"></script>
<script src="assets/app/account-navigation.js?v=57"></script>
<script src="assets/app/helpers.js?v=56"></script>
<script src="assets/app/dashboard.js?v=58"></script>
<script src="assets/app/tasks.js?v=57"></script>
<script src="assets/app/notes.js?v=56"></script>
<script src="assets/app/calendar.js?v=56"></script>
<script src="assets/app/history.js?v=57"></script>
<script src="assets/app/modals.js?v=58"></script>
<script src="assets/app/events.js?v=57"></script>
<script src="assets/app/init.js?v=56"></script>
</body>
</html>
