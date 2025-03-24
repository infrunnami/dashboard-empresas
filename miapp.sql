CREATE DATABASE miapp;

USE miapp;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
);

INSERT INTO users (username, password, role) VALUES
('admin', MD5('1234'), 'admin'),
('user1', MD5('1234'), 'user1'),
('user2', MD5('1234'), 'user2');
