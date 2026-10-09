# TaskTrack

A task tracking web application built with **HTML, CSS, JavaScript, PHP, and MySQL**.

## Features
- **Dashboard** - statistics cards (total / to-do / in-progress / completed / overdue / notes), upcoming tasks, recent activity
- **Tasks** - create, edit, delete tasks; priorities (low/medium/high); due dates; statuses (To Do / In Progress / Done); filter by status; overdue badges; click the status dot to advance a task
- **Notes** - sticky-note style grid; create, edit, delete notes
- **Calendar** - monthly view with tasks shown on their due dates; click a task to edit it
- **History** - automatic activity log of every task/note action
- **Auth** - register / login with hashed passwords (PHP `password_hash` / `password_verify`) and session management

## Project structure
```
TaskTrack/
├── database.sql      # MySQL schema (run this first)
├── config.example.php # DB connection template
├── config.php        # Local DB connection (not committed)
├── auth.php          # session guard + activity logger
├── login.php         # login / register page
├── logout.php
├── index.php         # main app (SPA-style, 5 views)
├── api.php           # JSON backend (all CRUD endpoints)
└── assets/
    ├── style.css
    └── app.js
```

## Setup (XAMPP / WAMP / MAMP)
1. Copy the `TaskTrack` folder into your web root
   (XAMPP: `htdocs/`, WAMP: `www/`, MAMP: `htdocs/`).
2. Open phpMyAdmin and import `database.sql`
   (or run: `mysql -u root -p < database.sql`).
3. Copy `config.example.php` to `config.php` and set your local DB credentials.
4. Visit `http://localhost/TaskTrack/` in your browser.
5. Register an account and start tracking!

## API endpoints (api.php?action=...)
| Action | Method | Description |
|---|---|---|
| `stats` | GET | dashboard counters |
| `tasks` | GET | list tasks (`&month=YYYY-MM` for calendar) |
| `add_task` | POST | create task |
| `update_task` | POST | edit task / change status |
| `delete_task` | POST | delete task |
| `notes` | GET | list notes |
| `save_note` | POST | create or update note |
| `delete_note` | POST | delete note |
| `history` | GET | activity log |
