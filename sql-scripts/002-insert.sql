USE iuhrms;

-- Insert data into users
INSERT INTO `users` (`first_name`, `last_name`, `email`, `is_admin`) VALUES
('John', 'Doe', 'john.doe@iu.org', 0),
('Jane', 'Doe', 'jane.doe@iu.org', 1);

-- Insert data into semesters
INSERT INTO `semesters` (`name`, `semester_start`, `semester_end`) VALUES
                                                                       ('Fall - September 2023', '2023-09-01', '2023-12-31'),
                                                                       ('Spring - January 2024', '2024-01-01', '2024-05-31'),
                                                                       ('Summer - May 2024', '2024-05-01', '2024-08-31');

-- Insert data into room_types
INSERT INTO `room_types` (`type`, `price`) VALUES
('Single', 800),
('Double', 1000),
('Triple', 1400),
('Quadruple', 1800);

-- Insert data into hostels
INSERT INTO `hostels` (`name`, `description`, `total_rooms`, `occupied_rooms`, `location`) VALUES
('Hostel A', 'Hostel A is a modern facility with a variety of room types. It offers a comfortable living environment with amenities such as a common lounge, study rooms, and a fully equipped kitchen.', 100, 50, 'North Campus'),
('Hostel B', 'Hostel B is a traditional-style residence hall with double and triple rooms. It features a large dining hall, shared bathrooms, and a vibrant community atmosphere.', 200, 100, 'South Campus'),
('Hostel C', 'Hostel C is an eco-friendly residence with a focus on sustainability. It offers single and double rooms with amenities such as solar-powered electricity, a communal garden, and recycling programs.', 150, 75, 'East Campus'),
('Hostel D', 'Hostel D is a quiet, study-focused residence with single rooms only. It features individual study cubicles, a library, and enforced quiet hours.', 120, 60, 'West Campus');


-- Insert data into reservation_statuses
INSERT INTO `reservation_statuses` (`name`) VALUES
                                                  ('Pending'),
                                                  ('Confirmed'),
                                                  ('Rejected'),
                                                  ('Cancelled');

-- Declare variables
-- Get user ids for John and Jane
SET @john_id = (SELECT id FROM users WHERE email = 'john.doe@iu.org');
SET @jane_id = (SELECT id FROM users WHERE email = 'jane.doe@iu.org');

-- Get hostel ids for Hostel A and B
SET @hostel_a_id = (SELECT id FROM hostels WHERE name = 'Hostel A');
SET @hostel_b_id = (SELECT id FROM hostels WHERE name = 'Hostel B');
SET @hostel_c_id = (SELECT id FROM hostels WHERE name = 'Hostel C');
SET @hostel_d_id = (SELECT id FROM hostels WHERE name = 'Hostel D');


-- Get room type ids for Single and Double
SET @single_room_id = (SELECT id FROM room_types WHERE type = 'Single');
SET @double_room_id = (SELECT id FROM room_types WHERE type = 'Double');
SET @triple_room_id = (SELECT id FROM room_types WHERE type = 'Triple');
SET @quadruple_room_id = (SELECT id FROM room_types WHERE type = 'Quadruple');


-- Get semester ids
SET @fall_semester_id = (SELECT id FROM semesters WHERE name = 'Fall - September 2023');

-- Get status ids for Confirmed, Pending, and Cancelled
SET @confirmed_status_id = (SELECT id FROM reservation_statuses WHERE name = 'Confirmed');
SET @pending_status_id = (SELECT id FROM reservation_statuses WHERE name = 'Pending');
SET @cancelled_status_id = (SELECT id FROM reservation_statuses WHERE name = 'Cancelled');
SET @rejected_status_id = (SELECT id FROM reservation_statuses WHERE name = 'Rejected');

-- Insert data into reservations
-- Since Jane is an admin, she does not have any reservations
INSERT INTO `reservations` (`user_id`, `hostel_id`, `room_type_id`, `reservation_date`, `semester_id`, `status_id`) VALUES
    (@john_id, @hostel_a_id, @single_room_id, '2023-08-01', @fall_semester_id, @pending_status_id);


-- Insert data into emails
-- As Jane is an admin, she receives a system status email instead of a reservation email
INSERT INTO `emails` (`user_id`, `subject`, `body`, `sent`) VALUES
(@john_id, 'Reservation Confirmation', 'Your reservation at Hostel A has been confirmed.', 1),
(@jane_id, 'System Status', 'System is operating normally.', 1);

-- Insert data into hostel_room_types linking table
INSERT INTO `hostel_room_types` (`hostel_id`, `room_type_id`) VALUES
                                                                  (@hostel_a_id, @single_room_id),
                                                                  (@hostel_a_id, @double_room_id),
                                                                  (@hostel_b_id, @double_room_id),
                                                                  (@hostel_b_id, @triple_room_id),
                                                                  (@hostel_c_id, @single_room_id),
                                                                  (@hostel_c_id, @double_room_id),
                                                                  (@hostel_d_id, @single_room_id),
                                                                  (@hostel_d_id, @double_room_id),
                                                                  (@hostel_d_id, @triple_room_id),
                                                                  (@hostel_d_id, @quadruple_room_id);
