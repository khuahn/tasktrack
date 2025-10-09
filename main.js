// main.js - Main JavaScript file
// Notes modal AJAX
function showNotes(taskId) {
    document.getElementById('notesModal').style.display = 'flex';
    document.getElementById('noteTaskId').value = taskId;
    fetchNotes(taskId);
}
function closeNotes() {
    document.getElementById('notesModal').style.display = 'none';
}
function fetchNotes(taskId) {
    fetch('notes.php?task_id=' + taskId)
        .then(r => r.json())
        .then(notes => {
            let html = '';
            notes.forEach(n => {
                html += `<div style='border-bottom:1px solid #eee;padding:0.5em 0;'>`
                    + `<b>${n.username}</b> <span style='color:#888;'>${formatDate(n.created_at)}</span><br>`
                    + `<span>${n.note}</span></div>`;
            });
            document.getElementById('notesContent').innerHTML = html;
        });
}
document.getElementById('noteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    let taskId = document.getElementById('noteTaskId').value;
    let note = document.getElementById('noteText').value;
    fetch('notes.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `task_id=${taskId}&note=${encodeURIComponent(note)}`
    }).then(() => {
        document.getElementById('noteText').value = '';
        fetchNotes(taskId);
    });
});
// Calendar modal AJAX
function showCalendar(taskId) {
    document.getElementById('calendarModal').style.display = 'flex';
    document.getElementById('calendarTaskId').value = taskId;
}
function closeCalendar() {
    document.getElementById('calendarModal').style.display = 'none';
}
document.getElementById('calendarForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    let taskId = document.getElementById('calendarTaskId').value;
    let newDate = document.getElementById('calendarDate').value;
    fetch('update_task_date.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `task_id=${taskId}&new_date=${newDate}`
    }).then(() => {
        closeCalendar();
        location.reload();
    });
});
function formatDate(dt) {
    let d = new Date(dt.replace(' ','T'));
    return `${d.getMonth()+1}/${d.getDate()}/${d.getFullYear().toString().slice(-2)} - ${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
}
