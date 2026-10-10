# Rencana Produk Farming Planner

## Latar Belakang

HoYoLAB dapat digunakan untuk menyinkronkan karakter yang dimiliki, level karakter, constellation, senjata terpasang, artifact, status daily check-in, dan catatan real-time.

Namun HoYoLAB tidak menyediakan inventori lengkap material upgrade secara andal. Karena itu, aplikasi tidak boleh bergantung pada stok material hasil sinkronisasi untuk membuat rencana farming.

## Keputusan Produk

Pertahankan model data Task yang sudah ada, tetapi ubah perannya menjadi **Target Upgrade** atau **Target Build**.

Farming Planner menjadi halaman utama untuk perencanaan harian. Planner menggabungkan seluruh target aktif dan menjawab pertanyaan berikut:

- Material apa yang perlu difarm hari ini?
- Domain atau boss mana yang memberikan material tersebut?
- Material mana yang domainnya buka hari ini?
- Target mana yang memiliki prioritas tertinggi?
- Berapa estimasi penggunaan resin?

Planner tidak boleh menyatakan jumlah kekurangan material secara pasti kecuali pemain memilih untuk mengisi stok material secara manual.

## Sumber Data

| Data | Sumber |
| --- | --- |
| Karakter, level, constellation, senjata, artifact yang dimiliki | HoYoLAB atau Enka |
| Level dan talent target | Input pemain |
| Biaya ascension, talent, dan weapon | Data Genshin lokal atau Genshin DB |
| Jadwal domain dan lokasi sumber material | Master data material lokal |
| Jumlah material yang dimiliki | Input manual opsional |

## Alur Pengguna yang Disarankan

1. Pemain menyinkronkan karakter dari HoYoLAB atau Enka.
2. Pemain memilih karakter atau senjata lalu membuat Target Upgrade.
3. Pemain menentukan kondisi saat ini dan target, misalnya level 80 ke 90 atau talent 1/8/8 ke 1/10/10.
4. Kalkulator menghasilkan daftar kebutuhan material.
5. Pemain menentukan prioritas dan status target: belum dimulai, sedang farming, siap upgrade, atau selesai.
6. Farming Planner menggabungkan semua target aktif dan menampilkan rekomendasi domain serta boss hari ini.
7. Pemain mencatat progres dengan checklist ringan, jumlah run domain, atau resin yang dipakai. Inventori material penuh tidak diperlukan.

## Cakupan Farming Planner

### Rekomendasi Harian

- Menampilkan material untuk semua target aktif.
- Mengelompokkan rekomendasi berdasarkan domain atau boss.
- Menandai material yang tersedia pada hari ini.
- Mengurutkan berdasarkan prioritas target dan jumlah kebutuhan.
- Menampilkan label cadangan jika sumber atau jadwal material belum diisi.

### Perencanaan Resin

- Menampilkan kategori penggunaan resin: domain talent/senjata, normal boss, weekly boss, dan ley line.
- Mengestimasi jumlah run jika data drop tersedia.
- Mengizinkan pemain menentukan budget resin harian, misalnya 160 resin.
- Menampilkan urutan aktivitas yang disarankan sesuai budget.

### Progres Tanpa Sinkronisasi Inventori

Gunakan satu atau beberapa opsi sederhana berikut:

- Tandai kebutuhan material sebuah target sebagai selesai.
- Catat jumlah run domain atau resin yang telah dipakai.
- Catat progres target dalam persentase.
- Sediakan input stok material manual sebagai fitur lanjutan untuk pemain yang ingin perhitungan kekurangan secara tepat.

## Hubungan dengan Build Planner

Build Planner tetap berfokus pada kualitas build:

- Rekomendasi main stat dan substat.
- Senjata kompatibel yang sudah dimiliki.
- Artifact set terpasang dan progres bonus set.
- Kandidat artifact terbaik per slot berdasarkan score artifact yang tersimpan.

Build Planner dapat membuat atau memperbarui Target Upgrade. Farming Planner kemudian menampilkan aktivitas farming harian untuk target tersebut.

## Perubahan Istilah

| Istilah saat ini | Istilah yang disarankan |
| --- | --- |
| Task | Target Upgrade |
| Sub-task | Kebutuhan Material |
| Status Task | Status Target |
| Halaman Task | Target Upgrade |
| Farming Planner | Rencana Farming Harian |

## Tahap Implementasi

### Tahap 1: Planner berbasis target

- Ubah label Task pada antarmuka menjadi Target Upgrade.
- Pertahankan tabel task dan sub_task untuk kompatibilitas data.
- Hilangkan narasi yang bergantung pada stok material dari Farming Planner.
- Tampilkan total kebutuhan, sumber, hari ketersediaan, prioritas, dan status target.

### Tahap 2: Rencana resin harian

- Tambahkan budget resin dan kategori sumber.
- Tambahkan pencatatan run domain serta checklist harian.
- Tambahkan estimasi resin jika data sumber mendukung.

### Tahap 3: Pembuatan target yang lebih baik

- Gunakan data karakter hasil sync sebagai nilai awal pada kalkulator.
- Izinkan Build Planner membuat target secara langsung.
- Tambahkan template target untuk level karakter, talent, level senjata, dan peningkatan artifact.

### Tahap 4: Inventori opsional

- Pertahankan inventori material manual sebagai fitur lanjutan.
- Jangan jadikan inventori sebagai syarat untuk memakai planner.
- Jika inventori manual aktif, tampilkan stok, kebutuhan, dan sisa secara tepat.

## Catatan Teknis

- Microservice HoYoLAB saat ini tetap menangani data karakter, real-time notes, dan check-in.
- Jangan menambahkan fitur sync inventori material yang menyesatkan.
- Semua rekomendasi planner harus tetap bisa digunakan tanpa akun atau stok material manual.
- Ketersediaan domain harus menggunakan field hari pada master data material.

