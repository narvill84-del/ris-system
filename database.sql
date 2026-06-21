-- Requisition and Issue Slip Database Schema

CREATE TABLE IF NOT EXISTS municipality (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS responsibility_center (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ris_slip (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ris_number VARCHAR(50) NOT NULL UNIQUE,
    ris_date DATE NOT NULL,
    sai_number VARCHAR(50),
    sai_date DATE,
    office VARCHAR(255) NOT NULL,
    responsibility_center_code VARCHAR(50),
    purpose TEXT,
    requested_by VARCHAR(255),
    requested_by_designation VARCHAR(255),
    requested_by_date DATE,
    approved_by VARCHAR(255),
    approved_by_designation VARCHAR(255),
    approved_date DATE,
    received_by VARCHAR(255),
    received_by_designation VARCHAR(255),
    received_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsibility_center_code) REFERENCES responsibility_center(code)
);

CREATE TABLE IF NOT EXISTS ris_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ris_id INT NOT NULL,
    stock_no VARCHAR(50),
    unit VARCHAR(50),
    description VARCHAR(500),
    requisition_quantity INT,
    issuance_quantity INT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ris_id) REFERENCES ris_slip(id) ON DELETE CASCADE
);

CREATE INDEX idx_ris_number ON ris_slip(ris_number);
CREATE INDEX idx_ris_date ON ris_slip(ris_date);
