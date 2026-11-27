CREATE DATABASE IF NOT EXISTS warehouse_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE warehouse_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS transaksi_stok;
DROP TABLE IF EXISTS detail_permintaan;
DROP TABLE IF EXISTS permintaan;
DROP TABLE IF EXISTS stok;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS gudang;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE gudang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_gudang VARCHAR(120) NOT NULL,
    lokasi VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(40) NOT NULL UNIQUE,
    nama_barang VARCHAR(150) NOT NULL,
    kategori_id INT NULL,
    satuan VARCHAR(30) NOT NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_barang_kategori
        FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_id INT NOT NULL,
    gudang_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_stok_barang_gudang (barang_id, gudang_id),
    CONSTRAINT fk_stok_barang
        FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE,
    CONSTRAINT fk_stok_gudang
        FOREIGN KEY (gudang_id) REFERENCES gudang(id) ON DELETE CASCADE,
    CONSTRAINT chk_stok_nonneg CHECK (jumlah >= 0)
) ENGINE = InnoDB;

CREATE TABLE permintaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    catatan TEXT NULL,
    diproses_oleh INT NULL,
    diproses_pada DATETIME NULL,
    CONSTRAINT fk_permintaan_user
        FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_permintaan_admin
        FOREIGN KEY (diproses_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE detail_permintaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permintaan_id INT NOT NULL,
    barang_id INT NOT NULL,
    gudang_id INT NOT NULL,
    jumlah INT NOT NULL,
    CONSTRAINT fk_detail_permintaan
        FOREIGN KEY (permintaan_id) REFERENCES permintaan(id) ON DELETE CASCADE,
    CONSTRAINT fk_detail_barang
        FOREIGN KEY (barang_id) REFERENCES barang(id),
    CONSTRAINT fk_detail_gudang
        FOREIGN KEY (gudang_id) REFERENCES gudang(id),
    CONSTRAINT chk_detail_jumlah CHECK (jumlah > 0)
) ENGINE = InnoDB;

CREATE TABLE transaksi_stok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_id INT NOT NULL,
    gudang_id INT NOT NULL,
    user_id INT NULL,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(255) NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_barang
        FOREIGN KEY (barang_id) REFERENCES barang(id),
    CONSTRAINT fk_transaksi_gudang
        FOREIGN KEY (gudang_id) REFERENCES gudang(id),
    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_transaksi_jumlah CHECK (jumlah > 0)
) ENGINE = InnoDB;

INSERT INTO kategori (nama_kategori)
VALUES
    ('Elektronik'),
    ('Peralatan Kantor'),
    ('Makanan'),
    ('Minuman'),
    ('Kebersihan');

INSERT INTO gudang (nama_gudang, lokasi)
VALUES
    ('Gudang Utama', 'Surabaya'),
    ('Gudang Cabang', 'Sidoarjo');

INSERT INTO barang (kode_barang, nama_barang, kategori_id, satuan, deskripsi)
VALUES
    ('BRG001', 'Laptop', 1, 'Unit', 'Laptop untuk kebutuhan operasional'),
    ('BRG002', 'Mouse', 1, 'Unit', 'Mouse komputer'),
    ('BRG003', 'Kertas A4', 2, 'Rim', 'Kertas printer ukuran A4'),
    ('BRG004', 'Air Mineral', 4, 'Dus', 'Air mineral kemasan'),
    ('BRG005', 'Sapu', 5, 'Unit', 'Sapu untuk kebersihan gudang');

INSERT INTO stok (barang_id, gudang_id, jumlah)
VALUES
    (1, 1, 20),
    (2, 1, 50),
    (3, 1, 100),
    (4, 1, 80),
    (5, 1, 30),
    (1, 2, 10),
    (2, 2, 25),
    (3, 2, 40),
    (4, 2, 35),
    (5, 2, 15);
