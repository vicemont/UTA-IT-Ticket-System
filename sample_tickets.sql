-- Optional test data. user_id values match your users_staff rows (2 = victoria, 4 = joe, 5 = johndoe).
INSERT INTO tickets (user_id, title, category, description, priority, status) VALUES
(2, 'Laptop will not connect to campus Wi-Fi', 'Network', 'My laptop sees the network but fails to authenticate since this morning.', 'high', 'open'),
(4, 'Need Adobe Acrobat installed', 'Software', 'Please install Acrobat Pro on my office computer.', 'low', 'open'),
(5, 'Cannot log in to email', 'Account / Access', 'Password reset link is not arriving.', 'medium', 'closed');
