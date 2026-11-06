-- Create database
CREATE DATABASE IF NOT EXISTS secure_bank;
USE secure_bank;

-- Admin table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Employee table
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    security_key VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Customer table (updated with Hill Cipher support)
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    cipher_type ENUM('vigenere', 'columnar', 'railfence', 'hill') DEFAULT 'vigenere',
    cipher_key VARCHAR(50) NOT NULL,
    private_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    from_account VARCHAR(20),
    to_account VARCHAR(20),
    amount DECIMAL(15,2) NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer', 'credit', 'debit') NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed', 'failed', 'reversed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Encryption logs table
CREATE TABLE encryption_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    operation ENUM('encrypt', 'decrypt') NOT NULL,
    cipher_type VARCHAR(20) NOT NULL,
    key_used VARCHAR(50),
    status ENUM('success', 'failed') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample employees
INSERT INTO employees (employee_id, full_name, email, password_hash, security_key) VALUES
('EMP001', 'Sarah Johnson', 'sarah@securebank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMP_SECURE_001'),
('EMP002', 'Mike Anderson', 'mike@securebank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMP_SECURE_002');

-- Insert sample customers with different cipher types (including Hill)
INSERT INTO customers (customer_id, full_name, email, phone, password_hash, account_number, balance, cipher_type, cipher_key, private_note, status) VALUES
('CUST001', 'John Doe', 'john@email.com', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACC1001', 5000.00, 'vigenere', 'SECUREKEY', 'Welcome to secure banking', 'active'),
('CUST002', 'Jane Smith', 'jane@email.com', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACC1002', 7500.00, 'columnar', 'COLUMNKEY', 'Important client', 'active'),
('CUST003', 'Robert Wilson', 'robert@email.com', '555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACC1003', 3200.00, 'railfence', 'RAILKEY', 'Premium account', 'active'),
('CUST004', 'Alice Brown', 'alice@email.com', '555-0104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ACC1004', 6200.00, 'hill', 'HILLKEY', 'High value client', 'active');

-- Sample transactions
INSERT INTO transactions (transaction_id, customer_id, from_account, to_account, amount, transaction_type, description, status) VALUES
('TXN001', 1, 'ACC1001', 'ACC1002', 500.00, 'transfer', 'Transfer to Jane', 'completed'),
('TXN002', 2, 'ACC1002', 'ACC1003', 300.00, 'transfer', 'Transfer to Robert', 'completed'),
('TXN003', 3, 'ACC1003', 'ACC1001', 200.00, 'transfer', 'Transfer to John', 'completed'),
('TXN004', 4, 'ACC1004', 'ACC1001', 1000.00, 'transfer', 'Transfer to John', 'completed');