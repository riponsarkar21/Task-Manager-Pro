-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Tasks Table (Default tasks will be inserted via PHP logic on first run if empty)
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift CHAR(1) NOT NULL, -- 'A', 'B', 'C', 'G'
    text TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Reports/Logs Table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_date DATE NOT NULL,
    shift CHAR(1) NOT NULL,
    total_tasks INT NOT NULL,
    completed_tasks INT NOT NULL,
    percentage INT NOT NULL,
    checked_ids TEXT NOT NULL, -- Will store JSON array of task IDs
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Users
INSERT IGNORE INTO users (name) VALUES ('Operator 1'), ('Manager'), ('Supervisor');

-- Insert Default Tasks for Shifts A, B, C, G
INSERT IGNORE INTO tasks (shift, text) VALUES 
('A', 'Check Machine Status'), ('A', 'Clean Work Area'), ('A', 'Log Production Data'),
('B', 'Check Machine Status'), ('B', 'Clean Work Area'), ('B', 'Log Production Data'),
('C', 'Check Machine Status'), ('C', 'Clean Work Area'), ('C', 'Log Production Data'),
('G', 'Check Machine Status'), ('G', 'Clean Work Area'), ('G', 'Log Production Data');