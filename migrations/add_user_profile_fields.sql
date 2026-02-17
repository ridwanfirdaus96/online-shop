-- Migration: Add phone and address columns to users table
-- Run this SQL in your database

USE shop_online;

-- Add phone column
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email;

-- Add address column  
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT AFTER phone;

-- Verify columns added
DESCRIBE users;
