# 🏆 Panduan Penggunaan Fitur Artifact Scoring & HoYoLAB Sync

Dokumen ini berisi panduan lengkap mengenai cara menggunakan fitur **Artifact Scoring System** dan **Sinkronisasi Inventori HoYoLAB** pada aplikasi Genshin Impact Planner.

---

## 📑 Daftar Isi
1. [Tentang Fitur Artifact Scoring](#-tentang-fitur-artifact-scoring)
2. [Langkah Awal: Sinkronisasi Data dari HoYoLAB](#-langkah-awal-sinkronisasi-data-dari-hoyolab)
3. [Cara Mengakses Fitur](#-cara-mengakses-fitur)
4. [Panduan Penggunaan Halaman Artifact Scoring](#-panduan-penggunaan-halaman-artifact-scoring)
   - [Memilih Akun & Melakukan Filter](#1-memilih-akun--filter)
   - [Skor Massal Sekaligus (Batch Score All)](#2-skor-massal-sekaligus-batch-score-all)
   - [Skor / Simulasi Per-Artifact](#3-skor--simulasi-per-artifact-interaktif)
   - [Membaca Detail Kontribusi Sub-Stat](#4-membaca-detail-kontribusi-sub-stat)
5. [Sistem Rating & Skala Nilai](#-sistem-rating--skala-nilai)
6. [Rumus & Cara Kerja Perhitungan Skor](#-rumus--cara-kerja-perhitungan-skor)
7. [Kustomisasi Bobot Stat & Archetype Karakter](#-kustomisasi-bobot-stat--archetype-karakter)
   - [Mengakses Halaman Aturan Scoring](#1-mengakses-halaman-aturan)
   - [Menggunakan Template Archetype](#2-menggunakan-template-archetype)
   - [Mengatur Bobot Manual (Slider 0.0 - 1.0)](#3-mengatur-bobot-manual)
8. [Pertanyaan yang Sering Diajukan (FAQ)](#-pertanyaan-yang-sering-diajukan-faq)

---

## 🌟 Tentang Fitur Artifact Scoring

Fitur **Artifact Scoring System** dirancang untuk membantu Anda mengevaluasi kualitas artifact yang Anda miliki secara objektif dan matematis. 

Berbeda dengan sistem CV (*Crit Value*) konvensional yang hanya melihat *CRIT Rate* dan *CRIT DMG*, sistem ini:
- Mengadaptasi konsep **Roll Quality** yang dinormalisasi.
- Memperhitungkan sub-stat relevan lainnya seperti **ATK%**, **Energy Recharge (ER)**, **Elemental Mastery (EM)**, **HP%**, dan **DEF%**.
- Disesuaikan dengan kebutuhan spesifik masing-masing **Karakter** atau peran (*Archetype*), sehingga artifact untuk karakter scaling HP (seperti Hu Tao / Neuvillette) atau Healer/Support dinilai dengan adil.

---

## 🔄 Sinkronisasi Data & Cara Mendapatkan Sub-Stat

Ada dua jalur sinkronisasi yang tersedia di dalam aplikasi:

### A. Sinkronisasi via Enka.Network API (Direkomendasikan untuk Sub-Stat Asli)
* **Kelebihan:** Menarik karakter & artifact yang sedang Anda pajang di *Character Showcase* in-game **lengkap dengan seluruh sub-stat asli 100%**, tanpa membutuhkan login cookie ataupun password (cukup UID publik).
* **Cara Menggunakan:**
  1. Pastikan di dalam game Genshin Impact (menu Edit Profil), opsi **"Tampilkan Detail Karakter"** / **"Show Character Details"** dalam keadaan **AKTIF**.
  2. Buka halaman **Artifact Scoring** (`/artifact-scoring`).
  3. Klik tombol **`Sync Enka (UID)`** di Quick Actions bar.
  4. Periksa UID akun Anda, lalu klik **"Mulai Sinkronkan (UID)"**.
  5. Seluruh artifact showcase beserta roll sub-stat aslinya akan langsung terisi dan dinilai secara otomatis.

### B. Sinkronisasi via HoYoLAB API
* **Kelebihan:** Menarik seluruh karakter dan inventori artifact yang Anda miliki dari akun HoYoLAB.
* **Catatan Sub-Stat:** Karena API publik HoYoLAB Battle Chronicle tidak mengekspos sub-stat, data awal dari HoYoLAB hanya memiliki Main Stat. Anda dapat melengkapi sub-statnya dengan tombol **"Input Sub-Stat"** manual atau tombol **"🎲 Isi Sub-Stat Simulasi"**.

---

## 🚀 Cara Mengakses Fitur

Ada dua cara untuk membuka fitur Artifact Scoring:

1. **Melalui Menu Navigasi:**
   - Arahkan kursor ke menu dropdown **Inventori** pada navigasi atas (navbar).
   - Klik sub-menu **"Artifact Scoring"**.
2. **Melalui URL Langsung:**
   - Kunjungi `http://localhost:8888/artifact-scoring` pada browser Anda.

---

## 📋 Panduan Penggunaan Halaman Artifact Scoring

### 1. Memilih Akun & Filter
Di bagian atas halaman, Anda dapat menyaring data artifact sesuai kebutuhan:
- **Pilih Akun**: Jika memiliki lebih dari satu akun Genshin Impact yang terdaftar, pilih akun yang ingin ditinjau.
- **Filter Karakter Pemakai**: Saring berdasarkan karakter spesifik yang sedang memakai artifact, atau pilih opsi *Sedang Dipakai (Semua)* maupun *Belum Dipakai (Kosong)*.
- **Filter Rating**: Saring berdasarkan tier kualitas (`SS`, `S`, `A`, `B`, `C`, `D`, atau `Semua Rating`).
- **Filter Slot**: Tampilkan slot tertentu saja (`Flower`, `Plume`, `Sands`, `Goblet`, `Circlet`).
- **Hanya yang Sudah Dinilai**: Centang opsi ini untuk menyembunyikan artifact yang belum memiliki skor.
- **Urutan (Sorting)**: Urutkan berdasarkan *Skor Tertinggi*, *Skor Terendah*, atau *Level Tertinggi*.
- Klik tombol **"Filter"** untuk menerapkan atau tombol **Reset** (ikon putar balik) untuk membersihkan semua filter.

---

### 2. Skor Massal Sekaligus (Batch Score All)
Jika Anda baru pertama kali menyinkronkan data atau memiliki banyak artifact yang belum dinilai:

1. Klik tombol **"⚡ Hitung Semua Skor"** yang berada di banner atas.
2. Sistem akan memproses seluruh artifact pada akun yang aktif:
   - Artifact yang sedang terpasang pada karakter akan dihitung berdasarkan aturan build karakter tersebut.
   - Artifact yang belum terpasang akan dihitung menggunakan formula default generalist (CRIT/ATK focus).
3. Setelah proses selesai, halaman akan memuat ulang dan menampilkan statistik ringkasan (jumlah SS, S, A, dll).

---

### 3. Skor / Simulasi Per-Artifact (Interaktif)
Pada setiap kartu artifact:
1. Terdapat dropdown **"Hitung untuk:"** yang menampilkan karakter-karakter yang Anda miliki.
2. Pilih nama karakter yang ingin Anda simulasikan (misal: Anda ingin melihat apakah Goblet ini bagus jika dipasangkan ke *Raiden Shogun* atau ke *Xiangling*).
3. Klik tombol **"Hitung Skor"**.
4. Nilai skor, badge rating huruf, dan rincian kontribusi sub-stat akan langsung diperbarui secara instan tanpa perlu memuat ulang seluruh halaman.

---

### 4. Membaca Detail Kontribusi Sub-Stat
Di bawah badge skor pada kartu artifact, klik panel **"Rincian Kontribusi Stat"** untuk melihat breakdown:
- **Nilai Asli**: Angka stat in-game (contoh: `CRIT DMG +21.0%`).
- **Rolls Equivalen**: Estimasi berapa kali stat tersebut ter-roll (contoh: `~3.18 roll`).
- **Bobot (Weight)**: Pengali relevansi untuk karakter terpilih (contoh: `1.0` untuk build CRIT).
- **Poin**: Kontribusi skor riil yang dihasilkan dari stat tersebut.

---

## 🎖️ Sistem Rating & Skala Nilai

Sistem memberikan predikat huruf berdasarkan total skor artifact:

| Rating | Rentang Skor | Kategori Kualitas | Rekomendasi |
| :---: | :---: | :--- | :--- |
| **SS** | **≥ 55.0** | 👑 **God Tier** | Kualitas sempurna, simpan dan kunci artifact ini. |
| **S** | **45.0 – 54.9** | ⭐ **Outstanding** | Sangat bagus, ideal untuk build endgame & Abyss Floor 12. |
| **A** | **35.0 – 44.9** | ✨ **Great** | Sangat layak pakai dan efisien untuk karakter utama. |
| **B** | **25.0 – 34.9** | 🔹 **Decent** | Bagus sebagai placeholder atau untuk karakter support. |
| **C** | **15.0 – 24.9** | 🔸 **Mediocre** | Kurang optimal, prioritaskan diganti saat ada opsi lebih baik. |
| **D** | **< 15.0** | ❌ **Fodder** | Stat tidak mendukung, cocok dijadikan bahan upgrade / Mystic Offering. |

---

## 🧮 Rumus & Cara Kerja Perhitungan Skor

Penghitungan skor mengadopsi normalisasi berdasarkan **nilai rata-rata 1 kali roll** (*Stat Roll Reference*) untuk artifact Bintang 5:

| Sub-Stat | Nilai Rata-rata 1 Roll |
| :--- | :---: |
| **CRIT Rate** | `3.30%` |
| **CRIT DMG** | `6.60%` |
| **ATK%** | `4.95%` |
| **HP%** | `4.95%` |
| **DEF%** | `6.20%` |
| **Elemental Mastery (EM)** | `19.75` |
| **Energy Recharge (ER)** | `5.50%` |
| **Flat ATK** | `16.50` |
| **Flat HP** | `253.00` |
| **Flat DEF** | `19.40` |

### Formula Perhitungan:
$$\text{Rolls} = \frac{\text{Nilai Sub-Stat}}{\text{Nilai Referensi Roll}}$$
$$\text{Kontribusi Stat} = \text{Rolls} \times \text{Bobot (0.0 - 1.0)} \times 10$$
$$\text{Total Skor} = \sum \text{Kontribusi Stat} \quad (\text{Maksimal } 100)$$

---

## ⚙️ Kustomisasi Bobot Stat & Archetype Karakter

Setiap karakter memiliki kebutuhan stat yang berbeda. Anda dapat mengatur bobot penilaian sesuai gaya bermain Anda.

### 1. Mengakses Halaman Aturan
1. Pada halaman **Artifact Scoring**, klik tombol **"⚙️ Aturan Scoring Karakter"** di pojok kanan atas (atau akses via URL `/artifact-scoring/rules`).
2. Pilih karakter yang ingin Anda ubah atau sesuaikan aturannya.

### 2. Menggunakan Template Archetype
Tersedia 5 template preset siap pakai:
1. **DPS — CRIT Build**: Fokus maksimal pada `CRIT Rate` (1.0), `CRIT DMG` (1.0), dan `ATK%` (0.75).
2. **DPS — Elemental Mastery Build**: Fokus utama pada `EM` (1.0) dan balanced `CRIT` (0.5).
3. **DPS — HP Scaling**: Untuk karakter seperti *Hu Tao*, *Yelan*, dan *Neuvillette* (`HP%` 1.0, `CRIT` 0.75, `ATK%` 0.0).
4. **Support**: Fokus pada `Energy Recharge` (1.0), `HP%`/`DEF%`, dan `EM`.
5. **Healer / Tank**: Fokus penuh pada `HP%` (1.0), `Energy Recharge` (1.0), dan `DEF%` (0.25).

Cukup pilih salah satu template dari dropdown preset, lalu slider nilai akan otomatis terisi.

### 3. Mengatur Bobot Manual
- Geser slider pada masing-masing dari 10 sub-stat antara **0.0 (Diabaikan)** hingga **1.0 (Prioritas Utama)**.
- Klik **"Simpan Perubahan Aturan"** untuk menyimpan.
- Semua artifact yang terhubung dengan karakter tersebut selanjutnya akan dinilai menggunakan formula baru ini.

---

## ❓ Pertanyaan yang Sering Diajukan (FAQ)

**Q: Mengapa artifact bintang 5 level 20 saya mendapatkan rating C atau D?**  
*A: Kemungkinan sub-stat yang ter-upgrade tidak sesuai dengan bobot karakter yang dipilih (misalnya banyak roll masuk ke Flat DEF/Flat HP pada karakter DPS), atau rule karakter belum disesuaikan.*

**Q: Apakah skor artifact yang belum dipakai karakter bisa dihitung?**  
*A: Bisa. Secara default sistem menggunakan bobot standar DPS umum, atau Anda dapat memilih nama karakter secara manual pada dropdown kartu artifact untuk mensimulasikannya.*

**Q: Apakah data di game saya berubah ketika saya mengubah skor di web ini?**  
*A: Tidak. Aplikasi ini hanya membaca data inventori dari HoYoLAB dan melakukan kalkulasi lokal. Tidak ada modifikasi data ke server game Genshin Impact.*

---

*Panduan ini dibuat untuk mempermudah pemain memaksimalkan alokasi artifact dan pengelolaan resin secara efisien.* 🎮✨
