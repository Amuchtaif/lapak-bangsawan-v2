# Panduan Migrasi & Sinkronisasi Database (Phinx)

Dokumen ini berisi panduan workflow untuk menyamakan struktur dan data database antar device (Windows <-> Linux) menggunakan **Phinx** dan **Phinx Migrations Generator**.

## Persiapan Awal
Pastikan dependensi telah terinstall via Composer:
```bash
composer install
```

---

## Skenario 1: Sinkronisasi Pertama Kali (Fresh Start)
**Kondisi:** Anda baru saja setup Phinx di Windows, dan sekarang ingin menyamakan database di Linux (yang mungkin memiliki struktur tabel lama).

**Masalah:** Jika langsung menjalankan `migrate`, akan muncul error *"Table already exists"* karena Phinx mencoba membuat ulang tabel yang sudah ada.

**Solusi:** Reset database target (Linux) agar bersih, lalu bangun ulang menggunakan schema terbaru dari Windows.

**Langkah-langkah di Device Target (Linux):**
1.  **Backup (Opsional):** Export database lama jika ada data penting yang tidak ada di Windows.
2.  **Drop Tables:** Masuk ke database management (PHPMyAdmin/DBeaver) dan **Hapus Semua Tabel** (Drop All) di database Linux Anda.
3.  **Jalankan Migrasi Struktur:**
    Perintah ini akan membuat semua tabel berdasarkan struktur terbaru dari Windows.
    ```bash
    vendor/bin/phinx migrate
    ```
4.  **Isi Data (Seeding):**
    Perintah ini akan mengisi data awal yang diambil dari database Windows.
    ```bash
    vendor/bin/phinx seed:run
    ```

*Sekarang database Linux Anda sudah 100% identik dengan Windows.*

---

## Skenario 2: Workflow Harian (Incremental Updates)
**Kondisi:** Kedua device sudah sinkron. Anda ingin melakukan perubahan kecil (misal: tambah kolom baru) di Windows dan mengirimnya ke Linux tanpa menghapus data.

### 1. Di Device Sumber (Windows)
Lakukan perubahan struktur database secara manual seperti biasa (via PHPMyAdmin/HeidiSQL). Setelah selesai:

a. **Generate File Diff Migration:**
   Perintah ini akan membandingkan DB Anda dengan schema terakhir, lalu otomatis membuat file migrasi baru yang hanya berisi perubahannya saja (misal: `ALTER TABLE`).
   ```bash
   vendor/bin/phinx-migrations generate --name DeskripsiPerubahanSingkat
   # Contoh: vendor/bin/phinx-migrations generate --name TambahKolomNoHp
   ```

b. **Update Data Seeder (Opsional):**
   Jika ada data baru yang ingin di-sync juga, update file seeder:
   ```bash
   php tools/generate_seeder.php
   ```

c. **Push ke Git:** Commit file migrasi baru yang muncul di `db/migrations/`.

### 2. Di Device Target (Linux)
Setelah melakukan `git pull`:

a. **Jalankan Migrasi Perubahan:**
   Phinx akan mendeteksi file baru dan hanya menjalankan perubahan tersebut. Data lama aman.
   ```bash
   vendor/bin/phinx migrate
   ```

b. **Update Data (Opsional):**
   Jika ingin menimpa data dengan yang terbaru:
   ```bash
   vendor/bin/phinx seed:run
   ```

---

## Daftar Command Penting

| Command | Fungsi |
| :--- | :--- |
| `vendor/bin/phinx migrate` | Menjalankan perubahan struktur (bangun tabel/alter). |
| `vendor/bin/phinx rollback` | Membatalkan 1 langkah perubahan terakhir (Undo). |
| `vendor/bin/phinx seed:run` | Mengisi tabel dengan data dari seeder. |
| `vendor/bin/phinx-migrations generate` | Membuat file migrasi otomatis dari perubahan DB lokal. |
| `php tools/generate_seeder.php` | (Custom Tool) Mengambil data DB lokal dan update file Seeder. |
