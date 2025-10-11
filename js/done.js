// done.js - shared JS for done pages (read-only notes + restore confirm)
(function(){
  'use strict';

  function showNotes(taskId, taskName) {
    const modal = document.getElementById('notesModal');
    const title = document.getElementById('modalTaskName');
    if (title) title.textContent = taskName || 'Task Notes';
    modal.style.display = 'flex';
    fetch('notes.php?task_id=' + taskId)
      .then(r => r.json())
      .then(notes => {
        let html = '';
        if (!notes || notes.length === 0) {
          html = '<div class="note-item" style="text-align:center;color:var(--gray-600);">No notes yet</div>';
        } else {
          notes.forEach(n => {
            html += `
              <div class="note-item">
                <div class="note-user">${escapeHtml(n.username)}</div>
                <div class="note-date">${formatDate(n.created_at)}</div>
                <div class="note-text">${escapeHtml(n.note)}</div>
              </div>
            `;
          });
        }
        document.getElementById('notesContent').innerHTML = html;
        const el = document.getElementById('notesContent');
        el.scrollTop = el.scrollHeight;
      });
  }

  function closeNotes(){
    document.getElementById('notesModal').style.display = 'none';
  }

  function formatDate(dt) {
    try {
      const d = new Date(String(dt).replace(' ','T'));
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

  function confirmRestore(url) {
    if (confirm('Restore this task?')) {
      window.location.href = url;
    }
  }

  // Close modal on overlay/ESC
  document.addEventListener('click', function(e){
    if (e.target?.classList?.contains('modal')) closeNotes();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeNotes();
  });

  window.showNotes = showNotes;
  window.closeNotes = closeNotes;
  window.confirmRestore = confirmRestore;
})();
