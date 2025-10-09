# TaskTrack

A lightweight, modern task tracker that runs entirely in your browser.

## Features

- Add, complete, delete tasks
- Filter by all/active/completed
- Persistent storage via `localStorage`
- Light/Dark theme with system preference support
- Responsive, sleek UI with accessible focus states

## Getting started

Open `index.html` in your browser. No build step or server required.

### Optional: serve locally

If you prefer to use a local server (for proper caching and MIME types):

```bash
python3 -m http.server 5173
# then visit http://localhost:5173
```

## Project structure

- `index.html` – App shell and layout
- `styles.css` – Modern, responsive styling and theming
- `app.js` – Task logic, filtering, and persistence
- `favicon.svg` – App icon

## Development notes

- No external dependencies
- Works offline after first load (static assets only)
- Data is stored in your browser; clearing site data will reset tasks

## License

MIT