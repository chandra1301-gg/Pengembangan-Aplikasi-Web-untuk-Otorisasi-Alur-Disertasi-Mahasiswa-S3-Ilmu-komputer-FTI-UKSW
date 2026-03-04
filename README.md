# Sistem Otomasi Alur Disertasi S3 – FTI UKSW

Repository ini berisi source code aplikasi web **Sistem Otomasi Alur Disertasi S3**  
pada Program Studi Doktor Ilmu Komputer, Fakultas Teknologi Informasi, Universitas Kristen Satya Wacana (FTI UKSW).

Aplikasi ini dikembangkan sebagai bagian dari **Kerja Praktek** untuk membantu
mengotomasi proses administrasi disertasi mahasiswa S3, mulai dari pendaftaran ujian,
penjadwalan, penetapan penguji, penilaian, hingga pengelolaan revisi disertasi.

---

## ✨ Fitur Utama

### 1. Modul Mahasiswa
- Dashboard status ujian (Proposal, Kualifikasi, Kelayakan, Tertutup).
- Registrasi ujian disertasi (sesuai urutan tahapan).
- Melihat nilai dan status ujian (Menunggu, Terjadwal, Lulus, Revisi).
- Unggah dokumen revisi disertasi dan memantau status revisi.
- Unduh dokumen/form terkait ujian.
- Pesan internal dan pengelolaan profil.

### 2. Modul Admin Program Studi
- Verifikasi pendaftaran ujian (Menunggu, Diterima, Ditolak) dengan catatan admin.
- Penetapan jadwal ujian (tanggal, waktu, lokasi).
- Penetapan dan pengelolaan daftar penguji.
- Monitoring & evaluasi (rekap ujian, nilai, status kelulusan).
- Pengelolaan data mahasiswa, dosen/penguji, dan akun pengguna.
- Pengumuman, berita, dan informasi publik program studi.

### 3. Modul Penguji
- Dashboard ujian yang akan diuji.
- Daftar ujian per penguji lengkap dengan jadwal.
- Form penilaian ujian disertasi (aspek penilaian, skor, komentar).
- Review dokumen revisi disertasi (setujui atau minta revisi ulang).
- Melihat rekap nilai dan catatan penilaian.

### 4. Halaman Publik
- Beranda / landing page program S3 Ilmu Komputer FTI UKSW.
- Informasi bagan alir disertasi, kurikulum, jadwal pendaftaran.
- Informasi staf pengajar, fasilitas, berita, dan pengumuman.

---

## 🛠️ Teknologi

- **Backend** : PHP (prosedural)
- **Frontend** : HTML5, CSS3, JavaScript
- **Database** : MySQL / MariaDB
- **Web Server** : Apache (XAMPP / LAMP / WAMP)
- **Tools** : Visual Studio Code, phpMyAdmin

---

## 📦 Persyaratan

- PHP 7.4+  
- MySQL / MariaDB
- Web server (Apache via XAMPP/Laragon/WAMP)
- Composer (opsional, jika ingin menambah package)

---

![Tampilan Mahasiswa](tampilan/mahasiswa)
![Tamoilan Admin](tampilan/admin)
![Tampilan Penguji](tampilan/penguji)
