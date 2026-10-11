<?php

namespace App\Http\Controllers;

use App\Models\GameAccount;
use App\Models\InventoryMaterial;
use App\Models\task as Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmingPlannerController extends Controller
{
    /**
     * Ringkas semua kebutuhan task yang belum selesai menjadi rencana farming.
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')->orderBy('nickname')->get();
        $activeAccount = $accounts->firstWhere('id', (int) $request->input('account_id')) ?? $accounts->first();
        $inventoryByMaterial = $activeAccount
            ? InventoryMaterial::where('game_account_id', $activeAccount->id)->pluck('amount', 'material_id')
            : collect();
        $tasks = Task::with(['sub_task.material.family'])
            ->where('game_account_id', $activeAccount?->id)
            ->where('status', '!=', 'complete')
            ->orderBy('prioritas')
            ->get();
        $materials = [];

        foreach ($tasks as $task) {
            foreach ($task->sub_task as $subTask) {
                if ($subTask->is_completed) {
                    continue; // Skip sub-task yang sudah selesai / checklist
                }
                $material = $subTask->material;
                if (!$material) {
                    continue;
                }
                $materials[$material->id] ??= [
                    'material' => $material,
                    'required' => 0,
                    'tasks' => [],
                    'priority' => (int) $task->prioritas,
                ];
                $materials[$material->id]['required'] += (int) $subTask->amount;
                $materials[$material->id]['priority'] = min($materials[$material->id]['priority'], (int) $task->prioritas);
                $materials[$material->id]['tasks'][] = html_entity_decode($task->nama_task, ENT_QUOTES, 'UTF-8');
            }
        }

        $today = Carbon::now(config('app.timezone'))->locale('id');
        $todayLabels = $this->todayLabels($today);

        $plan = collect($materials)->map(function (array $item) use ($inventoryByMaterial, $todayLabels) {
            $material = $item['material'];
            $owned = $inventoryByMaterial->has($material->id)
                ? (int) $inventoryByMaterial->get($material->id)
                : (int) $material->amount;
            $missing = max(0, $item['required'] - $owned);
            $sourceInfo = $this->parseSource($material);
            $availInfo = $this->checkAvailability($material, $todayLabels);

            return [
                ...$item,
                'owned' => $owned,
                'missing' => $missing,
                'is_ready' => $missing === 0,
                'has_schedule' => $availInfo['has_schedule'],
                'is_available_today' => $availInfo['is_available_today'],
                'status_label' => $availInfo['status_label'],
                'badge_class' => $availInfo['badge_class'],
                'days_formatted' => $availInfo['days_formatted'],
                'domain' => $sourceInfo['domain_name'] ?: $sourceInfo['primary_source'],
                'group' => $sourceInfo['group'],
                'group_type' => $sourceInfo['type'],
                'primary_source' => $sourceInfo['primary_source'],
                'tasks' => array_values(array_unique($item['tasks'])),
            ];
        })->sortBy([
            ['is_ready', 'asc'],
            ['has_schedule', 'desc'],
            ['is_available_today', 'desc'],
            ['priority', 'asc'],
            ['missing', 'desc'],
        ])->values();

        // Rute Farming yang Disarankan: HANYA yang memiliki jadwal domain tertentu
        $domainPlan = $plan->filter(fn (array $item) => $item['missing'] > 0 && !empty($item['has_schedule']))
            ->groupBy('group')
            ->map(fn ($items, $groupName) => [
                'group' => $groupName,
                'type' => $items->first()['group_type'] ?? 'domain',
                'domain' => $items->first()['domain'] ?? $groupName,
                'primary_source' => $items->first()['primary_source'] ?? $groupName,
                'materials' => $items,
                'total_missing' => $items->sum('missing'),
                'available_today' => $items->contains('is_available_today', true),
            ])->sortByDesc('available_today')->values();

        return view('planner.farming.index', compact('accounts', 'activeAccount', 'tasks', 'plan', 'domainPlan', 'today') + [
            'title' => 'Farming Planner',
        ]);
    }

    private function todayLabels(Carbon $today): array
    {
        $indonesian = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        return [strtolower($indonesian[$today->dayOfWeekIso]), strtolower($today->englishDayOfWeek)];
    }

    private function parseDaysOfWeek($rawDays): array
    {
        if (is_array($rawDays)) return $rawDays;
        if (is_string($rawDays)) {
            $decoded = json_decode($rawDays, true);
            if (is_array($decoded)) return $decoded;
            if (trim($rawDays) !== '' && $rawDays !== '[]') return [trim($rawDays)];
        }
        return [];
    }

    private function parseSource($material): array
    {
        $domain = $material->dropdomain ? trim($material->dropdomain) : null;
        $rawSource = $material->source;
        $sources = [];
        if (is_array($rawSource)) {
            $sources = $rawSource;
        } elseif (is_string($rawSource)) {
            $decoded = json_decode($rawSource, true);
            if (is_array($decoded)) {
                $sources = $decoded;
            } elseif (trim($rawSource) !== '' && $rawSource !== '[]') {
                $sources = [trim($rawSource)];
            }
        }

        $cleanSources = array_values(array_filter($sources, function ($s) {
            return !str_contains($s, 'Placeholder') && !str_contains($s, 'Konversi di Crafting');
        }));

        $primarySource = $cleanSources[0] ?? ($domain ?: 'Sumber tidak tercatat');

        if ($domain) {
            $group = $domain;
            $type = 'domain';
        } elseif ($material->materialtype === 'Material Penguatan Karakter' || str_contains($primarySource, 'Tantangan')) {
            $group = 'Weekly Boss (Trounce Domain)';
            $type = 'weekly_boss';
        } elseif ($material->materialtype === 'Material Penguatan Senjata dan Karakter' || str_contains($primarySource, 'Dijatuhkan')) {
            $group = 'Drop Musuh (Open World)';
            $type = 'monster';
        } elseif (str_contains($material->materialtype ?? '', 'Produk Khas') || str_contains($primarySource, 'Ditemukan')) {
            $group = 'Produk Khas Daerah (Open World)';
            $type = 'specialty';
        } elseif ($material->materialtype === 'Material Ascension Karakter') {
            $group = 'Boss Dunia & Elemental Gem';
            $type = 'boss';
        } else {
            $group = 'Material Lainnya / Event';
            $type = 'other';
        }

        return [
            'domain_name' => $domain,
            'primary_source' => $primarySource,
            'group' => $group,
            'type' => $type,
        ];
    }

    private function checkAvailability($material, array $todayLabels): array
    {
        $days = $this->parseDaysOfWeek($material->daysofweek);
        $hasDomain = !empty($material->dropdomain);

        if (!$hasDomain) {
            if ($material->name === 'Crown of Insight') {
                return [
                    'has_schedule' => false,
                    'is_available_today' => false,
                    'status_label' => 'Reward Event',
                    'badge_class' => 'badge-event',
                    'days_formatted' => 'Reward Event / Pohon Penawaran',
                ];
            }
            return [
                'has_schedule' => false,
                'is_available_today' => true,
                'status_label' => 'Setiap Hari',
                'badge_class' => 'badge-open-today',
                'days_formatted' => 'Setiap Hari (Open World)',
            ];
        }

        $hasSchedule = count($days) > 0;
        $isOpen = false;
        $dayNameLower = $todayLabels[0];
        $dayNameEnLower = $todayLabels[1];

        if ($dayNameLower === 'minggu' || $dayNameEnLower === 'sunday') {
            $isOpen = true;
        } else {
            foreach ($days as $d) {
                $dl = strtolower(trim($d));
                if (str_contains($dl, $dayNameLower) || str_contains($dl, $dayNameEnLower)) {
                    $isOpen = true;
                    break;
                }
            }
        }

        $daysFormatted = count($days) > 0 ? implode(', ', $days) : 'Cek Jadwal';

        if ($isOpen) {
            return [
                'has_schedule' => $hasSchedule,
                'is_available_today' => true,
                'status_label' => 'Buka Hari Ini',
                'badge_class' => 'badge-open-today',
                'days_formatted' => $daysFormatted,
            ];
        } else {
            return [
                'has_schedule' => $hasSchedule,
                'is_available_today' => false,
                'status_label' => 'Tutup Hari Ini',
                'badge_class' => 'badge-closed-today',
                'days_formatted' => $daysFormatted,
            ];
        }
    }
}