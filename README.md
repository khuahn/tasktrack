# TaskTrack

Lightweight PHP/MySQL task tracker.

## Setup

1. Create a MySQL database and user.
2. Import `schema.sql`.
3. Configure environment variables for DB connection:

```bash
export DB_HOST=localhost
export DB_USER=tasktrack_user
export DB_PASS=your_password
export DB_NAME=tasktrack
```

## Run

Serve the folder with PHP (for local dev):

```bash
php -S 127.0.0.1:8000
```

## Security and Coding Principles

- No hardcoded DB credentials; uses environment variables.
- `utf8mb4` charset set on the DB connection.
- Prepared statements for dynamic SQL.
- CSRF tokens required on mutating POST forms.
- Output is escaped via `htmlspecialchars` where needed.

## UI

- Sleek, simple, modern layout.
- Responsive `container` width and refined footer.