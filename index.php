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
<link rel="stylesheet" href="assets/style.css?v=147">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <button class="brand" id="brand-home" type="button" aria-label="TaskTrack dashboard"><span class="brand-name"><span class="brand-task"><span class="brand-t">T</span><span class="brand-a">a</span><span class="brand-s">s</span><span class="brand-k">k</span></span><span class="brand-track">Track</span></span></button>
    <div class="profile-control">
      <button class="user-chip" id="profile-toggle" type="button" data-username="<?= htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8') ?>" aria-expanded="false" aria-controls="account-menu" aria-label="Account options">
        <span class="profile-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
          <img id="sidebar-profile-picture" alt="" hidden>
        </span>
        <span class="user-name"><?= htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username'], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="profile-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
      </button>
      <div class="account-choice-menu" id="account-menu" hidden>
        <button class="account-choice" id="show-profile-information" type="button">Profile information</button>
        <button class="account-choice" id="manage-account" type="button">Manage account</button>
        <a class="logout account-choice-logout" href="logout.php">Log out</a>
      </div>
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
          <button type="button" class="btn small" id="task-checklist-add">+ Add item</button>
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
      <button type="button" class="btn danger" id="btn-delete-task">Delete</button>
      <button type="button" class="btn" id="btn-task-close" data-close hidden>Close</button>
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

<div class="modal-backdrop" id="profile-information-modal">
  <section class="modal account-modal profile-information-modal" role="dialog" aria-modal="true" aria-labelledby="profile-information-title">
    <div class="account-modal-header">
      <div class="account-avatar" id="profile-info-avatar" aria-hidden="true"><span class="account-avatar-fallback"></span><img alt="" hidden></div>
      <div>
        <h3 id="profile-information-title">Profile information</h3>
        <p class="account-identity-name" id="profile-info-name"></p>
        <p class="account-identity-username" id="profile-info-username"></p>
      </div>
      <button class="reminder-task-close" type="button" data-close aria-label="Close profile information">&times;</button>
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
      <button class="reminder-task-close" type="button" data-close aria-label="Close account details">&times;</button>
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
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 5 5 5M4 20l4.2-.9L19 8.3a2.1 2.1 0 0 0-3-3L5.2 16.1 4 20Z"/></svg>
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
          <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"/></svg>
        </button>
      </div>
      <label for="new-password">New password</label>
      <div class="password-input-wrap">
        <input id="new-password" name="new_password" type="password" required minlength="8" autocomplete="new-password">
        <button class="password-visibility-toggle" type="button" data-toggle-password="new-password" aria-label="Show new password" aria-pressed="false">
          <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"/></svg>
        </button>
      </div>
      <label for="confirm-password">Confirm new password</label>
      <div class="password-input-wrap">
        <input id="confirm-password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
        <button class="password-visibility-toggle" type="button" data-toggle-password="confirm-password" aria-label="Show confirmation password" aria-pressed="false">
          <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"/></svg>
        </button>
      </div>
      <p class="auth-error" id="password-error" role="alert" hidden></p>
      <p class="form-success" id="password-success" role="status" hidden>Password updated successfully.</p>
      <button class="btn primary" type="submit">Update password</button>
    </form>
  </section>
</div>

<script src="assets/app/app-core.js?v=56"></script>
<script src="assets/app/account-navigation.js?v=56"></script>
<script src="assets/app/helpers.js?v=56"></script>
<script src="assets/app/dashboard.js?v=56"></script>
<script src="assets/app/tasks.js?v=56"></script>
<script src="assets/app/notes.js?v=56"></script>
<script src="assets/app/calendar.js?v=56"></script>
<script src="assets/app/history.js?v=56"></script>
<script src="assets/app/modals.js?v=56"></script>
<script src="assets/app/events.js?v=56"></script>
<script src="assets/app/init.js?v=56"></script>
</body>
</html>
