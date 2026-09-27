# Genshin Impact Material & Task Planner ⚔️✨

Aplikasi berbasis web untuk mengelola inventaris material, merencanakan target upgrade karakter, senjata, dan talent pada game **Genshin Impact**, serta melakukan kalkulasi otomatis terhadap kebutuhan material dan fitur crafting.

---

## 🛠️ Tech Stack

Aplikasi ini dibangun menggunakan kombinasi teknologi modern berikut:

- **Backend**: **PHP 8.2+** dengan **Laravel 12 Framework**
- **Frontend**: **Blade Templating**, HTML5, CSS3, & JavaScript
- **Database**: **MySQL / SQLite** (Dikelola dengan **Laravel Eloquent ORM** & Migration)
- **Asset Bundler**: **Vite**
- **Dependency Manager**: **Composer** (PHP) & **NPM** (Node.js)

---

## ⚙️ Cara Kerja Aplikasi

Aplikasi ini bekerja sebagai **Material Inventory & Upgrade Planner** dengan alur kerja berikut:

1. **Manajemen Material & Famili**:
   - Material dikelompokkan berdasarkan **Family** (misalnya famili Slime, Specter, Talent Books, dll.).
   - Setiap material memiliki properti seperti rarity (bintang 1-5), kategori, drop domain, hari aktif drop, serta jumlah stok yang dimiliki (`amount`).

2. **Perencanaan Task (Target Upgrade)**:
   - Pengguna menambahkan **Task** baru (misal: Upgrade Character Stat, Weapon, atau Talent) dengan menentukan tingkat prioritas.
   - Pengguna memilih material yang dibutuhkan beserta jumlahnya yang disimpan sebagai **Sub-Task**.

3. **Kalkulasi Otomatis & Auto-Crafting Simulation**:
   - Sistem secara otomatis membandingkan stok material yang dimiliki dengan kebutuhan pada task.
   - Jika stok material tier tinggi kurang, sistem secara cerdas menghitung opsi **Crafting** dari material tier lebih rendah dalam famili yang sama (menggunakan rasio kelipatan 3:1).
   - Indikator `statusUpgrade` akan aktif apabila total stok (stok aktual + potensi hasil crafting) mencukupi kebutuhan task.

4. **Eksekusi Crafting & Penyelesaian Task**:
   - Pengguna dapat mengeksekusi fitur **Crafting Build** untuk langsung mengonversi material tier bawah menjadi tier atas pada inventaris.
   - Saat task diselesaikan (`Task Complete`), stok material di inventaris akan dipotong secara otomatis sesuai jumlah kebutuhan sub-task.

---

## ⭐ Daftar Fitur Utama

- 📦 **Inventory Management (Material & Family)**:
  - Manajemen data kelompok material (Family).
  - Manajemen data material lengkap dengan deskripsi, gambar, rarity, sumber perolehan, dan hari drop domain.
  - Quick edit jumlah stok material.

- 🎯 **Task & Target Planner**:
  - Penambahan task upgrade berdasarkan jenis: `stat`, `weapon`, atau `talent`.
  - Penentuan prioritas task untuk menentukan urutan alokasi material.
  - Pemetaan material yang dibutuhkan ke dalam sub-task.

- 🧪 **Smart Crafting Engine**:
  - Perhitungan otomatis konversi material (3 material tier rendah = 1 material tier lebih tinggi).
  - Deteksi kelayakan upgrade task berdasarkan ketersediaan material + konversi craft.
  - Fitur eksekusi `CraftingBuild` untuk memperbarui stok material secara riil.

- ✅ **Automatic Stock Deduction on Completion**:
  - Menyelesaikan task akan secara otomatis mengurangkan stok material terkait dari database inventaris.

---

## 🗄️ Rancangan Database

Database aplikasi ini **sudah dirancang penuh** menggunakan Laravel Migration & Eloquent ORM. Berikut struktur entitas utamanya:

| Nama Tabel | Deskripsi & Fungsi | Relasi Utama |
| :--- | :--- | :--- |
| `families` | Mengelompokkan jenis famili material | HasMany `materials` |
| `materials` | Menyimpan detail item, rarity, stok (`amount`), drop domain, hari drop, & sumber | BelongsTo `families`, HasMany `sub_tasks` |
| `tasks` | Menyimpan target upgrade (`nama_task`, `jenis`, `status`, `prioritas`, `images`) | HasMany `sub_tasks` |
| `sub_tasks` | Pivot table kebutuhan material untuk setiap task (`task_id`, `material_id`, `amount`) | BelongsTo `tasks`, BelongsTo `materials` |
| `sumbers` | Data referensi sumber lokasi perolehan material | - |

---

## 🚀 Panduan Instalasi & Penggunaan

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB (atau SQLite)
- Web Server (Laragon, XAMPP, atau Artisan CLI)

### Langkah Setup

1. **Clone repository & masuk ke direktori proyek**:
   ```bash
   git clone <repository-url>
   cd genshin-app
   ```

2. **Install Dependensi PHP & Node**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Atur koneksi database pada file `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

4. **Jalankan Migration & Seeder**:
   ```bash
   php artisan migrate
   ```

5. **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses di `http://127.0.0.1:8000`.

---
