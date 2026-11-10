CREATE DATABASE IF NOT EXISTS it_tickets CHARACTER SET utf8mb4;
USE it_tickets;

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    created_by VARCHAR(100) NOT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'low',
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_role ENUM('user','it') NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

-- Sample data (your old array)
INSERT INTO tickets (title, description, created_by, priority, status) VALUES
('Ticket 1', 'Detailed description of ticket 1 goes here.', 'Name', 'low', 'open'),
('Ticket 2', 'Detailed description of ticket 2 goes here.', 'Name', 'medium', 'closed'),
('Ticket 3', 'Detailed description of ticket 3 goes here.', 'Name', 'high', 'open');
