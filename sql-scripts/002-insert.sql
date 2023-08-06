USE iuhrms;

-- Insert data into users
INSERT INTO `users` (`first_name`, `last_name`, `email`, `is_admin`) VALUES
('John', 'Doe', 'john.doe@example.com', 0),
('Jane', 'Doe', 'jane.doe@example.com', 1);

-- Insert data into room_types
INSERT INTO `room_types` (`type`, `price`) VALUES
('Single', 1000),
('Double', 800),
('Triple', 600),
('Quadruple', 500);

-- Insert data into hostels
INSERT INTO `hostels` (`name`, `description`, `total_rooms`, `occupied_rooms`, `location`) VALUES
('Hostel A', 'Hostel A is a modern facility with a variety of room types. It offers a comfortable living environment with amenities such as a common lounge, study rooms, and a fully equipped kitchen.', 100, 50, 'North Campus'),
('Hostel B', 'Hostel B is a traditional-style residence hall with double and triple rooms. It features a large dining hall, shared bathrooms, and a vibrant community atmosphere.', 200, 100, 'South Campus'),
('Hostel C', 'Hostel C is an eco-friendly residence with a focus on sustainability. It offers single and double rooms with amenities such as solar-powered electricity, a communal garden, and recycling programs.', 150, 75, 'East Campus'),
('Hostel D', 'Hostel D is a quiet, study-focused residence with single rooms only. It features individual study cubicles, a library, and enforced quiet hours.', 120, 60, 'West Campus'),
('Hostel E', 'Hostel E is a lively, social-focused residence with double and triple rooms. It features a large common area, game rooms, and regular social events.', 180, 90, 'North Campus'),
('Hostel F', 'Hostel F is a luxury residence with single and double rooms. It offers high-end amenities such as en-suite bathrooms, a fitness center, and a gourmet dining hall.', 100, 50, 'South Campus');

-- Get user ids for John and Jane
SET @john_id = (SELECT id FROM users WHERE email = 'john.doe@example.com');
SET @jane_id = (SELECT id FROM users WHERE email = 'jane.doe@example.com');

-- Get hostel ids for Hostel A and B
SET @hostel_a_id = (SELECT id FROM hostels WHERE name = 'Hostel A');
SET @hostel_b_id = (SELECT id FROM hostels WHERE name = 'Hostel B');

-- Get room type ids for Single and Double
SET @single_room_id = (SELECT id FROM room_types WHERE type = 'Single');
SET @double_room_id = (SELECT id FROM room_types WHERE type = 'Double');

-- Insert data into reservations
-- Since Jane is an admin, she does not have any reservations
INSERT INTO `reservations` (`user_id`, `hostel_id`, `room_type_id`, `reservation_date`, `semester`, `status`) VALUES
(@john_id, @hostel_a_id, @single_room_id, '2023-08-01', 'Fall 2023', 'Confirmed');

-- Insert data into emails
-- As Jane is an admin, she receives a system status email instead of a reservation email
INSERT INTO `emails` (`user_id`, `subject`, `body`, `sent`) VALUES
(@john_id, 'Reservation Confirmation', 'Your reservation at Hostel A has been confirmed.', 1),
(@jane_id, 'System Status', 'System is operating normally.', 1);
