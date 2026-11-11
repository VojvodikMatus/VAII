-- skúšobné dáta pre testovanie aplikácie vygenerované pomocou AI(claude) a upravené pre potreby aplikácie
SET NAMES utf8mb4;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`)
VALUES (1, 'Admin', 'Systému', 'admin@meditermin.sk', NULL,
        '$2y$10$LlwR5LXY9DqooRQqsSNM/OTyAbXS9CGJxxkPdfykBTanTCHtBU8dC', 'admin'),
       (2, 'Jana', 'Nováková', 'novakova@meditermin.sk', '+421 900 111 222',
        '$2y$10$LlwR5LXY9DqooRQqsSNM/OTyAbXS9CGJxxkPdfykBTanTCHtBU8dC', 'doctor'),
       (3, 'Peter', 'Kováč', 'kovac@meditermin.sk', '+421 900 333 444',
        '$2y$10$LlwR5LXY9DqooRQqsSNM/OTyAbXS9CGJxxkPdfykBTanTCHtBU8dC', 'doctor'),
       (4, 'Lucia', 'Horváthová', 'horvathova@meditermin.sk', '+421 900 555 666',
        '$2y$10$LlwR5LXY9DqooRQqsSNM/OTyAbXS9CGJxxkPdfykBTanTCHtBU8dC', 'doctor'),
       (5, 'Ján', 'Pacient', 'pacient@meditermin.sk', '+421 900 777 888',
        '$2y$10$LlwR5LXY9DqooRQqsSNM/OTyAbXS9CGJxxkPdfykBTanTCHtBU8dC', 'patient');

INSERT INTO `clinics` (`id`, `name`, `street`, `city`, `postal_code`, `country`)
VALUES (1, 'Poliklinika Centrum', 'Hlavná 12', 'Žilina', '010 01', 'Slovensko'),
       (2, 'Kardiocentrum Sever', 'Národná 5', 'Žilina', '010 01', 'Slovensko'),
       (3, 'Ambulancia Zdravá koža', 'Mostná 8', 'Žilina', '010 01', 'Slovensko');

INSERT INTO `doctors` (`id`, `user_id`, `clinic_id`, `bio`, `appointment_length`)
VALUES (1, 2, 1, 'Všeobecná lekárka pre dospelých s 15-ročnou praxou.', 20),
       (2, 3, 2, 'Kardiológ a internista, venuje sa najmä vysokému tlaku a arytmiám.', 30),
       (3, 4, 3, NULL, 15);

INSERT INTO `specializations` (`id`, `name`, `description`)
VALUES (1, 'Praktický lekár', 'Všeobecná starostlivosť o dospelých pacientov, preventívne prehliadky.'),
       (2, 'Kardiológ', 'Choroby srdca a ciev, EKG, záťažové testy.'),
       (3, 'Internista', 'Choroby vnútorných orgánov.'),
       (4, 'Dermatovenerológ', 'Choroby kože, vlasov a nechtov.'),
       (5, 'Zubný lekár', 'Prevencia a liečba ochorení zubov.');

INSERT INTO `doctor_specializations` (`doctor_id`, `specialization_id`)
VALUES (1, 1),
       (2, 2),
       (2, 3),
       (3, 4);

INSERT INTO `working_hours` (`doctor_id`, `day_of_week`, `start_time`, `end_time`)
VALUES (1, 'Pondelok', '07:30', '12:00'),
       (1, 'Streda', '07:30', '15:00'),
       (1, 'Piatok', '07:30', '12:00'),
       (2, 'Utorok', '08:00', '14:00'),
       (2, 'Štvrtok', '08:00', '16:00'),
       (3, 'Pondelok', '12:00', '18:00'),
       (3, 'Streda', '08:00', '12:00');

INSERT INTO `doctor_absences` (`doctor_id`, `date_from`, `date_to`, `reason`)
VALUES (3, '2026-12-21', '2027-01-03', 'Vianočná dovolenka');

-- Zrušený termín má slot_taken = NULL, takže rovnaký čas môže byť rezervovaný znova.
INSERT INTO `appointments` (`id`, `doctor_id`, `patient_id`, `slot_taken`, `appointment_date`, `start_time`, `end_time`, `status`)
VALUES (1, 1, 5, 1, '2026-10-12', '07:40', '08:00', 'completed'),
       (2, 1, 5, NULL, '2026-11-09', '08:00', '08:20', 'cancelled_patient'),
       (3, 1, 5, 1, '2026-11-09', '08:00', '08:20', 'scheduled'),
       (4, 2, 5, 1, '2026-11-10', '09:00', '09:30', 'scheduled');

INSERT INTO `reviews` (`appointment_id`, `rating`, `comment`)
VALUES (1, 5, 'Príjemná lekárka, všetko vysvetlila.');