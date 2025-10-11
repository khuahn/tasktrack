// tskmgt.js - Task Management Page JS
(function() {
  'use strict';

  // ===== Notes (reuse logic from index.js but minimal) =====
  let currentTaskId = 0;
  let currentTaskName = '';
  let noteAddedInCurrentSession = false;

  function showNotes(taskId, taskName = '') {
    currentTaskId = taskId;
    currentTaskName = taskName;
    noteAddedInCurrentSession = false;

    document.getElementById('notesModal').style.display = 'flex';
    document.getElementById('noteTaskId').value = taskId;
    document.getElementById('modalTaskName').textContent = taskName || 'Task Notes';
    loadNotes(taskId);
  }

  function closeNotes() {
    document.getElementById('notesModal').style.display = 'none';
    document.getElementById('noteText').value = '';
    document.getElementById('modalTaskName').textContent = 'Task Notes';
    if (noteAddedInCurrentSession) {
      setTimeout(() => location.reload(), 300);
    }
    currentTaskName = '';
    noteAddedInCurrentSession = false;
  }

  function loadNotes(taskId) {
    fetch('notes.php?task_id=' + taskId)
      .then(r => r.json())
      .then(displayNotes)
      .catch(() => {
        document.getElementById('notesContent').innerHTML = '<div class="note-item" style="color:var(--danger);">Error loading notes</div>';
      });
  }

  function displayNotes(notes) {
    let html = '';
    if (!notes || notes.length === 0) {
      html = '<div class="note-item" style="text-align:center;color:var(--gray-600);">No notes yet</div>';
    } else {
      notes.forEach(n => {
        const dateStr = formatDate(n.created_at);
        html += `
          <div class="note-item">
            <div class="note-user">${escapeHtml(n.username)}</div>
            <div class="note-date">${dateStr}</div>
            <div class="note-text">${escapeHtml(n.note)}</div>
          </div>
        `;
      });
    }
    const el = document.getElementById('notesContent');
    el.innerHTML = html;
    el.scrollTop = el.scrollHeight;
  }

  document.getElementById('noteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('add_note.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(res => {
        if (res && res.success) {
          noteAddedInCurrentSession = true;
          document.getElementById('noteText').value = '';
          loadNotes(currentTaskId);
        }
      });
  });

  function formatDate(dt) {
    try {
      const d = new Date(String(dt).replace(' ', 'T'));
      return `${(d.getMonth()+1).toString().padStart(2,'0')}/${d.getDate().toString().padStart(2,'0')}/${d.getFullYear().toString().slice(-2)} - ${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
    } catch (e) { return dt; }
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  // Expose for onclick
  window.showNotes = showNotes;
  window.closeNotes = closeNotes;

  // ===== Edit Modal =====
  function openEdit(id, name, link, priority, assignedTo) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('editTaskId').value = id;
    document.getElementById('editName').value = name || '';
    document.getElementById('editLink').value = link || '';
    document.getElementById('editPriority').value = priority || 'LOW';
    document.getElementById('editAssignedTo').value = String(assignedTo || '0');
  }

  function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
  }

  document.getElementById('editForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editTaskId').value;
    const formData = new FormData(this);
    fetch(`?action=edit&id=${encodeURIComponent(id)}`, { method: 'POST', body: formData })
      .then(() => { closeEdit(); location.reload(); });
  });

  // Auto-submit filters on change
  const filterForm = document.getElementById('filterForm');
  const filterPriority = document.getElementById('filterPriority');
  const filterAssignedTo = document.getElementById('filterAssignedTo');
  [filterPriority, filterAssignedTo].forEach(el => {
    if (el) el.addEventListener('change', () => filterForm?.submit());
  });

  // Close modals by clicking outside or Esc
  document.addEventListener('click', function(e) {
    if (e.target.classList?.contains('modal')) {
      if (e.target.id === 'notesModal') closeNotes();
      if (e.target.id === 'editModal') closeEdit();
    }
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeNotes(); closeEdit(); }
  });

  window.openEdit = openEdit;
  window.closeEdit = closeEdit;
})();
