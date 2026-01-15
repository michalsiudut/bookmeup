CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Note: references table 'bussiness' which must exist.
CREATE TABLE services (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL, -- references bussiness(id)
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    duration_minutes INTEGER NOT NULL,
    description TEXT
);

INSERT INTO users (firstname, lastname, email, password, bio, enabled)
VALUES (
    'Jan',
    'Kowalski',
    'jan.kowalski@example.com',
    '$2b$10$ZbzQrqD1vDhLJpYe/vzSbeDJHTUnVPCpwlXclkiFa8dO5gOAfg8tq',
    'Lubi programować w JS i PL/SQL.',
    TRUE
);
 