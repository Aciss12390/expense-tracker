-- Baza uzywa utf8mb4, aby poprawnie przechowywac polskie znaki i szerszy zestaw Unicode.
CREATE DATABASE IF NOT EXISTS expense_tracker
CHARACTER SET utf8mb4
COLLATE utf8mb4_polish_ci;

USE expense_tracker;

-- Glowna tabela aplikacji: kazdy rekord opisuje jeden wydatek z opcjonalna notatka.
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    expense_date DATE NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
