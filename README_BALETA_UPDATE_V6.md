# README_BALETA_UPDATE_V6.md

## Ringkasan Update

Patch ini memperbarui aplikasi menjadi brand **Baleta** dan menyesuaikan alur bisnis terbaru dari nelayan.

Fokus update:

1. Rekap bulanan dapat diedit dan dihapus.
2. Tutup bulan tidak lagi mengikuti bulan kalender.
3. Menu mobile tidak lagi memakai scroll horizontal.
4. Bahasa tampilan dibuat lebih mudah dipahami.
5. Kapten diinput langsung saat membuat atau mengedit kapal.
6. Export laporan diganti menjadi tampilan screenshot.

---

## 1. Alur Tutup Bulan Terbaru

Tutup bulan di Baleta tidak memakai bulan kalender sebagai dasar utama.

Alur baru:

```text
Invoice harian diposting
        ↓
Semua invoice posted yang belum ditutup masuk ke tutup bulan berikutnya
        ↓
Baleta memberi nomor urut: Tutup Bulan 1, Tutup Bulan 2, Tutup Bulan 3
        ↓
Tanggal invoice paling awal dan paling akhir tetap disimpan sebagai rentang data
        ↓
Waktu pembuatan tutup bulan tetap disimpan
```

Contoh:

```text
Jika admin membuat tutup bulan pertama kali,
maka sistem membuat Tutup Bulan 1.

Jika ada tanggal pada bulan kalender berikutnya,
tetap bisa masuk Tutup Bulan 1 selama invoice itu belum pernah ditutup.
```

Kolom `month` dan `year` lama tetap disimpan untuk kompatibilitas database, tetapi bukan lagi dasar aturan bisnis tutup bulan.

---

## 2. Update dan Hapus Rekap Bulanan

Rekap bulanan sekarang mendukung:

```text
Edit
Hapus
```

Edit digunakan untuk memperbaiki:

1. Biaya operasional kapal.
2. Persentase jasa kapten.
3. Catatan tutup bulan.

Hapus digunakan untuk membatalkan rekap bulanan.

Saat tutup bulan dihapus:

```text
Invoice yang sebelumnya closed akan kembali menjadi posted.
Data rekap kapal pada tutup bulan tersebut dihapus.
Rekap dapat dibuat ulang setelah data diperbaiki.
```

---

## 3. Kapten Langsung di Form Kapal

Menu kapten tidak lagi menjadi alur utama.

Saat membuat kapal, admin langsung mengisi:

```text
Nama kapal
Nama kapten
Nomor HP kapten opsional
Tanggal mulai kapten
```

Baleta akan otomatis:

1. Mencari kapten dengan nama yang sama pada owner tersebut.
2. Jika ada, memakai data kapten itu.
3. Jika belum ada, membuat data kapten baru.
4. Menetapkan kapten sebagai kapten aktif kapal.

Dengan alur ini, admin tidak perlu mengisi menu kapten dulu.

---

## 4. Navigasi Mobile Baru

Navigasi mobile diperbaiki.

Sebelumnya:

```text
Menu cepat di atas memakai scroll horizontal.
```

Sekarang:

```text
Header mobile memiliki tombol Menu Pengaturan.
Saat ditekan, menu muncul sebagai panel.
```

Isi menu:

1. Kapal.
2. Invoice baru.
3. Tutup bulan.
4. Non-operasional.
5. Admin, khusus owner.

Bottom navigation tetap dipakai untuk menu utama.

---

## 5. Bahasa Tampilan

Beberapa istilah asing diganti agar lebih mudah dipahami nelayan.

Contoh perubahan:

```text
Dashboard       → Beranda
Print/Cetak     → Screenshot
Filter          → Saring
Cancel          → Batal
Password        → Kata sandi
Owner Final     → Hasil Pemilik dari Kapal
```

Istilah teknis di kode tetap memakai bahasa Inggris agar struktur Laravel tetap rapi.

---

## 6. Screenshot Laporan

Export Excel dan PDF tidak menjadi fokus.

Baleta sekarang menyediakan tampilan screenshot untuk:

1. Invoice harian.
2. Tutup bulan.
3. Rekap non-operasional.

Tampilan screenshot dibuat seperti kartu mobile yang bisa langsung di-screenshot dari HP.

Route baru:

```text
/invoices/{invoice}/screenshot
/monthly-closings/{monthlyClosing}/screenshot
/expenses/recap/screenshot
```

Route print lama tetap diarahkan ke tampilan screenshot untuk kompatibilitas.

---

## 7. File Penting yang Berubah

```text
routes/web.php
app/Http/Controllers/MonthlyClosingController.php
app/Services/MonthlyClosingService.php
app/Services/InvoiceNumberService.php
app/Http/Controllers/ShipController.php
app/Http/Requests/StoreShipRequest.php
app/Http/Requests/UpdateShipRequest.php
app/Http/Requests/StoreMonthlyClosingRequest.php
app/Http/Requests/UpdateMonthlyClosingRequest.php
app/Models/MonthlyClosing.php
app/Models/FishDeliveryInvoice.php
resources/views/layouts/app.blade.php
resources/views/monthly-closings/*
resources/views/ships/form.blade.php
resources/views/invoices/screenshot.blade.php
resources/views/owner-expenses/screenshot.blade.php
public/manifest.webmanifest
public/offline.html
```

Migration baru:

```text
database/migrations/2026_07_06_000017_update_monthly_closing_to_sequence_period.php
```

---

## 8. Cara Pasang

Ekstrak patch ke root project Laravel.

Jalankan:

```bash
composer dump-autoload
php artisan migrate
php artisan optimize:clear
npm run build
```

Untuk development:

```bash
npm run dev
```

---

## 9. Test Manual Wajib

Jalankan test berikut setelah patch dipasang:

```text
[ ] Login sebagai owner
[ ] Buat kapal dan input nama kapten langsung dari form kapal
[ ] Pastikan kapten otomatis muncul sebagai kapten aktif kapal
[ ] Buat invoice harian
[ ] Posting invoice
[ ] Buka tutup bulan
[ ] Pastikan sistem langsung menampilkan invoice posted tanpa pilih bulan kalender
[ ] Simpan tutup bulan
[ ] Edit tutup bulan
[ ] Ubah biaya operasional dan persentase kapten
[ ] Hapus tutup bulan
[ ] Pastikan invoice kembali menjadi posted
[ ] Buka tampilan screenshot invoice
[ ] Buka tampilan screenshot tutup bulan
[ ] Buka tampilan screenshot non-operasional
[ ] Cek menu mobile, pastikan tidak ada scroll horizontal di header
```

---

## 10. Catatan Arsitektur

Acuan utama Baleta setelah update ini:

```text
Invoice harian per kapal
        ↓
Tutup bulan berurutan berdasarkan invoice posted
        ↓
Rekap per kapal
        ↓
Hasil pemilik dari kapal
```

Non-operasional tetap berdiri sendiri dan tidak masuk tutup bulan kapal.
