CREATE TABLE cas_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester VARCHAR(255) NOT NULL,
    district VARCHAR(255) NOT NULL,
    barangay VARCHAR(255) NOT NULL
);

CREATE TABLE cas_budget (
    id INT AUTO_INCREMENT PRIMARY KEY,
    details_id INT NOT NULL, -- Foreign key reference to cas_details
    event_title VARCHAR(255) NOT NULL,
    total_budget DECIMAL(10, 2) NOT NULL,
    expenses DECIMAL(10, 2) NOT NULL,
    remaining_budget DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (details_id) REFERENCES cas_details(id) ON DELETE CASCADE
);
