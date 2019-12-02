CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `email` tinytext NOT NULL,
  `first_name` text NOT NULL,
  `surname` text NOT NULL,
  `phone` text NOT NULL,
  `password` text NOT NULL,
  `rank` int(11) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`(64))
);

CREATE TABLE `course` (
  `id_course` varchar(5) NOT NULL,
  `course_name` varchar(30) NOT NULL,
  `course_description` varchar(500) NOT NULL,
  `course_type` varchar(5) NOT NULL,
  `course_price` int(11) NOT NULL,
  `id_guarantor` int(11) NOT NULL,
  `course_status` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(500) NOT NULL,
  PRIMARY KEY (`id_course`),
  KEY `id_guarantor` (`id_guarantor`),
  CONSTRAINT `course_ibfk_1` FOREIGN KEY (`id_guarantor`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `course_has_lecturer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` varchar(5) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_Course_has_lecturer` (`id_user`),
  KEY `FK_Lecturer_has_course` (`id_course`),
  CONSTRAINT `FK_Course_has_lecturer` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Lecturer_has_course` FOREIGN KEY (`id_course`) REFERENCES `course` (`id_course`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `course_has_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` varchar(5) NOT NULL,
  `id_user` int(11) NOT NULL,
  `student_status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_Course_has_student` (`id_user`),
  KEY `FK_Student_has_course` (`id_course`),
  CONSTRAINT `FK_Course_has_student` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Student_has_course` FOREIGN KEY (`id_course`) REFERENCES `course` (`id_course`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `room_address` (
  `id_room_address` int(11) NOT NULL AUTO_INCREMENT,
  `room_address` varchar(70) NOT NULL,
  PRIMARY KEY (`id_room_address`),
  KEY `room_address` (`room_address`)
);

CREATE TABLE `room` (
  `id_room` varchar(4) NOT NULL,
  `room_type` varchar(20) NOT NULL,
  `room_capacity` int(11) NOT NULL,
  `id_room_address` int(11) NOT NULL,
  PRIMARY KEY (`id_room`),
  KEY `FK_Room_has_address` (`id_room_address`),
  CONSTRAINT `FK_Room_has_address` FOREIGN KEY (`id_room_address`) REFERENCES `room_address` (`id_room_address`) ON UPDATE CASCADE
);


CREATE TABLE `room_equipment` (
  `id_room_equipment` int(11) NOT NULL AUTO_INCREMENT,
  `room_equipment` varchar(50) NOT NULL,
  `id_room` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`id_room_equipment`),
  KEY `id_room` (`id_room`),
  CONSTRAINT `room_equipment_ibfk_1` FOREIGN KEY (`id_room`) REFERENCES `room` (`id_room`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE `task` (
  `id_task` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(50) NOT NULL,
  `task_type` varchar(5) NOT NULL,
  `task_description` varchar(100) NOT NULL,
  `task_points` int(11) DEFAULT NULL,
  `task_date` date NOT NULL,
  `task_from` int(11) DEFAULT NULL,
  `task_to` int(11) NOT NULL,
  `id_room` varchar(4) DEFAULT NULL,
  `id_course` varchar(5) NOT NULL,
  PRIMARY KEY (`id_task`),
  KEY `id_course` (`id_course`),
  KEY `id_room` (`id_room`),
  CONSTRAINT `task_ibfk_2` FOREIGN KEY (`id_course`) REFERENCES `course` (`id_course`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_ibfk_3` FOREIGN KEY (`id_room`) REFERENCES `room` (`id_room`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `student_has_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_task` int(11) NOT NULL,
  `points` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_task` (`id_task`),
  CONSTRAINT `student_has_task_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_has_task_ibfk_4` FOREIGN KEY (`id_task`) REFERENCES `task` (`id_task`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `user` (`email`, `first_name`, `surname`, `phone`, `password`, `rank`, `active`)
VALUES ('admin@a.a', 'Admin', 'z adminova', '123', '$2y$12$jC7EHEdO2ZvoZAQZWMJ9nOfNUDyfLl9UD9O9kSWiz07bOubOiLJIW', '5', '1');
INSERT INTO `course` (`id_course`, `course_name`, `course_description`, `course_type`, `course_price`, `id_guarantor`, `course_status`, `tags`)
VALUES ('IIS', 'Informační systémy', 'Informační systémy', 'P', '0', '1', '1', 'php, html, css');
INSERT INTO `course` (`id_course`, `course_name`, `course_description`, `course_type`, `course_price`, `id_guarantor`, `course_status`, `tags`)
VALUES ('IOS', 'Operační systémy', 'Operační systémy', 'P', '100', '1', '0', 'os, c');
INSERT INTO `user` (`email`, `first_name`, `surname`, `phone`, `password`, `rank`, `active`)
VALUES ('student@a.a', 'Student', 'z studentova', '123', '$2y$12$jC7EHEdO2ZvoZAQZWMJ9nOfNUDyfLl9UD9O9kSWiz07bOubOiLJIW', '1', '1');
INSERT INTO `user` (`email`, `first_name`, `surname`, `phone`, `password`, `rank`, `active`)
VALUES ('garant@a.a', 'Garant', 'z garantova', '123', '$2y$12$jC7EHEdO2ZvoZAQZWMJ9nOfNUDyfLl9UD9O9kSWiz07bOubOiLJIW', '2', '1');
INSERT INTO `user` (`email`, `first_name`, `surname`, `phone`, `password`, `rank`, `active`)
VALUES ('lektor@a.a', 'Lektor', 'z lektorova', '123', '$2y$12$jC7EHEdO2ZvoZAQZWMJ9nOfNUDyfLl9UD9O9kSWiz07bOubOiLJIW', '3', '1');
INSERT INTO `user` (`email`, `first_name`, `surname`, `phone`, `password`, `rank`, `active`)
VALUES ('veduci@a.a', 'Veduci z veducova', 'z garantova', '123', '$2y$12$jC7EHEdO2ZvoZAQZWMJ9nOfNUDyfLl9UD9O9kSWiz07bOubOiLJIW', '4', '1');
INSERT INTO `course_has_lecturer` (`id_course`, `id_user`)
VALUES ('IIS', '2');
INSERT INTO `room_address` (`room_address`)
VALUES ('Božetěchova 2');
INSERT INTO `room` (`id_room`, `room_type`, `room_capacity`, `id_room_address`)
VALUES ('D105', 'Přednáškový sál', '300', '1');
INSERT INTO `task` (`task_name`, `task_type`, `task_description`, `task_points`, `task_date`, `task_from`, `task_to`, `id_room`, `id_course`)
VALUES ('Zkouška', 'ZK', 'Test', '10', '2019-12-03', '8', '11', 'D105', 'IIS');



