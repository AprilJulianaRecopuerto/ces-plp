-- Table to store general requisition details
CREATE TABLE cas_requisition (
    requisition_id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    college_name VARCHAR(100) NOT NULL DEFAULT 'College of Arts and Science'
);

-- Table to store details of each item in the requisition
CREATE TABLE cas_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT,  -- Foreign key linking to cas_requisition
    item_name VARCHAR(100) NOT NULL,
    total_meals INT DEFAULT 0,
    total_usage INT DEFAULT 0,
    utilization_percentage DECIMAL(5, 0) GENERATED ALWAYS AS (
        CASE WHEN total_meals > 0 THEN (total_usage / total_meals) * 100 ELSE 0 END
    ) STORED,  -- Automatically calculate utilization percentage
    FOREIGN KEY (requisition_id) REFERENCES cas_requisition(requisition_id) ON DELETE CASCADE
);
