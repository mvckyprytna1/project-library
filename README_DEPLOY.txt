VICKY PROJECT LIBRARY - DEPLOY CPANEL

1. Buat database MySQL di cPanel
   - Masuk cPanel > MySQL Database
   - Buat database, user database, dan password
   - Assign user ke database dengan ALL PRIVILEGES

2. Upload file
   - Upload semua isi folder vicky_project_library ke public_html atau subdomain folder
   - Pastikan struktur file tetap sama

3. Edit config/database.php
   - DB_NAME isi nama database cPanel
   - DB_USER isi user database
   - DB_PASS isi password database
   - DB_HOST biasanya localhost

4. Kalau app ditaruh di root domain/subdomain
   - config/app.php:
     define('APP_BASE_URL', '');

5. Kalau app ditaruh di subfolder, contoh domain.com/project-library
   - config/app.php:
     define('APP_BASE_URL', '/project-library');

6. Jalankan installer
   - Buka https://domainmu.com/install.php
   - Buat akun owner pertama
   - Installer akan mencoba membuat tabel otomatis dari database.sql

7. Kalau installer gagal membuat tabel
   - Buka phpMyAdmin
   - Pilih database
   - Import database.sql
   - Buka lagi install.php

8. Setelah owner dibuat
   - Login lewat /auth/login.php
   - Tambah kategori dan project
   - Public portfolio ada di /public.php

9. Setelah setup aman
   - Hapus file install.php dari hosting atau rename menjadi install-disabled.php
   - Jangan upload config/database.php ke repo publik
   - Pastikan folder uploads/projects punya permission 755
