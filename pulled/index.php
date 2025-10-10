<?php
// index.php - User dashboard
include 'auth.php';
require_any_role(['teamlead','member']);
include 'head.php';
include 'db.php';

$user_id = $_SESSION['user_id'];
$role = get_user_role();

// List tasks
$stmt = $conn->prepare('SELECT * FROM tasks WHERE assigned_to = ? AND priority != "DONE" ORDER BY updated_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$tasks = $res->fetch_all(MYSQLI_ASSOC);

?>
<style>
    /* Modern Design System */
    .main-container {
        max-width: 1400px;
        margin: 96px auto; /* 1 inch margins */
        padding: 0 1rem;
    }
    
    .task-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .task-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .task-table th {
        background: #f8f9fa;
        color: #555;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .task-table td {
        padding: 1rem;
        border-bottom: 1px solid #f8f9fa;
        font-size: 0.9rem;
    }
    
    .task-table tr:hover {
        background: #f8f9fa;
    }
    
    .task-table tr:last-child td {
        border-bottom: none;
    }
    
    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.7rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid;
    }
    
    .priority-low { background: #f8f9fa; color: #6c757d; border-color: #e9ecef; }
    .priority-mid { background: #e7f1ff; color: #0d6efd; border-color: #cfe2ff; }
    .priority-high { background: #fff3cd; color: #856404; border-color: #ffeaa7; }
    .priority-prio { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    .priority-pend { background: #e8f4f4; color: #008080; border-color: #b8d8d8; }
    
    .task-link {
        color: #008080;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.2s ease;
    }
    
    .task-link:hover {
        color: #006666;
    }
    
    .action-btn {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 0.4rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }
    
    .action-btn:hover {
        background: #e9ecef;
        color: #008080;
    }
    
    /* Single line date info */
    .date-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }
    
    .date-main {
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .date-days {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 900px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .notes-container {
        flex: 1;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        background: #f8f9fa;
    }
    
    .note-item {
        border-bottom: 1px solid #dee2e6;
        padding: 0.8rem 0;
        font-size: 0.8rem;
        word-wrap: break-word;
    }
    
    .note-item:last-child {
        border-bottom: none;
    }
    
    .note-user {
        font-weight: 600;
        color: #008080;
        margin-bottom: 0.2rem;
        font-size: 0.85rem;
    }
    
    .note-date {
        font-size: 0.7rem;
        color: #6c757d;
        margin-bottom: 0.3rem;
    }
    
    .note-text {
        color: #333;
        line-height: 1.4;
    }
    
    .form-group {
        margin-bottom: 1rem;
        flex-shrink: 0;
        margin: 0 6px; /* 6px left and right margins */
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 0.85rem;
        resize: none;
        transition: border-color 0.2s ease;
        line-height: 1.4;
        height: 100px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #008080;
        box-shadow: 0 0 0 2px rgba(0,128,128,0.1);
    }
    
    .btn {
        padding: 0.6rem 1.25rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .btn-primary {
        background: #008080;
        color: white;
    }
    
    .btn-primary:hover {
        background: #006666;
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #545b62;
    }
    
    .btn-group {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        margin: 0 6px; /* 6px left and right margins */
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 2.5rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.1rem;
    }
    
    .empty-state p {
        margin: 0;
        font-size: 0.9rem;
    }
</style>

<div class="main-container">
    <!-- Task Table -->
    <div class="task-table-container">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <i class="fa fa-check-circle"></i>
                <h3>No Active Tasks</h3>
                <p>All tasks are completed! Great work!</p>
            </div>
        <?php else: ?>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Task</th>
                        <th>Notes</th>
                        <th>Updated</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                    <?php
                        $priorityClasses = [
                            'LOW' => 'priority-low',
                            'MID' => 'priority-mid', 
                            'HIGH' => 'priority-high',
                            'PRIO' => 'priority-prio',
                            'PEND' => 'priority-pend'
                        ];
                        $priorityIcons = [
                            'LOW' => 'fa-arrow-down',
                            'MID' => 'fa-minus',
                            'HIGH' => 'fa-arrow-up',
                            'PRIO' => 'fa-exclamation',
                            'PEND' => 'fa-hourglass-half'
                        ];
                        $priorityClass = $priorityClasses[$t['priority']] ?? 'priority-low';
                        $priorityIcon = $priorityIcons[$t['priority']] ?? 'fa-minus';
                    ?>
                    <tr>
                        <td>
                            <span class="priority-badge <?php echo $priorityClass; ?>">
                                <i class="fa <?php echo $priorityIcon; ?>"></i>
                                <?php echo htmlspecialchars($t['priority']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($t['link']); ?>" target="_blank" class="task-link">
                                <i class="fa fa-external-link-alt"></i>
                                <?php echo htmlspecialchars($t['name']); ?>
                            </a>
                        </td>
                        <td>
                            <button class="action-btn" onclick="showNotes(<?php echo $t['id']; ?>);return false;" title="View/Add Notes">
                                <i class="fa fa-sticky-note"></i>
                            </button>
                        </td>
                        <td>
                            <div class="date-info">
                                <span class="date-main">
                                    <?php echo date('m/d/y', strtotime($t['updated_at'])); ?>
                                </span>
                                <button class="action-btn" onclick="showCalendar(<?php echo $t['id']; ?>);return false;" title="Change Date">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="date-info">
                                <span class="date-main">
                                    <?php echo date('m/d/y - H:i', strtotime($t['assigned_at'])); ?>
                                </span>
                                <span class="date-days">
                                    (<?php echo floor((time()-strtotime($t['assigned_at']))/86400); ?> days)
                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Wide Notes Modal -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa fa-sticky-note" style="color:#008080"></i> Task Notes</h3>
        </div>
        <div class="modal-body">
            <div class="notes-container" id="notesContent">
                <!-- Notes will be loaded here -->
            </div>
            <form id="noteForm" method="post">
                <input type="hidden" name="task_id" id="noteTaskId">
                <div class="form-group">
                    <textarea name="note" id="noteText" class="form-control" placeholder="Add a new note..." required></textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Note
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeNotes()">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendarModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="fa fa-calendar" style="color:#008080"></i> Move Task Date</h3>
        </div>
        <div class="modal-body">
            <form id="calendarForm" method="post">
                <input type="hidden" name="task_id" id="calendarTaskId">
                <div class="form-group">
                    <input type="date" name="new_date" id="calendarDate" class="form-control" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Confirm
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeCalendar()">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentTaskId = 0;

function showNotes(taskId) {
    currentTaskId = taskId;
    document.getElementById('notesModal').style.display = 'flex';
    document.getElementById('noteTaskId').value = taskId;
    loadNotes(taskId);
}

function closeNotes() {
    document.getElementById('notesModal').style.display = 'none';
    document.getElementById('noteText').value = '';
}

function loadNotes(taskId) {
    fetch('notes.php?task_id=' + taskId)
        .then(response => response.json())
        .then(notes => {
            let html = '';
            if (notes.length === 0) {
                html = '<div class="note-item" style="text-align:center;color:#6c757d;">No notes yet</div>';
            } else {
                notes.forEach(note => {
                    const date = new Date(note.created_at.replace(' ', 'T'));
                    const dateStr = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear().toString().slice(-2)} - ${date.getHours().toString().padStart(2,'0')}:${date.getMinutes().toString().padStart(2,'0')}`;
                    
                    html += `
                        <div class="note-item">
                            <div class="note-user">${note.username}</div>
                            <div class="note-date">${dateStr}</div>
                            <div class="note-text">${note.note}</div>
                        </div>
                    `;
                });
            }
            document.getElementById('notesContent').innerHTML = html;
            // Scroll to bottom to see latest notes
            document.getElementById('notesContent').scrollTop = document.getElementById('notesContent').scrollHeight;
        })
        .catch(error => {
            console.error('Error loading notes:', error);
            document.getElementById('notesContent').innerHTML = '<div class="note-item" style="color:#dc3545;">Error loading notes</div>';
        });
}

// Handle note form submission
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
            document.getElementById('noteText').value = '';
            loadNotes(currentTaskId); // Reload notes
        } else {
            alert('Error adding note: ' + result.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding note');
    });
});

function showCalendar(taskId) {
    document.getElementById('calendarModal').style.display = 'flex';
    document.getElementById('calendarTaskId').value = taskId;
    // Set today's date as default
    document.getElementById('calendarDate').valueAsDate = new Date();
}

function closeCalendar() {
    document.getElementById('calendarModal').style.display = 'none';
}

// Handle calendar form submission
document.getElementById('calendarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update_task_date.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Date updated successfully!');
            closeCalendar();
            location.reload(); // Reload page to show updated order
        } else {
            alert('Error updating date: ' + result.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating date');
    });
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script>

<?php include 'foot.php'; ?>