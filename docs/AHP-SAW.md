# Studi kasus pemilihan rumah kost

Data awal mengacu pada `STUDI KASUS SPK NEW.pdf`: lima kriteria, 30 alternatif rumah kost, 150 skor alternatif, dan 10 perbandingan berpasangan AHP. Data disimpan oleh empat seeder terpisah: `CriteriaSeeder`, `AlternativeSeeder`, `AlternativeScoreSeeder`, dan `AhpComparisonSeeder`.

## Kriteria

| Kriteria | Atribut | Satuan |
| --- | --- | --- |
| Harga | Cost | Skor 1-5 |
| Jarak | Benefit | Skor 1-5 |
| Fasilitas | Benefit | Skor 1-5 |
| Keamanan | Benefit | Skor 1-5 |
| Luas Kamar | Benefit | Skor 1-5 |

`Jarak` mengikuti atribut **benefit** yang tertulis dalam dokumen, meskipun penilaian jarak pada kasus lain bisa berbeda. Urutan skor pada setiap alternatif adalah Harga, Jarak, Fasilitas, Keamanan, lalu Luas Kamar. Nama alternatif yang terpotong ke dua baris pada PDF digabung, misalnya `Hi Smart Boarding House` dan `Kost We Goona House`.

## Perbandingan AHP

| Pasangan | Nilai A terhadap B |
| --- | ---: |
| Harga - Jarak | 2 |
| Harga - Fasilitas | 3 |
| Harga - Keamanan | 2 |
| Harga - Luas Kamar | 4 |
| Jarak - Fasilitas | 2 |
| Jarak - Keamanan | 1 |
| Jarak - Luas Kamar | 3 |
| Fasilitas - Keamanan | 1/2 |
| Fasilitas - Luas Kamar | 2 |
| Keamanan - Luas Kamar | 3 |

Seeder mencari ID kriteria berdasarkan nama dan membalik nilai jika urutan ID database berbeda. Perbandingan yang sudah disimpan pengguna tidak ditimpa saat `AhpComparisonSeeder` dijalankan ulang.

Hasil AHP dari data ini: bobot sekitar `[0,375063; 0,214681; 0,121484; 0,214681; 0,074090]` pada urutan tabel kriteria, dan CR sekitar `0,007404` (konsisten).

## Menjalankan seeder

Pada database baru, jalankan:

```bash
php artisan migrate --seed
```

`DatabaseSeeder` memanggil seeder kriteria, pasangan AHP, alternatif, dan skor dalam urutan yang diperlukan. Menjalankan `db:seed` pada database lama **tidak menghapus** data studi kasus menu sebelumnya, sehingga kedua kasus akan tercampur. Jika ingin mengganti seluruh isi database lama, cadangkan data terlebih dahulu; `php artisan migrate:fresh --seed` menghapus semua tabel dan data sebelum membuatnya kembali. Perintah itu tidak dijalankan otomatis oleh perubahan kode ini.

## Batas perhitungan saat ini

Aplikasi menghitung bobot kriteria dengan AHP, lalu memeringkat alternatif dengan SAW (`benefit = nilai/maksimum`, `cost = minimum/nilai`). PDF menggunakan prioritas lokal AHP untuk alternatif dengan normalisasi terhadap jumlah seluruh alternatif. Karena rumus alternatifnya berbeda, angka skor dan sebagian urutan peringkat pada halaman SAW tidak harus sama dengan tabel akhir PDF, meskipun data seed dan bobot kriterianya sama.

Seeder menyimpan skor 1-5 sesuai PDF. Validasi formulir aplikasi saat ini masih menerima 1-100; perubahan rentang input dan istilah tampilan dari menu ke rumah kost berada di luar perubahan seeder ini.

Pengujian menggunakan database SQLite sementara:

```bash
php artisan test --compact
```
