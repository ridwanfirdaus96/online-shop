-- Migration: Add Payment Accounts Feature
-- Created: 2025-12-26
-- Description: Menambahkan tabel payment_accounts dan kolom payment_method_id di tabel orders

USE shop_online;

-- Create payment_accounts table
CREATE TABLE IF NOT EXISTS payment_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('bank_transfer', 'e_wallet', 'cod') NOT NULL,
    name VARCHAR(100) NOT NULL COMMENT 'Nama rekening (misal: BCA, Mandiri, GoPay)',
    account_number VARCHAR(100) DEFAULT NULL COMMENT 'Nomor rekening atau nomor e-wallet',
    account_holder VARCHAR(100) DEFAULT NULL COMMENT 'Nama pemegang rekening',
    instructions TEXT DEFAULT NULL COMMENT 'Instruksi pembayaran untuk customer',
    is_active BOOLEAN DEFAULT 1 COMMENT '1 = aktif, 0 = nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
);

-- Add payment_method_id column to orders table
ALTER TABLE orders 
ADD COLUMN payment_method_id INT DEFAULT NULL AFTER address,
ADD CONSTRAINT fk_orders_payment_method 
    FOREIGN KEY (payment_method_id) 
    REFERENCES payment_accounts(id) 
    ON DELETE SET NULL;

-- Insert sample payment accounts
INSERT INTO payment_accounts (type, name, account_number, account_holder, instructions, is_active) VALUES
('bank_transfer', 'BCA', '1234567890', 'Toko Shop Online', 'Transfer ke rekening BCA a.n. Toko Shop Online\nNomor Rekening: 1234567890\nSetelah transfer, harap konfirmasi via WhatsApp dengan menyertakan bukti transfer.', 1),
('bank_transfer', 'Mandiri', '0987654321', 'Toko Shop Online', 'Transfer ke rekening Mandiri a.n. Toko Shop Online\nNomor Rekening: 0987654321\nSetelah transfer, harap konfirmasi via WhatsApp dengan menyertakan bukti transfer.', 1),
('e_wallet', 'GoPay', '081234567890', 'Toko Shop Online', 'Transfer via GoPay ke nomor: 081234567890\nSetelah transfer, harap konfirmasi via WhatsApp dengan menyertakan bukti transfer.', 1),
('e_wallet', 'OVO', '081234567890', 'Toko Shop Online', 'Transfer via OVO ke nomor: 081234567890\nSetelah transfer, harap konfirmasi via WhatsApp dengan menyertakan bukti transfer.', 1),
('cod', 'Cash on Delivery (COD)', NULL, NULL, 'Pembayaran dilakukan saat barang diterima.\nPastikan Anda menyiapkan uang pas untuk mempermudah transaksi.', 1);

-- Show result
SELECT 'Migration completed successfully!' AS status;
SELECT * FROM payment_accounts;
