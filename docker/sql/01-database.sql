SET NAMES utf8mb4;

CREATE TABLE `users`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `first_name`      VARCHAR(50) NOT NULL,
    `last_name`       VARCHAR(50) NOT NULL,
    `photo`           VARCHAR(255) NULL COMMENT 'Cesta k profilovej fotografii používateľa',
    `role`            ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
    `phone`           VARCHAR(20) NULL,
    `email`           VARCHAR(100) NOT NULL,
    `password`        VARCHAR(255) NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum registrácie používateľa',
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum poslednej aktualizácie používateľa',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_email` (`email`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka používateľov';

CREATE TABLE `clinics`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100) NOT NULL,
    `street`          VARCHAR(255) NOT NULL,
    `city`            VARCHAR(100) NOT NULL,
    `postal_code`     VARCHAR(20) NOT NULL,
    `country`         VARCHAR(100) NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum registrácie kliniky',
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum poslednej aktualizácie kliniky',
    PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka kliník';

CREATE TABLE `doctors`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `user_id`         INT NOT NULL,
    `clinic_id`       INT NOT NULL COMMENT 'ID kliniky, kde doktor pracuje',
    `bio`             TEXT NULL COMMENT 'Krátky popis doktora',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum registrácie doktora',
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum poslednej aktualizácie doktora',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_doctors_user` (`user_id`),
    FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka doktorov';

CREATE TABLE `specializations`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(100) NOT NULL,
    `description`     TEXT NULL COMMENT 'Popis špecializácie',
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Indikuje, či je špecializácia aktívna, 1 = aktívna, 0 = neaktívna',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum registrácie špecializácie',
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Dátum poslednej aktualizácie špecializácie',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_specializations_name` (`name`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka špecializácií doktorov';

CREATE TABLE `doctor_specializations`
(
    `id`                    INT NOT NULL AUTO_INCREMENT,
    `doctor_id`             INT NOT NULL,
    `specialization_id`     INT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_doctor_specializations` (`doctor_id`, `specialization_id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka prepojenia doktorov a ich špecializácií';

CREATE TABLE `working_hours`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `doctor_id`       INT NOT NULL,
    `day_of_week`     ENUM('Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota', 'Nedeľa') NOT NULL,
    `start_time`      TIME NOT NULL,
    `end_time`        TIME NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    CHECK (`start_time` < `end_time`)  -- Overenie, že čas začiatku je pred časom konca
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka pracovných hodín doktorov';

CREATE TABLE `doctor_absences`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `doctor_id`       INT NOT NULL,
    `date_from`       DATE NOT NULL,
    `date_to`         DATE NOT NULL,
    `reason`          VARCHAR(255) NULL COMMENT 'Dôvod neprítomnosti doktora',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    CHECK (`date_from` <= `date_to`)  -- Overenie, že dátum začiatku je pred alebo rovný dátumu konca
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka neprítomností doktorov';

CREATE TABLE `appointments`
(
    `id`              INT NOT NULL AUTO_INCREMENT,
    `doctor_id`       INT NOT NULL,
    `patient_id`      INT NOT NULL,
    `slot_taken`      TINYINT(1) NULL DEFAULT 1 COMMENT 'Indikuje, či je slot obsadený, 1 = obsadený, NULL = termín zrušený (čas je voľný)',
    `appointment_date` DATE NOT NULL,
    `start_time`      TIME NOT NULL,
    `end_time`        TIME NOT NULL,
    `status`          ENUM('scheduled', 'completed', 'cancelled_patient', 'cancelled_doctor', 'patient_didnt_come') NOT NULL DEFAULT 'scheduled',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_appointments_slot` (`doctor_id`, `appointment_date`, `start_time`, `slot_taken`),
    CHECK (`start_time` < `end_time`),  -- Overenie, že čas začiatku je pred časom konca
    CHECK (
        (`status` IN ('cancelled_patient', 'cancelled_doctor') AND `slot_taken` IS NULL)
            OR (`status` NOT IN ('cancelled_patient', 'cancelled_doctor') AND `slot_taken` IS NOT NULL AND `slot_taken` = 1)
        ),  -- Zrušený termín má slot_taken = NULL, platný 1
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`),
    FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka termínov schôdzok medzi pacientmi a doktormi';

CREATE TABLE `reviews`
(
    `id` INT NOT NULL AUTO_INCREMENT,
    `appointment_id` INT NOT NULL,
    `rating` SMALLINT NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dátum vytvorenia recenzie',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unq_reviews_appointment` (`appointment_id`),
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
    CHECK (`rating` >= 1 AND `rating` <= 5)  -- Overenie, že hodnotenie je medzi 1 a 5
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabuľka recenzií od pacientov na doktorov';