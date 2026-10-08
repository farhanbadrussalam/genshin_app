# Panduan Penggunaan Postman Collection: Genshin App Microservices & Sync APIs

File koleksi Postman telah disediakan di root proyek:
📁 [`genshin_microservices.postman_collection.json`](./genshin_microservices.postman_collection.json)

---

## 🚀 Cara Import ke Postman

1. Buka aplikasi **Postman**.
2. Klik tombol **Import** (di pojok kiri atas).
3. Pilih tab **Files** lalu pilih file:
   `genshin_microservices.postman_collection.json`
4. Koleksi bernama **"Genshin App - Microservices & Sync APIs"** akan muncul di sidebar koleksi Anda.

---

## ⚙️ Variabel Koleksi (Collection Variables)

Di dalam koleksi ini sudah disertakan variabel otomatis yang bisa Anda ubah nilainya (klik nama koleksi → tab **Variables**):

| Nama Variabel | Nilai Default | Penjelasan |
| :--- | :--- | :--- |
| `hoyolab_base_url` | `http://localhost:8001` | URL langsung ke Python FastAPI Microservice |
| `laravel_base_url` | `http://localhost:8888` | URL aplikasi Laravel / Nginx |
| `ltuid_v2` | `123456789` | Cookie `ltuid_v2` dari [hoyolab.com](https://www.hoyolab.com) |
| `ltoken_v2` | `v2_CAISDGhveW9sYWI...` | Cookie `ltoken_v2` dari [hoyolab.com](https://www.hoyolab.com) |
| `uid` | `812345678` | UID game Genshin Impact akun Anda (in-game) |
| `account_id` | `1` | ID akun game di database lokal Laravel Anda |

---

## 📂 Struktur Endpoint dalam Koleksi

### 1. HoYoLAB Microservice (Direct FastAPI - Port 8001)
Endpoint langsung ke service Python (`genshin.py`):
- **`GET /health`** : Pengecekan status kesehatan microservice Python.
- **`POST /api/genshin/characters`** : Mengambil data Battle Chronicle (karakter, level, konstelasi, senjata, dan artefak) menggunakan cookie `ltuid_v2` dan `ltoken_v2`.
- **`GET /openapi.json`** : Mendapatkan spesifikasi OpenAPI / Swagger schema dalam format JSON.

### 2. Laravel - HoYoLAB Microservice Bridge (Port 8888)
Endpoint orkestrasi di backend Laravel:
- **`GET /hoyolab/ping`** : Laravel mengecek konektivitas jaringan internal ke microservice Python.
- **`POST /hoyolab/import-characters`** : Mengambil karakter dari HoYoLAB lalu otomatis mengonversinya menjadi draft **Upgrade Tasks** di Laravel.

### 3. Laravel - Inventory Sync Services (HoYoLAB & Enka)
Endpoint sinkronisasi data inventori akun game:
- **`POST /inventory/characters/sync-hoyolab`** : Sinkronisasi karakter akun dari HoYoLAB.
- **`POST /inventory/characters/sync-enka`** : Sinkronisasi karakter showcase akun via UID dari Enka.Network.
- **`POST /inventory/weapons/sync-hoyolab`** : Sinkronisasi senjata akun dari HoYoLAB.
- **`POST /inventory/weapons/sync-enka`** : Sinkronisasi senjata showcase akun via UID dari Enka.Network.
- **`POST /inventory/artifacts/sync-hoyolab`** : Sinkronisasi artefak akun dari HoYoLAB.
- **`POST /inventory/artifacts/sync-enka`** : Sinkronisasi artefak + sub-stats akun via UID dari Enka.Network.

### 4. Laravel - Artifact Scoring & Sub-stats Services
Layanan penilaian skor artefak dan simulasi roll sub-stats:
- **`POST /artifact-scoring/sync-enka`** : Tarik artefak + sub-stats via UID dan langsung lakukan kalkulasi skor artefak otomatis.
- **`POST /artifact-scoring/score-all`** : Kalkulasi ulang seluruh skor artefak pada akun berdasarkan konfigurasi bobot karakter.
- **`POST /artifact-scoring/generate-mock-substats`** : Membuat simulasi sub-stats acak realistis untuk artefak yang belum memiliki sub-stat.
- **`GET /artifact-scoring/preset?archetype=dps_atk`** : Mengambil bobot standar penilaian sesuai archetype build (contoh: `dps_atk`, `dps_hp`, `support_healer`, dll).

### 5. Laravel - Master Data Sync Services
Sinkronisasi katalog referensi game:
- **`POST /character/sync-all?source=amber`** : Sinkronisasi data master karakter dari **Project Amber (`gi.yatta.moe`)** ke database lokal (katalog terlengkap dengan Region/Bangsa dan icon HD).
- **`POST /character/sync-all?source=enka`** : Sinkronisasi data master karakter dari repositori **Enka.Network**.
- **`POST /weapon/sync-all`** : Sinkronisasi seluruh data master senjata dari database Enka ke database lokal.

---

## 💡 Catatan Tambahan
Semua endpoint sinkronisasi Laravel di atas telah dikecualikan dari verifikasi CSRF token pada `app/Http/Middleware/VerifyCsrfToken.php`, sehingga Anda dapat langsung melakukan pengujian via Postman tanpa kendala `419 Page Expired / CSRF token mismatch`.
