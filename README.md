# TaskTrack

TaskTrack is a task and notes web app built with PHP, MySQL, HTML, CSS, and vanilla JavaScript. The PHP backend serves the app and a JSON API; the frontend loads data with `fetch`. User data is scoped to the signed-in account.

## Features

- **Dashboard:** task statistics, up to six upcoming unfinished tasks, a seven-day completed-task chart, recent notes, recent activity, and due-date reminders. Long upcoming-task descriptions are shortened to 20 characters followed by `...`.
- **Tasks:** create, edit, and delete tasks with a title, description, priority, and optional due date. Tasks have To Do, In Progress, and Done statuses.
- **Task checklists:** optionally add checklist items when creating or editing a task. Check items off from the Tasks view to update the task progress percentage. Without checklist items, tasks continue to work with their regular statuses. The task editor's Start task / Complete task button changes status; completing through this button asks for confirmation. The status dot in the Tasks view cycles through statuses.
- **Task filters:** search task titles and filter by status, priority, and due date.
- **Notes:** create, edit, and delete sticky-note-style notes. Open a note by selecting it.
- **Calendar:** browse months and view tasks on their due dates. Selecting a calendar task opens its details.
- **History:** review up to the latest 200 task and note activity entries, or clear the history.
- **Sign-in and registration:** create an account and sign in with session-based authentication. Passwords are stored as hashes.
- **Appearance and responsive layout:** toggle between light and dark themes; the selected theme is saved in the browser.

## Project structure

```text
TaskTrack/
├── api.php             # Authenticated JSON API
├── auth.php            # Session guard and activity logger
├── config.example.php  # Database configuration template
├── config.php          # Local database settings (create locally; don't commit)
├── database.sql        # MySQL database and table schema
├── index.php           # Main app and its Dashboard, Tasks, Notes, Calendar, History views
├── login.php           # Sign-in and registration
├── logout.php          # Ends the current session
└── assets/
    ├── app/            # Frontend scripts, loaded in order by index.php
    │   ├── app-core.js
    │   ├── account-navigation.js
    │   ├── helpers.js
    │   ├── dashboard.js
    │   ├── tasks.js
    │   ├── notes.js
    │   ├── calendar.js
    │   ├── history.js
    │   ├── modals.js
    │   ├── events.js
    │   └── init.js
    └── style.css       # App styles
```

## Requirements

- PHP with PDO MySQL enabled
- MySQL or MariaDB
- A local PHP web server (for example, XAMPP, WAMP, or MAMP)

## Setup (XAMPP / WAMP / MAMP)

1. Copy the project folder into the web server's document root (for XAMPP, `htdocs`).
2. Start the web server and MySQL.
3. Create the database and tables by importing `database.sql` with phpMyAdmin or the MySQL command-line client:

   ```sh
   mysql -u root -p < database.sql
   ```

4. Copy `config.example.php` to `config.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` to match your MySQL setup. Keep `config.php` local and out of version control.
5. Open `http://localhost/TaskTrack/` (adjust the URL if you used a different folder name).
6. Create an account from the sign-in page, then sign in.

The checklist table is included in `database.sql`; the API also creates it if it is missing, so an existing installation can receive the checklist table when the API is next used.

## API

All API routes are in `api.php?action=...` and require an authenticated session. GET requests return JSON data; write actions accept JSON POST bodies (or form POST data). Task responses include a `checklist` array.

| Action | Method | Purpose |
|---|---|---|
| `stats` | GET | Dashboard task counts and note count |
| `tasks` | GET | List the signed-in user's tasks; optionally pass `&month=YYYY-MM` for calendar tasks |
| `add_task` | POST | Create a task; optional `checklist` array can be included |
| `update_task` | POST | Update task fields/status; optionally replace its checklist |
| `toggle_checklist_item` | POST | Toggle a checklist item's completed state |
| `delete_task` | POST | Delete a task and its checklist items |
| `notes` | GET | List the signed-in user's notes |
| `save_note` | POST | Create or update a note |
| `delete_note` | POST | Delete a note |
| `history` | GET | Get the latest 200 activity entries |
| `clear_history` | POST | Clear activity history |
