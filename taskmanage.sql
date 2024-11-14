CREATE TABLE cba (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);

CREATE TABLE ccs (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);

CREATE TABLE coed (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);

CREATE TABLE coe (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);

CREATE TABLE cihm (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);

CREATE TABLE con (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_name VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  due_date DATE NOT NULL,
  letter_request VARCHAR(100) NOT NULL,
  act_plan VARCHAR(100) NOT NULL,
  termof_ref VARCHAR(100) NOT NULL,
  requi_form VARCHAR(100) NOT NULL,
  venue_reserve VARCHAR(100) NOT NULL,
  budget_plan VARCHAR(100) NOT NULL
);
