# README_DEVELOPMENT.md

## Baleta

Baleta adalah aplikasi web PWA untuk membantu owner atau bos kapal dan admin mencatat pemasukan, pengeluaran harian kapal, rekap tutup bulan, dan catatan non-operasional nelayan.

Dokumen ini menjadi acuan pengembangan terbaru setelah revisi Baleta V6. Semua perubahan fitur harus tetap mengikuti alur bisnis di bawah agar aplikasi tidak berubah arah.

---

## 1. Stack

```text
Framework      : Laravel 13
Auth           : Laravel Fortify
View           : Blade
CSS            : Tailwind CSS
Interaksi UI   : JavaScript vanilla
Database       : MySQL atau PostgreSQL
PWA            : Installable app
UI             : Mobile-first
```

---

## 2. Role

```text
Owner / Pemilik Kapal
Admin
Kapten sebagai data kapal, bukan user login utama
```

Owner dapat membuat admin. Admin membantu input data. Kapten diinput langsung saat membuat atau mengedit kapal.

---

## 3. Alur Inti

```text
Kapal dibuat + nama kapten diinput langsung
        ↓
Invoice harian dibuat per kapal
        ↓
Invoice diposting
        ↓
Tutup bulan mengambil semua invoice posted yang belum ditutup
        ↓
Rekap per kapal dibuat
        ↓
Biaya operasional kapal dikurangi
        ↓
Jasa kapten dihitung per kapal
        ↓
Hasil pemilik dari kapal menjadi rekap tutup bulan
```

Non-operasional berdiri sendiri. Tidak masuk tutup bulan kapal.

---

## 4. Aturan Tutup Bulan Terbaru

Tutup bulan tidak mengikuti bulan kalender.

Baleta memakai urutan:

```text
Tutup Bulan 1
Tutup Bulan 2
Tutup Bulan 3
```

Saat tutup bulan dibuat, sistem mengambil semua invoice `posted` yang belum pernah masuk tutup bulan.

Tanggal tetap disimpan sebagai data:

```text
period_started_at = tanggal invoice paling awal dalam rekap
period_ended_at   = tanggal invoice paling akhir dalam rekap
closed_at          = waktu tutup bulan dibuat
```

Kolom lama `month` dan `year` tetap ada untuk kompatibilitas, tetapi bukan dasar bisnis utama.

---

## 5. Update dan Hapus Tutup Bulan

Tutup bulan dapat diedit.

Yang boleh diedit:

```text
Biaya operasional per kapal
Persentase jasa kapten per kapal
Catatan tutup bulan
```

Tutup bulan dapat dihapus.

Saat dihapus:

```text
Invoice yang sebelumnya closed dikembalikan menjadi posted.
Data rekap kapal dalam tutup bulan tersebut dihapus.
Tutup bulan dapat dibuat ulang.
```

---

## 6. Kapten

Alur utama kapten sekarang berada di form kapal.

Saat membuat kapal, input:

```text
Nama kapal
Nama kapten
Nomor HP kapten opsional
Tanggal mulai kapten
```

Baleta otomatis membuat atau memakai data kapten yang sudah ada.

Menu kapten lama dapat tetap ada sebagai data pendukung, tetapi bukan alur utama input kapal.

---

## 7. Screenshot Laporan

Export Excel atau PDF tidak menjadi alur utama.

Baleta memakai tampilan screenshot:

```text
Invoice screenshot
Tutup bulan screenshot
Non-operasional screenshot
```

User dapat membuka tampilan screenshot lalu menyimpan gambar melalui fitur screenshot HP atau simpan halaman dari browser.

---

## 8. UI Mobile

UI Baleta harus mobile-first.

Prinsip:

```text
Input besar
Tombol besar
Card layout
Bottom navigation
Menu Pengaturan pada header mobile
Tidak memakai scroll horizontal untuk menu header
Bahasa sederhana untuk nelayan
```

---

## 9. Bahasa

Gunakan bahasa Indonesia yang mudah dipahami.

Contoh istilah:

```text
Dashboard       → Beranda
Filter          → Saring
Cancel          → Batal
Password        → Kata sandi
Print/Cetak     → Screenshot
Owner Final     → Hasil Pemilik dari Kapal
```

Kode internal boleh tetap memakai bahasa Inggris sesuai standar Laravel.

---

## 10. Prinsip Keamanan dan Data

```text
Semua data bisnis wajib memakai owner_id.
Admin hanya boleh melihat data owner tempat dia bekerja.
Invoice closed tidak boleh diedit langsung.
Tutup bulan yang dihapus harus membuka invoice kembali ke posted.
Form penting tetap memakai anti double submit.
Uang tetap disimpan sebagai integer.
```

---

## 11. Non-Operasional

Non-operasional adalah pencatatan terpisah.

Aturan:

```text
Tidak masuk tutup bulan kapal.
Tidak mempengaruhi jasa kapten.
Tidak mengurangi hasil kapal.
Memiliki rekap sendiri.
Memiliki tampilan screenshot sendiri.
```

---

## 12. Migration Terbaru

```text
2026_07_06_000017_update_monthly_closing_to_sequence_period.php
```

Fungsi:

```text
Menambah closing_period_number.
Menambah period_label.
Menambah period_started_at.
Menambah period_ended_at.
Menambah closed_at.
Menghapus unique calendar month/year.
Menambah unique owner + closing_period_number.
```
