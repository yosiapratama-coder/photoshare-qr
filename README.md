# PhotoShare QR — PHP + MySQL

## Fitur
- Galeri acara dengan tautan dan QR Code.
- Unggah foto ke galeri melalui browser dari HP/komputer.
- Mendukung foto yang dipindahkan dari Canon, Nikon, Sony, Fujifilm, atau perangkat lain.
- Galeri publik untuk pemegang tautan/QR; unduh foto.
- SQL untuk database MySQL.
- Validasi tipe file, batas ukuran, dan nama file acak.

## Persyaratan
- PHP 8.1+ dengan ekstensi `pdo_mysql` dan `fileinfo`
- MySQL/MariaDB
- XAMPP atau Laragon
- Internet untuk memuat pustaka QR Code dari CDN.

## Menjalankan di Windows dengan XAMPP
1. Ekstrak folder `photoshare_qr` ke `C:\\xampp\\htdocs\\photoshare_qr`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka `http://localhost/phpmyadmin`.
4. Import `database.sql`.
5. Buka `config.php` dan sesuaikan username/password MySQL bila perlu. Default XAMPP biasanya user `root`, password kosong.
6. Buka `http://localhost/photoshare_qr/`.
7. Buat acara, lalu bagikan QR/tautan acara.

## Akses dari HP pada Wi-Fi yang sama
- Cari IP lokal komputer (misalnya `ipconfig` di Command Prompt).
- Gunakan `http://IP-KOMPUTER/photoshare_qr/`.
- Pastikan firewall mengizinkan Apache. QR yang dibagikan harus memakai alamat yang bisa dijangkau HP, bukan `localhost`.

## Publikasi internet
Untuk dipakai peserta di luar Wi-Fi lokal, deploy ke hosting PHP + MySQL dengan HTTPS, lalu atur `BASE_URL` di `config.php` ke URL hosting. Jangan gunakan server development PHP untuk hosting publik.

## Catatan transfer kamera
Website tidak bisa mengontrol atau menarik file langsung dari setiap kamera hanya karena QR dipindai. Untuk unggah dari kamera:
- Gunakan Wi-Fi/app resmi kamera bila didukung untuk menyalin foto ke HP, lalu unggah ke galeri.
- Atau salin kartu SD ke komputer, lalu unggah lewat browser.
- Integrasi transfer langsung perlu implementasi khusus sesuai model kamera dan SDK/protokol yang tersedia.

## Privasi dan keamanan
- Unggah hanya foto yang memang berhak dibagikan.
- Siapa pun yang memiliki tautan galeri dapat melihat dan mengunduh foto. Jangan unggah foto privat.
- Untuk produksi, tambahkan login admin, kode akses galeri, batas unggah, pemindaian malware, kebijakan retensi, dan backup.
- Atur batas upload PHP (`upload_max_filesize`, `post_max_size`) sesuai kebutuhan.
