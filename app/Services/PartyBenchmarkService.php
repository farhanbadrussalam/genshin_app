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

    /**
     * Asisten Tanya-Jawab Interaktif (Multi-turn Chat) dengan Gemini AI Theorycrafter
     */
    public function chatWithTheorycrafter(
        PartyBenchmark $benchmark,
        string $userMessage,
        array $history = [],
        string $modelChoice = 'flash'
    ): array {
        if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY belum dikonfigurasi di file .env sistem.');
        }

        // Bangun konteks data benchmark lengkap
        $benchmarkContext = $this->generateGeminiPrompt($benchmark);

        $systemPrompt = <<<SYS
Kamu adalah **Theorycrafter & Combat Analyst Genshin Impact Profesional**.
Tugasmu adalah menjawab pertanyaan pemain, menganalisis hasil pengujian DPS mereka, membedah rotasi, serta memberikan saran build (stat karakter, senjata, artefak, dan urutan kombo Skill/Burst) secara akurat.

Aturan Jawaban:
1. Selalu gunakan **Bahasa Indonesia** yang jelas, ramah, dan terstruktur.
2. Gunakan format Markdown (bullet points, penebalan **bold** pada angka/stat penting, dan sub-judul) agar nyaman dibaca di antarmuka chat.
3. Selalu gunakan data aktual dari pengujian tim di bawah ini sebagai landasan analisis (jangan mengarang angka stat di luar data yang diberikan).
4. Berikan saran yang spesifik, praktis, dan langsung bisa diterapkan di dalam game.

=== DATA PENGUJIAN TIM SAAT INI ===
{$benchmarkContext}
SYS;

        // Sanitasi riwayat percakapan agar selalu bergantian user <-> model secara valid
        $contents = [];
        $recentHistory = array_slice($history, -10);

        foreach ($recentHistory as $turn) {
            $role = ($turn['role'] ?? '') === 'user' ? 'user' : 'model';
            $text = trim((string)($turn['content'] ?? $turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            // Pastikan pesan pertama selalu 'user'
            if (empty($contents) && $role !== 'user') {
                continue;
            }

            // Jika role sama dengan pesan sebelumnya, gabungkan teksnya
            $lastIdx = count($contents) - 1;
            if ($lastIdx >= 0 && $contents[$lastIdx]['role'] === $role) {
                $contents[$lastIdx]['parts'][0]['text'] .= "\n\n" . $text;
            } else {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        // Tambahkan pesan terbaru dari user
        $lastIdx = count($contents) - 1;
        if ($lastIdx >= 0 && $contents[$lastIdx]['role'] === 'user') {
            $contents[$lastIdx]['parts'][0]['text'] .= "\n\n" . trim($userMessage);
        } else {
            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => trim($userMessage)]],
            ];
        }

        // Tentukan urutan model berdasarkan pilihan user ('flash' atau 'pro') beserta fallback aman
        $defaultFlash = $this->model ?: 'gemini-2.5-flash';
        if ($modelChoice === 'pro') {
            $candidateModels = ['gemini-2.5-pro', 'gemini-1.5-pro', $defaultFlash];
        } else {
            $candidateModels = array_values(array_unique([$defaultFlash, 'gemini-2.5-flash', 'gemini-2.0-flash']));
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 4096,
            ],
        ];

        $lastError = null;
        $usedModel = $candidateModels[0];
        $fallbackUsed = false;

        foreach ($candidateModels as $idx => $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($url, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    $parts = $result['candidates'][0]['content']['parts'] ?? [];
                    $replyParts = [];

                    foreach ($parts as $part) {
                        if (!empty($part['thought'])) {
                            continue;
                        }
                        if (isset($part['text'])) {
                            $replyParts[] = $part['text'];
                        }
                    }

                    $replyText = trim(implode("\n", $replyParts));
                    if ($replyText !== '') {
                        $usedModel = $modelName;
                        $fallbackUsed = ($idx > 0 && $modelChoice === 'pro' && str_contains($modelName, 'flash'));

                        return [
                            'reply'         => $replyText,
                            'model_used'    => $usedModel,
                            'model_choice'  => $modelChoice,
                            'fallback_used' => $fallbackUsed,
                        ];
                    }
                }

                $status = $response->status();
                $errMsg = $response->json('error.message') ?? "HTTP {$status}";
                $lastError = $errMsg;
                Log::warning("Gemini Chat percobaan model {$modelName} gagal ({$status}): {$errMsg}");

                // Jika error karena API key invalid (400/403 API_KEY_INVALID), hentikan loop
                if ($status === 400 && str_contains(strtolower($errMsg), 'api key')) {
                    break;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("Gemini Chat exception pada model {$modelName}: " . $e->getMessage());
            }
        }

        throw new \Exception('Gagal mendapatkan respons dari Gemini AI: ' . ($lastError ?? 'Tidak ada respons dari server.'));
    }

    /**
     * Hitung selisih metrik matematika & perubahan stat karakter antara 2 pengujian (Uji A vs Uji B)
     */
    public function calculateComparisonMetrics(PartyBenchmark $benchA, PartyBenchmark $benchB): array
    {
        $dpsA = (int)$benchA->dps;
        $dpsB = (int)$benchB->dps;
        $dpsDiff = $dpsB - $dpsA;
        $dpsPct = $dpsA > 0 ? round(($dpsDiff / $dpsA) * 100, 1) : 0.0;

        $dmgA = (int)$benchA->total_damage;
        $dmgB = (int)$benchB->total_damage;
        $dmgDiff = $dmgB - $dmgA;
        $dmgPct = $dmgA > 0 ? round(($dmgDiff / $dmgA) * 100, 1) : 0.0;

        $maxA = (int)$benchA->max_hit;
        $maxB = (int)$benchB->max_hit;
        $maxDiff = $maxB - $maxA;
        $maxPct = $maxA > 0 ? round(($maxDiff / $maxA) * 100, 1) : 0.0;

        $timeA = (float)$benchA->total_time;
        $timeB = (float)$benchB->total_time;
        $timeDiff = round($timeB - $timeA, 2);

        $rotA = $benchA->rotations ?? [];
        $rotB = $benchB->rotations ?? [];
        $avgDurA = count($rotA) > 0 ? round(array_sum(array_column($rotA, 'time')) / count($rotA), 2) : 0.0;
        $avgDurB = count($rotB) > 0 ? round(array_sum(array_column($rotB, 'time')) / count($rotB), 2) : 0.0;
        $avgDurDiff = round($avgDurB - $avgDurA, 2);

        // Pemetaan stat & kontribusi damage per karakter
        $statsMapA = [];
        foreach ($benchA->character_stats ?? [] as $st) {
            $key = strtolower(trim($st['name'] ?? ''));
            if ($key !== '') {
                $statsMapA[$key] = $st;
            }
        }

        $statsMapB = [];
        foreach ($benchB->character_stats ?? [] as $st) {
            $key = strtolower(trim($st['name'] ?? ''));
            if ($key !== '') {
                $statsMapB[$key] = $st;
            }
        }

        $dmgMapA = [];
        foreach ($benchA->character_dmg_distribution ?? [] as $cd) {
            $key = strtolower(trim($cd['name'] ?? ''));
            if ($key !== '') {
                $dmgMapA[$key] = $cd;
            }
        }

        $dmgMapB = [];
        foreach ($benchB->character_dmg_distribution ?? [] as $cd) {
            $key = strtolower(trim($cd['name'] ?? ''));
            if ($key !== '') {
                $dmgMapB[$key] = $cd;
            }
        }

        $allKeys = array_values(array_unique(array_merge(
            array_keys($statsMapA),
            array_keys($statsMapB),
            array_keys($dmgMapA),
            array_keys($dmgMapB)
        )));

        $characterComparisons = [];
        foreach ($allKeys as $key) {
            $sA = $statsMapA[$key] ?? null;
            $sB = $statsMapB[$key] ?? null;
            $dA = $dmgMapA[$key] ?? null;
            $dB = $dmgMapB[$key] ?? null;

            $displayName = $sB['name'] ?? $sA['name'] ?? $dB['name'] ?? $dA['name'] ?? ucfirst($key);
            $element = $sB['element'] ?? $sA['element'] ?? $dB['element'] ?? $dA['element'] ?? 'Multi';

            $atkA = (int)($sA['total_atk'] ?? 0);
            $atkB = (int)($sB['total_atk'] ?? 0);
            $crA  = (float)($sA['crit_rate'] ?? 0);
            $crB  = (float)($sB['crit_rate'] ?? 0);
            $cdA  = (float)($sA['crit_dmg'] ?? 0);
            $cdB  = (float)($sB['crit_dmg'] ?? 0);
            $erA  = (float)($sA['energy_recharge'] ?? 0);
            $erB  = (float)($sB['energy_recharge'] ?? 0);
            $emA  = (int)($sA['elemental_mastery'] ?? 0);
            $emB  = (int)($sB['elemental_mastery'] ?? 0);
            $hpA  = (int)($sA['hp'] ?? 0);
            $hpB  = (int)($sB['hp'] ?? 0);

            $pctA = (int)($dA['percentage'] ?? 0);
            $pctB = (int)($dB['percentage'] ?? 0);
            $charDmgA = (int)($dA['damage'] ?? 0);
            $charDmgB = (int)($dB['damage'] ?? 0);

            $characterComparisons[] = [
                'name'      => $displayName,
                'element'   => $element,
                'hp_a'      => $hpA,
                'hp_b'      => $hpB,
                'hp_diff'   => $hpB - $hpA,
                'atk_a'     => $atkA,
                'atk_b'     => $atkB,
                'atk_diff'  => $atkB - $atkA,
                'cr_a'      => $crA,
                'cr_b'      => $crB,
                'cr_diff'   => round($crB - $crA, 1),
                'cd_a'      => $cdA,
                'cd_b'      => $cdB,
                'cd_diff'   => round($cdB - $cdA, 1),
                'er_a'      => $erA,
                'er_b'      => $erB,
                'er_diff'   => round($erB - $erA, 1),
                'em_a'      => $emA,
                'em_b'      => $emB,
                'em_diff'   => $emB - $emA,
                'dmg_pct_a' => $pctA,
                'dmg_pct_b' => $pctB,
                'dmg_pct_diff' => $pctB - $pctA,
                'dmg_a'     => $charDmgA,
                'dmg_b'     => $charDmgB,
                'dmg_diff'  => $charDmgB - $charDmgA,
            ];
        }

        return [
            'dps_a'           => $dpsA,
            'dps_b'           => $dpsB,
            'dps_diff'        => $dpsDiff,
            'dps_pct'         => $dpsPct,
            'total_damage_a'  => $dmgA,
            'total_damage_b'  => $dmgB,
            'damage_diff'     => $dmgDiff,
            'damage_pct'      => $dmgPct,
            'max_hit_a'       => $maxA,
            'max_hit_b'       => $maxB,
            'max_hit_diff'    => $maxDiff,
            'max_hit_pct'     => $maxPct,
            'total_time_a'    => $timeA,
            'total_time_b'    => $timeB,
            'time_diff'       => $timeDiff,
            'avg_rot_dur_a'   => $avgDurA,
            'avg_rot_dur_b'   => $avgDurB,
            'avg_rot_diff'    => $avgDurDiff,
            'rotations_cnt_a' => count($rotA),
            'rotations_cnt_b' => count($rotB),
            'characters'      => $characterComparisons,
        ];
    }

    /**
     * Analisis Komparasi Mendalam 2 Pengujian Benchmark menggunakan Gemini AI
     */
    public function compareBenchmarksWithAi(
        PartyBenchmark $benchA,
        PartyBenchmark $benchB,
        string $modelChoice = 'flash'
    ): array {
        if (!$this->apiKey) {
            throw new \Exception('GEMINI_API_KEY belum dikonfigurasi di file .env sistem.');
        }

        $metrics = $this->calculateComparisonMetrics($benchA, $benchB);
        $contextA = $this->generateGeminiPrompt($benchA);
        $contextB = $this->generateGeminiPrompt($benchB);

        $dpsSign = $metrics['dps_diff'] >= 0 ? '+' : '';
        $rotSign = $metrics['avg_rot_diff'] >= 0 ? '+' : '';

        $prompt = <<<PROMPT
Halo Gemini! Bertindaklah sebagai **Theorycrafter & Combat Analyst Genshin Impact Profesional**.
Saya ingin membandingkan **2 Hasil Pengujian Benchmark (A/B Testing)** dari party saya untuk mengetahui mengapa performa keduanya berbeda dan build/rotasi mana yang paling optimal.

### RINGKASAN DELTA MATEMATIKA (UJI A ➔ UJI B):
- **Perubahan Rata-rata DPS**: {$metrics['dps_a']} ➔ {$metrics['dps_b']} ({$dpsSign}{$metrics['dps_diff']} DPS / {$dpsSign}{$metrics['dps_pct']}%)
- **Perubahan Hit Terkuat (Nuke)**: {$metrics['max_hit_a']} ➔ {$metrics['max_hit_b']} ({$metrics['max_hit_pct']}%)
- **Perubahan Rata-rata Durasi Rotasi**: {$metrics['avg_rot_dur_a']} detik ➔ {$metrics['avg_rot_dur_b']} detik ({$rotSign}{$metrics['avg_rot_diff']} detik)
- **Total Waktu Pengujian**: {$metrics['total_time_a']} detik ➔ {$metrics['total_time_b']} detik

=== DATA LENGKAP UJI A (ACUAN / SEBELUMNYA) ===
{$contextA}

=== DATA LENGKAP UJI B (PEMBANDING / TERBARU) ===
{$contextB}

### INSTRUKSI LAPORAN KOMPARASI:
Berikan analisis komparasi yang tajam, terstruktur dalam **Bahasa Indonesia** menggunakan Markdown dengan susunan berikut:
1. **🏆 Ringkasan Utama & Pemenang Pengujian**: Sebutkan pengujian mana yang lebih unggul secara keseluruhan dan seberapa signifikan peningkatan/penurunan performanya.
2. **🧬 Analisis Perubahan Stat & Build Karakter**: Bedah perubahan stat antar karakter (ATK, HP, CRIT Rate, CRIT DMG, ER%, EM) dari Uji A ke Uji B dan jelaskan bagaimana perubahan stat tersebut mempengaruhi kontribusi damage.
3. **🔄 Evaluasi Kecepatan & Konsistensi Rotasi**: Bandingkan durasi dan kestabilan rotasi antara Uji A dan Uji B. Jelaskan apakah masalah Energy Recharge (ER%) membaik atau memburuk.
4. **💡 Kesimpulan & Rekomendasi Optimasi Lanjutan**: Setup mana yang wajib dipakai untuk Spiral Abyss Lantai 12, serta saran penyempurnaan berikutnya.
PROMPT;

        $defaultFlash = $this->model ?: 'gemini-2.5-flash';
        if ($modelChoice === 'pro') {
            $candidateModels = ['gemini-2.5-pro', 'gemini-1.5-pro', $defaultFlash];
        } else {
            $candidateModels = array_values(array_unique([$defaultFlash, 'gemini-2.5-flash', 'gemini-2.0-flash']));
        }

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.35,
                'maxOutputTokens' => 4096,
            ],
        ];

        $lastError = null;
        foreach ($candidateModels as $idx => $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$this->apiKey}";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($url, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    $parts = $result['candidates'][0]['content']['parts'] ?? [];
                    $replyParts = [];

                    foreach ($parts as $part) {
                        if (!empty($part['thought'])) {
                            continue;
                        }
                        if (isset($part['text'])) {
                            $replyParts[] = $part['text'];
                        }
                    }

                    $replyText = trim(implode("\n", $replyParts));
                    if ($replyText !== '') {
                        return [
                            'analysis'      => $replyText,
                            'metrics'       => $metrics,
                            'model_used'    => $modelName,
                            'model_choice'  => $modelChoice,
                            'fallback_used' => ($idx > 0 && $modelChoice === 'pro' && str_contains($modelName, 'flash')),
                        ];
                    }
                }

                $status = $response->status();
                $errMsg = $response->json('error.message') ?? "HTTP {$status}";
                $lastError = $errMsg;
                Log::warning("Gemini Compare percobaan model {$modelName} gagal ({$status}): {$errMsg}");

                if ($status === 400 && str_contains(strtolower($errMsg), 'api key')) {
                    break;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning("Gemini Compare exception pada model {$modelName}: " . $e->getMessage());
            }
        }

        throw new \Exception('Gagal menghasilkan analisis komparasi dari Gemini AI: ' . ($lastError ?? 'Tidak ada respons dari server.'));
    }
}
