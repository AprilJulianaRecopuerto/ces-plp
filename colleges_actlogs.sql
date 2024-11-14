CREATE TABLE colleges_actlogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uname VARCHAR(255),
    button_name VARCHAR(255),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
