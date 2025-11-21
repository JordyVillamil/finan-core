-- Init script for PostgreSQL

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Set timezone
SET timezone = 'America/Bogota';

-- Create testing database
CREATE DATABASE proyecto_contable_test;
GRANT ALL PRIVILEGES ON DATABASE proyecto_contable_test TO proyecto_user;

-- Log
DO $$
BEGIN
    RAISE NOTICE 'Database initialization completed successfully';
    RAISE NOTICE 'Main database: proyecto_contable';
    RAISE NOTICE 'Test database: proyecto_contable_test';
END $$;