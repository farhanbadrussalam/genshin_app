<?php

namespace App\Services;

use App\Models\GameAccount;
use App\Models\PartyBenchmark;
use App\Models\SavedParty;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartyBenchmarkService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->model  = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash'));
    }

    /**
     * Ekstrak data hasil pengujian DPS dari gambar/screenshot menggunakan Google Gemini Vision API
     */
    public function extractFromScreenshot(UploadedFile|string $image): array
    {
        if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY belum dikonfigurasi di sistem.');
        }

        $imageBytes = ($image instanceof UploadedFile)
            ? file_get_contents($image->getRealPath())
            : file_get_contents($image);

        $mimeType = ($image instanceof UploadedFile)
            ? ($image->getClientMimeType() ?: 'image/jpeg')
            : (mime_content_type($image) ?: 'image/jpeg');

        $base64Image = base64_encode($imageBytes);

        $prompt = <<<TEXT
Ekstrak seluruh informasi dari screenshot DPS Test Dummy++ Genshin Impact ini ke dalam format JSON murni.
Pastikan semua angka diekstrak dengan akurat sesuai data visual pada gambar.

Format JSON yang diwajibkan:
{
  "preset_name": "String nama preset, misal 'Spiral Abyss Lantai 12'",
  "dps": Integer angka DPS total,
  "total_time": Float total waktu dalam detik (misal 195.87),
  "total_damage": Integer total damage seluruh pengujian,
  "max_hit": Integer angka hit terkuat (nuke),
  "target_dummy": {
    "name": "Nama dummy target, misal 'SSR Teddy'",
    "level": Integer level target,
    "elemental_res": {
      "pyro": Integer persen,
      "hydro": Integer persen,
      "electro": Integer persen,
      "cryo": Integer persen,
      "anemo": Integer persen,
      "geo": Integer persen,
      "dendro": Integer persen,
      "physical": Integer persen
    }
  },
  "modifiers": "String modifikator stage bila ada, atau kosongkan",
  "character_dmg_distribution": [
    {
      "name": "Nama karakter",
      "element": "Pyro/Hydro/Anemo/Electro/Dendro/Cryo/Geo",
      "damage": Integer total damage karakter ini,
      "percentage": Integer persen kontribusi (0 - 100)
    }
  ],
  "elemental_dmg_distribution": {
    "pyro": Integer persen,
    "hydro": Integer persen,
    "electro": Integer persen,
    "cryo": Integer persen,
    "anemo": Integer persen,
    "geo": Integer persen,
    "dendro": Integer persen,
    "physical": Integer persen
  },
  "rotations": [
    {
      "rotation": Integer nomor rotasi (1, 2, 3...),
      "dps": Integer angka DPS pada rotasi ini (misal 107000 jika 107K),
      "damage": Integer total damage pada rotasi ini,
      "time": Float durasi rotasi dalam detik (misal 24.60)
    }
  ],
  "character_stats": [
    {
      "name": "Nama karakter",
      "element": "Elemen karakter",
      "level": Integer level karakter,
      "hp": Integer HP,
      "base_atk": Integer Base ATK,
      "total_atk": Integer Total ATK,
      "base_def": Integer Base DEF,
      "total_def": Integer Total DEF,
      "crit_rate": Float persentase CRIT Rate (contoh: 55.0 untuk 55%, BUKAN 0.55),
      "crit_dmg": Float persentase CRIT DMG (contoh: 215.0 untuk 215%, BUKAN 2.15),
      "energy_recharge": Float persentase Energy Recharge (contoh: 192.0 untuk 192%, BUKAN 1.92),
      "elemental_mastery": Integer Elemental Mastery,
      "dmg_bonus": {
        "pyro": Float persen,
        "hydro": Float persen,
        "electro": Float persen,
        "cryo": Float persen,
        "anemo": Float persen,
        "geo": Float persen,
        "dendro": Float persen,
        "physical": Float persen
      },
      "stat_bonus": "String bonus status jika ada"
    }
  ],
  "stage_guid": "String GUID Stage jika tertera di kanan bawah",
  "uid": "String UID pemain jika tertera di kanan bawah"
}
TEXT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data'      => $base64Image,
                            ],
                        ],
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature'        => 0.1,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Gemini Vision Extraction Error: ' . $response->body());
            throw new \Exception('Gagal mengekstrak gambar dari Gemini API: ' . ($response->json('error.message') ?? $response->status()));
        }

        $result = $response->json();
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new \Exception('Respons Gemini tidak berisi data teks.');
        }

        $parsed = json_decode($text, true);
        if (!is_array($parsed)) {
            throw new \Exception('Gagal menguraikan JSON hasil ekstraksi Gemini.');
        }

        // Normalisasi persentase jika AI mengembalikan format desimal 0-1
        if (!empty($parsed['character_stats']) && is_array($parsed['character_stats'])) {
            foreach ($parsed['character_stats'] as &$cs) {
                if (isset($cs['crit_rate']) && (float)$cs['crit_rate'] <= 1.0 && (float)$cs['crit_rate'] > 0) {
                    $cs['crit_rate'] = round((float)$cs['crit_rate'] * 100, 1);
                }
                if (isset($cs['crit_dmg']) && (float)$cs['crit_dmg'] <= 3.5 && (float)$cs['crit_dmg'] > 0) {
                    $cs['crit_dmg'] = round((float)$cs['crit_dmg'] * 100, 1);
                }
                if (isset($cs['energy_recharge']) && (float)$cs['energy_recharge'] <= 4.0 && (float)$cs['energy_recharge'] > 0) {
                    $cs['energy_recharge'] = round((float)$cs['energy_recharge'] * 100, 1);
                }
            }
            unset($cs);
        }

        return $parsed;
    }

    /**
     * Hitung evaluasi otomatis kecocokan dan kestabilan performa tim
     */
    public function evaluateBenchmark(array $data): array
    {
        $dps = (int)($data['dps'] ?? 0);

        // 1. Tier Penilaian
        $tier = match (true) {
            $dps >= 100000 => 'SS',
            $dps >= 70000  => 'S',
            $dps >= 50000  => 'A',
            default        => 'B',
        };

        $tierLabel = match ($tier) {
            'SS'    => 'Overkill / Meta Endgame',
            'S'     => 'Abyss Floor 12 Ready (Sangat Kuat)',
            'A'     => 'Layak Lulus Spiral Abyss 36★',
            default => 'Butuh Peningkatan Build & Rotasi',
        };

        // 2. Archetype Tim (Distribusi Damage Karakter)
        $charDmg = $data['character_dmg_distribution'] ?? [];
        $archetype = 'Balanced / Quickswap';
        $mainDpsName = null;

        if (!empty($charDmg)) {
            usort($charDmg, fn($a, $b) => ($b['percentage'] ?? 0) <=> ($a['percentage'] ?? 0));
            $top = $charDmg[0];
            $topPct = $top['percentage'] ?? 0;
            $mainDpsName = $top['name'] ?? null;

            if ($topPct >= 70) {
                $archetype = "Hypercarry ({$top['name']} - {$topPct}%)";
            } elseif ($topPct >= 45 && isset($charDmg[1]) && ($charDmg[1]['percentage'] ?? 0) >= 30) {
                $archetype = "Dual Carry ({$top['name']} & {$charDmg[1]['name']})";
            }
        }

        // 3. Elemen Dominan
        $elemDmg = $data['elemental_dmg_distribution'] ?? [];
        $dominantElement = 'Multi-Elemental';
        if (!empty($elemDmg)) {
            arsort($elemDmg);
            $topElem = array_key_first($elemDmg);
            $topElemPct = current($elemDmg);
            if ($topElemPct >= 50) {
                $dominantElement = ucfirst($topElem) . " Dominant ({$topElemPct}%)";
            }
        }

        // 4. Analisis Konsistensi Rotasi & Bottleneck Energy
        $rotations = $data['rotations'] ?? [];
        $rotationAnalysis = [
            'total_rotations'   => count($rotations),
            'avg_duration'      => 0,
            'avg_dps'           => 0,
            'is_stable'         => true,
            'bottlenecks'       => [],
        ];

        $warnings = [];
        $positives = [];
        $recommendations = [];

        if (!empty($rotations)) {
            $durations = array_column($rotations, 'time');
            $dpsList = array_column($rotations, 'dps');

            $avgDur = count($durations) > 0 ? (array_sum($durations) / count($durations)) : 0;
            $avgDps = count($dpsList) > 0 ? (array_sum($dpsList) / count($dpsList)) : 0;

            $rotationAnalysis['avg_duration'] = round($avgDur, 2);
            $rotationAnalysis['avg_dps'] = (int)round($avgDps);

            // Periksa rotasi yang molor (> 15% dari rata-rata durasi)
            foreach ($rotations as $r) {
                $time = (float)($r['time'] ?? 0);
                if ($time > ($avgDur * 1.15)) {
                    $diff = round($time - $avgDur, 2);
                    $rotNum = $r['rotation'] ?? '?';
                    $msg = "Rotasi {$rotNum} memanjang hingga {$time}d (+{$diff}d dari rata-rata). Indikasi kekurangan Energy Recharge (ER%) sehingga Burst terlambat diaktifkan.";
                    $rotationAnalysis['bottlenecks'][] = $msg;
                    $warnings[] = $msg;
                    $rotationAnalysis['is_stable'] = false;
                }
            }

            if ($rotationAnalysis['is_stable']) {
                $positives[] = "Waktu rotasi sangat stabil di kisaran rata-rata " . round($avgDur, 1) . " detik per rotasi.";
            }
        }

        // 5. Audit Stat Karakter (CRIT Ratio & Support ER)
        $charStats = $data['character_stats'] ?? [];
        foreach ($charStats as $cs) {
            $name = $cs['name'] ?? 'Karakter';
            $cr = (float)($cs['crit_rate'] ?? 0);
            $cd = (float)($cs['crit_dmg'] ?? 0);
            $er = (float)($cs['energy_recharge'] ?? 0);

            // Audit Carry CRIT
            if ($name === $mainDpsName) {
                if ($cr > 0 && $cd > 0) {
                    $ratio = $cd / $cr;
                    if ($cr < 60 && $cd >= 180) {
                        $warnings[] = "Rasio CRIT {$name} belum optimal ({$cr}% CR : {$cd}% CD). CRIT Rate disarankan ditingkatkan minimal 65-75% agar fluktuasi DPS antar rotasi berkurang.";
                        $recommendations[] = "Gunakan Circlet CRIT Rate atau cari substat CRIT Rate untuk {$name}.";
                    } elseif ($ratio >= 1.7 && $ratio <= 2.3) {
                        $positives[] = "Rasio CRIT {$name} sangat seimbang mendekati standar emas 1:2 ({$cr}% CR : {$cd}% CD).";
                    }
                }
            }

            // Audit Buffer/Support ER
            if (in_array($name, ['Faruzan', 'Bennett', 'Xingqiu', 'Yelan', 'Xiangling', 'Gorou', 'Sara']) && $er < 180) {
                $warnings[] = "{$name} hanya memiliki {$er}% Energy Recharge. Untuk rotasi lancar di Abyss, disarankan minimal 190% - 220% ER.";
                $recommendations[] = "Pertimbangkan Energy Recharge Sands atau senjata seri Favonius/Sacrificial untuk {$name}.";
            }
        }

        if ($tier === 'SS') {
            $positives[] = "DPS tim melampaui 100K (" . number_format($dps, 0, ',', '.') . " DPS) — performa luar biasa, musuh Abyss Lantai 12 dapat diselesaikan dengan sangat cepat.";
        }

        return [
            'tier'             => $tier,
            'tier_label'       => $tierLabel,
            'archetype'        => $archetype,
            'dominant_element' => $dominantElement,
            'rotation'         => $rotationAnalysis,
            'warnings'         => $warnings,
            'positives'        => $positives,
            'recommendations'  => $recommendations,
        ];
    }

    /**
     * Susun teks Markdown komprehensif berisi seluruh data benchmark + instruksi prompt profesional untuk Gemini AI
     */
    public function generateGeminiPrompt(PartyBenchmark $benchmark, ?GameAccount $account = null): string
    {
        $party = $benchmark->savedParty;
        $eval  = $benchmark->evaluation ?: $this->evaluateBenchmark($benchmark->toArray());

        $md = [];
        $md[] = "# ⚔️ Analisis Evaluasi Performa Tim Genshin Impact — Gemini AI Theorycrafter";
        $md[] = "> Hasil Pengujian: {$benchmark->preset_name} | Tanggal Uji: " . $benchmark->created_at->format('d M Y, H:i') . " | UID: {$benchmark->uid}";
        $md[] = "";

        // Instruksi Persona & Task untuk Gemini
        $md[] = "## 🤖 Instruksi untuk Gemini AI";
        $md[] = "```text";
        $md[] = "Halo Gemini! Bertindaklah sebagai Theorycrafter Genshin Impact profesional kelas dunia dan Combat Analyst.";
        $md[] = "Saya telah melakukan pengujian DPS pada tim saya menggunakan dummy target level 100 dengan data pengujian super lengkap di bawah ini.";
        $md[] = "Mohon berikan analisis mendalam dan solusi terperinci mengenai:";
        $md[] = "1. [Evaluasi Struktur & Archetype Tim]: Apakah komposisi tim ini bersinergi dengan maksimal? Siapa yang paling berperan?";
        $md[] = "2. [Analisis Fluktuasi Rotasi]: Perhatikan tabel rotasi 1 s/d " . count($benchmark->rotations ?? []) . ". Mengapa waktu rotasi tertentu melambat atau DPS berfluktuasi? Karakter mana yang menjadi bottleneck energy?";
        $md[] = "3. [Audit Stat Karakter]: Berdasarkan tabel snapshot stat 4 karakter, siapa yang stat-nya paling butuh perbaikan (CRIT Ratio, ATK, ER%, EM)?";
        $md[] = "4. [Rekomendasi Rotasi & Urutan Skill/Burst]: Tuliskan urutan tombol/rotasi paling optimal langkah demi langkah (misal: Karakter A (E -> Q) -> Karakter B (Q) -> ...).";
        $md[] = "```";
        $md[] = "";

        // Profil Tim
        $md[] = "## 🛡️ Komposisi Tim: " . ($party ? $party->name : $benchmark->title);
        if ($party) {
            $charNames = $party->characters->pluck('name')->implode(', ');
            $md[] = "- **Anggota Party:** {$charNames}";
            if (!empty($party->elemental_resonances)) {
                $resNames = array_map(fn($r) => "{$r['name']} ({$r['badge']})", $party->elemental_resonances);
                $md[] = "- **Resonansi Elemental Aktif:** " . implode(', ', $resNames);
            }
        }
        $md[] = "- **Klasifikasi Evaluasi:** Tier **{$benchmark->tier}** ({$benchmark->tier_label})";
        $md[] = "- **Tipe Struktur Tim:** {$eval['archetype']}";
        $md[] = "- **Elemen Dominan:** {$eval['dominant_element']}";
        $md[] = "";

        // Ringkasan Angka Pengujian
        $md[] = "## 📊 Metrik Utama Hasil Pengujian";
        $md[] = "| Metrik | Nilai | Keterangan |";
        $md[] = "| :--- | :---: | :--- |";
        $md[] = "| **Rata-rata DPS Tim** | **" . number_format($benchmark->dps, 0, ',', '.') . "** | Total damage dibagi total waktu |";
        $md[] = "| **Total Waktu Tempur** | **{$benchmark->total_time} detik** | Akumulasi seluruh rotasi pengujian |";
        $md[] = "| **Total Damage Dihasilkan** | **" . number_format($benchmark->total_damage, 0, ',', '.') . "** | Total akumulasi angka damage |";
        $md[] = "| **Hit Terkuat (Nuke Peak)** | **" . number_format($benchmark->max_hit, 0, ',', '.') . "** | Damage tertinggi dalam 1 serangan |";
        $target = $benchmark->target_dummy ?? [];
        $dummyName = ($target['name'] ?? 'SSR Teddy') . " (Lv. " . ($target['level'] ?? 100) . ")";
        $md[] = "| **Target Uji (Dummy)** | **{$dummyName}** | Resistensi Elemental: 10% rata seluruh elemen |";
        $md[] = "";

        // Distribusi Damage Karakter
        if (!empty($benchmark->character_dmg_distribution)) {
            $md[] = "## 👥 Kontribusi Damage Tiap Karakter";
            $md[] = "| Karakter | Elemen | Total Damage | Kontribusi (%) |";
            $md[] = "| :--- | :---: | :---: | :---: |";
            foreach ($benchmark->character_dmg_distribution as $cd) {
                $dmgStr = number_format($cd['damage'] ?? 0, 0, ',', '.');
                $md[] = "| **{$cd['name']}** | {$cd['element']} | {$dmgStr} | **{$cd['percentage']}%** |";
            }
            $md[] = "";
        }

        // Distribusi Damage Elemen
        if (!empty($benchmark->elemental_dmg_distribution)) {
            $md[] = "## 🌪️ Distribusi Damage Berdasarkan Elemen";
            $elemParts = [];
            foreach ($benchmark->elemental_dmg_distribution as $elem => $pct) {
                if ($pct > 0) {
                    $elemParts[] = "**" . ucfirst($elem) . ":** {$pct}%";
                }
            }
            $md[] = implode(" | ", $elemParts);
            $md[] = "";
        }

        // Rincian Rotasi
        if (!empty($benchmark->rotations)) {
            $md[] = "## 🔄 Rincian Performa Tiap Rotasi (1 s/d " . count($benchmark->rotations) . ")";
            $md[] = "| Rotasi # | DPS Rotasi | Total Damage Rotasi | Durasi Waktu | Status Rotasi |";
            $md[] = "| :---: | :---: | :---: | :---: | :--- |";

            $avgDur = $eval['rotation']['avg_duration'] ?? 23;
            foreach ($benchmark->rotations as $rot) {
                $dpsStr = number_format($rot['dps'] ?? 0, 0, ',', '.');
                $dmgStr = number_format($rot['damage'] ?? 0, 0, ',', '.');
                $dur = (float)($rot['time'] ?? 0);
                $status = ($dur > ($avgDur * 1.15)) ? "⚠️ Molor (+ " . round($dur - $avgDur, 1) . "s) - ER Issue" : "✅ Normal & Stabil";
                $md[] = "| **Rotasi {$rot['rotation']}** | {$dpsStr} | {$dmgStr} | {$dur} detik | {$status} |";
            }
            $md[] = "";
        }

        // Snapshot Stat Karakter
        if (!empty($benchmark->character_stats)) {
            $md[] = "## 🧬 Snapshot Stat 4 Karakter Saat Pengujian";
            $md[] = "| Karakter | Lv. | HP | ATK (Base/Total) | DEF (Base/Total) | CRIT Rate | CRIT DMG | ER% | EM | Dmg Bonus |";
            $md[] = "| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :--- |";

            foreach ($benchmark->character_stats as $st) {
                $atkStr = ($st['base_atk'] ?? 0) . " / " . ($st['total_atk'] ?? 0);
                $defStr = ($st['base_def'] ?? 0) . " / " . ($st['total_def'] ?? 0);
                $crStr  = ($st['crit_rate'] ?? 0) . "%";
                $cdStr  = ($st['crit_dmg'] ?? 0) . "%";
                $erStr  = ($st['energy_recharge'] ?? 0) . "%";
                $emStr  = ($st['elemental_mastery'] ?? 0);

                // Cari dmg bonus aktif
                $dmgBonuses = [];
                foreach ($st['dmg_bonus'] ?? [] as $bElem => $bVal) {
                    if ($bVal > 0) {
                        $dmgBonuses[] = ucfirst($bElem) . " +{$bVal}%";
                    }
                }
                $bonusStr = implode(', ', $dmgBonuses) ?: '-';

                $md[] = "| **{$st['name']}** ({$st['element']}) | {$st['level']} | " . number_format($st['hp'] ?? 0, 0, ',', '.') . " | {$atkStr} | {$defStr} | {$crStr} | {$cdStr} | {$erStr} | {$emStr} | {$bonusStr} |";
            }
            $md[] = "";
        }

        // Diagnosa Awal dari Sistem
        $md[] = "## 🔍 Diagnosa Awal Sistem";
        if (!empty($eval['positives'])) {
            $md[] = "### ✅ Kelebihan Tim:";
            foreach ($eval['positives'] as $pos) {
                $md[] = "- {$pos}";
            }
        }
        if (!empty($eval['warnings'])) {
            $md[] = "### ⚠️ Peringatan & Area Bottleneck:";
            foreach ($eval['warnings'] as $warn) {
                $md[] = "- {$warn}";
            }
        }
        if (!empty($eval['recommendations'])) {
            $md[] = "### 💡 Saran Peningkatan:";
            foreach ($eval['recommendations'] as $rec) {
                $md[] = "- {$rec}";
            }
        }
        $md[] = "";

        $md[] = "---";
        $md[] = "*Laporan digenerate otomatis oleh Genshin App Party Analyzer. Siap untuk dianalisis oleh Google Gemini.*";

        return implode("\n", $md);
    }
}
