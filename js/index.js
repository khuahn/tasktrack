// Task Page JavaScript - Safe and Isolated
(function() {
    'use strict';
    
    let currentTaskId = 0;
    let currentTaskName = '';
    let noteAddedInCurrentSession = false;
    
    /**
     * Show notes modal and load existing notes for a task
     */
    function showNotes(taskId, taskName = '') {
        currentTaskId = taskId;
        currentTaskName = taskName;
        noteAddedInCurrentSession = false;
        
        document.getElementById('notesModal').style.display = 'flex';
        document.getElementById('noteTaskId').value = taskId;
        
        // Set modal title to actual task name
        if (taskName && taskName !== '') {
            document.getElementById('modalTaskName').textContent = taskName;
        } else {
            document.getElementById('modalTaskName').textContent = 'Task Notes';
        }
        
        loadNotes(taskId);
    }
    
    /**
     * Close notes modal and clear form
     */
    function closeNotes() {
        document.getElementById('notesModal').style.display = 'none';
        document.getElementById('noteText').value = '';
        document.getElementById('modalTaskName').textContent = 'Task Notes';
        
        // If a note was added during this session, reload the page to update task order
        if (noteAddedInCurrentSession) {
            setTimeout(() => {
                location.reload();
            }, 300);
        }
        
        currentTaskName = '';
        noteAddedInCurrentSession = false;
    }
    
    /**
     * Load notes for a specific task via AJAX
     */
    function loadNotes(taskId) {
        // Use global utility if available, otherwise fallback
        if (window.GlobalUtils && window.GlobalUtils.apiRequest) {
            window.GlobalUtils.apiRequest('notes.php?task_id=' + taskId)
                .then(notes => displayNotes(notes))
                .catch(error => {
                    console.error('Error loading notes:', error);
                    document.getElementById('notesContent').innerHTML = 
                        '<div class="note-item" style="color:var(--danger);">Error loading notes</div>';
                });
        } else {
            // Fallback to direct fetch
            fetch('notes.php?task_id=' + taskId)
                .then(response => response.json())
                .then(notes => displayNotes(notes))
                .catch(error => {
                    console.error('Error loading notes:', error);
                    document.getElementById('notesContent').innerHTML = 
                        '<div class="note-item" style="color:var(--danger);">Error loading notes</div>';
                });
        }
    }
    
    /**
     * Display notes in the modal
     */
    function displayNotes(notes) {
        let html = '';
        if (notes.length === 0) {
            html = '<div class="note-item" style="text-align:center;color:var(--gray-600);">No notes yet</div>';
        } else {
            notes.forEach(note => {
                // Use global date formatting if available
                const dateStr = window.GlobalUtils ? 
                    window.GlobalUtils.formatDate(note.created_at, 'datetime') :
                    formatDateFallback(note.created_at);
                
                html += `
                    <div class="note-item">
                        <div class="note-user">${escapeHtml(note.username)}</div>
                        <div class="note-date">${dateStr}</div>
                        <div class="note-text">${escapeHtml(note.note)}</div>
                    </div>
                `;
            });
        }
        document.getElementById('notesContent').innerHTML = html;
        document.getElementById('notesContent').scrollTop = document.getElementById('notesContent').scrollHeight;
    }
    
    /**
     * Fallback date formatting
     */
    function formatDateFallback(dateString) {
        try {
            const date = new Date(dateString.replace(' ', 'T'));
            return `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear().toString().slice(-2)} - ${date.getHours().toString().padStart(2,'0')}:${date.getMinutes().toString().padStart(2,'0')}`;
        } catch (e) {
            return dateString;
        }
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    /**
     * Show temporary success message
     */
    function showTempMessage(message, type = 'success') {
        // Use global notification if available
        if (window.GlobalUtils && window.GlobalUtils.showNotification) {
            window.GlobalUtils.showNotification(message, type, 3000);
        } else {
            // Fallback notification
            const messageDiv = document.createElement('div');
            messageDiv.textContent = message;
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--success)' : 'var(--danger)'};
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-lg);
                z-index: 1001;
                font-size: 0.9rem;
                animation: fadeInOut 3s ease-in-out;
            `;
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 3000);
        }
    }
    
    // ===== EVENT LISTENERS =====
    
    /**
     * Handle note form submission
     */
    document.getElementById('noteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('add_note.php', { 
            method: 'POST', 
            body: formData 
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Clear form but DO NOT close modal
                document.getElementById('noteText').value = '';
                
                // Mark that a note was added in this session
                noteAddedInCurrentSession = true;
                
                // Show success message
                showTempMessage('Note added successfully!', 'success');
                
                // Reload the notes to show the new one
                loadNotes(currentTaskId);
                
            } else {
                showTempMessage('Error adding note: ' + result.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showTempMessage('Error adding note', 'error');
        });
    });
    
    /**
     * Close modals when clicking outside modal content
     */
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeNotes();
        }
    });
    
    /**
     * Close modal when pressing Escape key
     */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('notesModal').style.display === 'flex') {
            closeNotes();
        }
    });
    
    // Expose functions to global scope for onclick handlers
    window.showNotes = showNotes;
    window.closeNotes = closeNotes;
    
    console.log('Task page JavaScript loaded successfully');
})();
