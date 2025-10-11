-- Updates for TaskTrack to support restore audit and done-page features
-- Safe to run repeatedly (IF NOT EXISTS guards)

-- 1) Audit trail for task restore events
CREATE TABLE IF NOT EXISTS task_events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  event_type ENUM('RESTORE') NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_task_events_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_task_events_task_created (task_id, created_at),
  INDEX idx_task_events_type_created (event_type, created_at)
);

-- No schema changes required to tasks/notes/users; existing columns are used.
-- Application now logs a row into task_events on restore.
