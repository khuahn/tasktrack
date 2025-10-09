const TASKS_KEY = "tasktrack.tasks.v1";
const THEME_KEY = "tasktrack.theme.v1";

function readLocalStorage(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    return raw ? JSON.parse(raw) : fallback;
  } catch {
    return fallback;
  }
}

function writeLocalStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    /* no-op */
  }
}

function createId() {
  return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

const state = {
  tasks: readLocalStorage(TASKS_KEY, []),
  filter: "all", // all | active | completed
  theme: readLocalStorage(THEME_KEY, null), // 'light' | 'dark' | null (auto)
};

const els = {
  form: document.getElementById("task-form"),
  input: document.getElementById("task-input"),
  list: document.getElementById("task-list"),
  clearCompleted: document.getElementById("clear-completed"),
  count: document.getElementById("task-count"),
  filters: document.querySelector(".filters"),
  themeToggle: document.getElementById("theme-toggle"),
  themeMeta: document.querySelector('meta[name="theme-color"]'),
};

function getSystemTheme() {
  return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches
    ? "dark"
    : "light";
}

function applyTheme(theme) {
  const next = theme ?? getSystemTheme();
  document.documentElement.setAttribute("data-theme", next);
  const bg = getComputedStyle(document.documentElement).getPropertyValue("--bg").trim();
  if (els.themeMeta) els.themeMeta.setAttribute("content", bg);
}

function toggleTheme() {
  const current = document.documentElement.getAttribute("data-theme") || getSystemTheme();
  const next = current === "dark" ? "light" : "dark";
  state.theme = next;
  writeLocalStorage(THEME_KEY, next);
  applyTheme(next);
}

function saveTasks() {
  writeLocalStorage(TASKS_KEY, state.tasks);
}

function render() {
  const tasks = state.tasks.filter((t) => {
    if (state.filter === "active") return !t.done;
    if (state.filter === "completed") return t.done;
    return true;
  });

  els.list.innerHTML = tasks
    .map(
      (t) => `
      <li class="task-row" data-id="${t.id}">
        <input class="toggle" id="cb-${t.id}" type="checkbox" ${t.done ? "checked" : ""} aria-label="Toggle ${escapeHtml(
        t.title
      )}" />
        <label class="task-title ${t.done ? "is-done" : ""}" for="cb-${t.id}">${escapeHtml(t.title)}</label>
        <button class="icon-btn delete" aria-label="Delete ${escapeHtml(t.title)}" title="Delete">×</button>
      </li>`
    )
    .join("");

  const remaining = state.tasks.filter((t) => !t.done).length;
  const total = state.tasks.length;
  els.count.textContent = `${total === 0 ? 0 : remaining}/${total} remaining`;

  // Update chips active state
  for (const btn of els.filters.querySelectorAll(".chip")) {
    const isActive = btn.dataset.filter === state.filter;
    btn.classList.toggle("is-active", isActive);
    btn.setAttribute("aria-selected", String(isActive));
  }
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, (c) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  })[c]);
}

function addTask(title) {
  const task = { id: createId(), title: title.trim(), done: false };
  if (!task.title) return;
  state.tasks.unshift(task);
  saveTasks();
  render();
}

function toggleTask(id) {
  const t = state.tasks.find((x) => x.id === id);
  if (!t) return;
  t.done = !t.done;
  saveTasks();
  render();
}

function deleteTask(id) {
  state.tasks = state.tasks.filter((x) => x.id !== id);
  saveTasks();
  render();
}

function clearCompleted() {
  state.tasks = state.tasks.filter((x) => !x.done);
  saveTasks();
  render();
}

function attachEvents() {
  els.form.addEventListener("submit", (e) => {
    e.preventDefault();
    addTask(els.input.value);
    els.input.value = "";
    els.input.focus();
  });

  els.list.addEventListener("click", (e) => {
    const target = e.target;
    const row = target.closest(".task-row");
    if (!row) return;
    const id = row.getAttribute("data-id");

    if (target.classList.contains("delete")) {
      deleteTask(id);
      return;
    }

    if (target.classList.contains("toggle")) {
      toggleTask(id);
      return;
    }
  });

  els.filters.addEventListener("click", (e) => {
    const btn = e.target.closest(".chip");
    if (!btn) return;
    state.filter = btn.dataset.filter;
    render();
  });

  els.clearCompleted.addEventListener("click", () => clearCompleted());

  els.themeToggle.addEventListener("click", () => toggleTheme());

  // Keep in sync with system changes if user hasn't explicitly chosen
  const mql = window.matchMedia("(prefers-color-scheme: dark)");
  mql.addEventListener?.("change", () => {
    const saved = readLocalStorage(THEME_KEY, null);
    if (saved === null) applyTheme(null);
  });
}

// Init
applyTheme(state.theme);
attachEvents();
render();
