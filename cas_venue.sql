-- CBA Reservation Table
CREATE TABLE cba_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Business Administration',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cba_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES cba_reservation(id) ON DELETE CASCADE
);

CREATE TABLE cba_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES cba_reservation(id) ON DELETE CASCADE
);

-- CCS Reservation Table
CREATE TABLE ccs_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Computer Studies',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ccs_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES ccs_reservation(id) ON DELETE CASCADE
);

CREATE TABLE ccs_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES ccs_reservation(id) ON DELETE CASCADE
);

-- COED Reservation Table
CREATE TABLE coed_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Education',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coed_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES coed_reservation(id) ON DELETE CASCADE
);

CREATE TABLE coed_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES coed_reservation(id) ON DELETE CASCADE
);

-- COE Reservation Table
CREATE TABLE coe_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Engineering',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coe_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES coe_reservation(id) ON DELETE CASCADE
);

CREATE TABLE coe_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES coe_reservation(id) ON DELETE CASCADE
);

-- CIHM Reservation Table
CREATE TABLE cihm_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of International Hospitality Management',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cihm_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES cihm_reservation(id) ON DELETE CASCADE
);

CREATE TABLE cihm_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES cihm_reservation(id) ON DELETE CASCADE
);

-- CON Reservation Table
CREATE TABLE con_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_request DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Nursing',
    event_activity VARCHAR(255),
    event_date DATE,
    time_of_event VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE con_venue_request (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    venue_name VARCHAR(255) NOT NULL,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES con_reservation(id) ON DELETE CASCADE
);

CREATE TABLE con_addedrequest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    additional_request VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) DEFAULT 100,
    FOREIGN KEY (reservation_id) REFERENCES con_reservation(id) ON DELETE CASCADE
);

