<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\taskController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\familyController;
use App\Http\Controllers\HoyoLabController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\InventoryCharacterController;
use App\Http\Controllers\ArtifactScoringController;
use App\Http\Controllers\GoodFormatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ─── Public: Game Portal & Hub ────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome', ['title' => 'Game Hub & Portal']);
})->name('welcome');

Route::get('select-game/{game}', function (\Illuminate\Http\Request $request, $game) {
    if ($game === 'genshin_impact' || $game === 'genshin') {
        session(['active_game' => 'genshin_impact']);
        return redirect()->route('inventory.dashboard');
    }
    session(['active_game' => $game]);
    return redirect()->route('welcome')->with('info', 'Game ini sedang dalam tahap perancangan.');
})->name('select-game');

Route::get('reset-game', function () {
    session()->forget('active_game');
    return redirect()->route('welcome');
})->name('reset-game');

// ─── Authentication: Guest Routes (Login & Register) ──────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
});

// Logout (Authenticated Only)
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Master Database (Katalog Publik) ─────────────────────────────────────────
Route::post('family/sync-all', [familyController::class, 'syncAll'])->name('family.sync-all');
Route::resource('family', familyController::class);
Route::post('material/sync-all', [materialController::class, 'syncAll'])->name('material.sync-all');
Route::resource('material', materialController::class);
Route::post('character/sync-all', [CharacterController::class, 'syncAll'])->name('character.sync-all');
Route::resource('character', CharacterController::class);
Route::get('api/characters', [CharacterController::class, 'apiList'])->name('character.api');
Route::post('weapon/sync-all', [\App\Http\Controllers\WeaponController::class, 'syncAll'])->name('weapon.sync-all');
Route::resource('weapon', \App\Http\Controllers\WeaponController::class);
Route::get('api/weapons', [\App\Http\Controllers\WeaponController::class, 'apiList'])->name('weapon.api');
Route::post('artifact/sync-all', [\App\Http\Controllers\ArtifactSetController::class, 'syncAll'])->name('artifact.sync-all');
Route::resource('artifact', \App\Http\Controllers\ArtifactSetController::class);
Route::get('api/artifacts', [\App\Http\Controllers\ArtifactSetController::class, 'apiList'])->name('artifact.api');
Route::post('enemy/sync-all', [\App\Http\Controllers\EnemyController::class, 'syncAll'])->name('enemy.sync-all');
Route::resource('enemy', \App\Http\Controllers\EnemyController::class);
Route::get('api/enemies', [\App\Http\Controllers\EnemyController::class, 'apiList'])->name('enemy.api');

// ─── Authenticated Routes (Data Kepemilikan User) ──────────────────────────────
Route::middleware('auth')->group(function () {

    // ─── Game Accounts ────────────────────────────────────────────────────────
    Route::resource('game-accounts', GameAccountController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // ─── Inventory: Overview, Characters, Weapons, Artifacts & Materials ─────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\InventoryDashboardController::class, 'index'])->name('dashboard');
        Route::get('compare', [\App\Http\Controllers\AccountCompareController::class, 'index'])->name('compare');
        Route::post('sync-all', [\App\Http\Controllers\InventoryDashboardController::class, 'syncAll'])->name('sync-all');

        // GOOD & Gemini Format
        Route::prefix('good')->name('good.')->group(function () {
            Route::get('/', [GoodFormatController::class, 'index'])->name('index');
            Route::post('export', [GoodFormatController::class, 'export'])->name('export');
            Route::post('export-json', [GoodFormatController::class, 'exportJson'])->name('export-json');
            Route::post('import', [GoodFormatController::class, 'import'])->name('import');
            Route::post('gemini-export-json', [GoodFormatController::class, 'exportGeminiJson'])->name('gemini-export-json');
            Route::post('gemini-export-markdown', [GoodFormatController::class, 'exportGeminiMarkdown'])->name('gemini-export-markdown');
            Route::post('gemini-preview', [GoodFormatController::class, 'previewGemini'])->name('gemini-preview');
        });

        // Characters
        Route::get('characters', [InventoryCharacterController::class, 'index'])->name('characters.index');
        Route::post('characters', [InventoryCharacterController::class, 'store'])->name('characters.store');
        Route::post('characters/sync-hoyolab', [InventoryCharacterController::class, 'syncFromHoyoLab'])->name('characters.sync-hoyolab');
        Route::post('characters/sync-enka', [InventoryCharacterController::class, 'syncFromEnka'])->name('characters.sync-enka');
        Route::put('characters/{inventoryCharacter}', [InventoryCharacterController::class, 'update'])->name('characters.update');
        Route::delete('characters/{inventoryCharacter}', [InventoryCharacterController::class, 'destroy'])->name('characters.destroy');

        // Weapons
        Route::get('weapons', [\App\Http\Controllers\InventoryWeaponController::class, 'index'])->name('weapons.index');
        Route::post('weapons', [\App\Http\Controllers\InventoryWeaponController::class, 'store'])->name('weapons.store');
        Route::post('weapons/sync-hoyolab', [\App\Http\Controllers\InventoryWeaponController::class, 'syncFromHoyoLab'])->name('weapons.sync-hoyolab');
        Route::post('weapons/sync-enka', [\App\Http\Controllers\InventoryWeaponController::class, 'syncFromEnka'])->name('weapons.sync-enka');
        Route::put('weapons/{inventoryWeapon}', [\App\Http\Controllers\InventoryWeaponController::class, 'update'])->name('weapons.update');
        Route::delete('weapons/{inventoryWeapon}', [\App\Http\Controllers\InventoryWeaponController::class, 'destroy'])->name('weapons.destroy');

        // Artifacts
        Route::get('artifacts', [\App\Http\Controllers\InventoryArtifactController::class, 'index'])->name('artifacts.index');
        Route::post('artifacts', [\App\Http\Controllers\InventoryArtifactController::class, 'store'])->name('artifacts.store');
        Route::post('artifacts/sync-hoyolab', [\App\Http\Controllers\InventoryArtifactController::class, 'syncFromHoyoLab'])->name('artifacts.sync-hoyolab');
        Route::post('artifacts/sync-enka', [\App\Http\Controllers\InventoryArtifactController::class, 'syncFromEnka'])->name('artifacts.sync-enka');
        Route::put('artifacts/{inventoryArtifact}', [\App\Http\Controllers\InventoryArtifactController::class, 'update'])->name('artifacts.update');
        Route::delete('artifacts/{inventoryArtifact}', [\App\Http\Controllers\InventoryArtifactController::class, 'destroy'])->name('artifacts.destroy');

        // Materials
        Route::get('materials', [\App\Http\Controllers\InventoryMaterialController::class, 'index'])->name('materials.index');
        Route::post('materials/update-amount', [\App\Http\Controllers\InventoryMaterialController::class, 'updateAmount'])->name('materials.updateAmount');
        Route::post('materials/quick-adjust', [\App\Http\Controllers\InventoryMaterialController::class, 'quickAdjust'])->name('materials.quickAdjust');
    });

    // ─── Task Tracker & Kalkulator ───────────────────────────────────────────
    Route::get('task/character-talent-materials/{characterId}', [taskController::class, 'getCharacterTalentMaterials'])->name('task.talent-materials');
    Route::get('task/talent-presets/{characterId}', [taskController::class, 'getTalentPresets'])->name('task.talent-presets');
    Route::get('task/toggle-status/{id}', [taskController::class, 'toggleStatus'])->name('task.toggle-status');
    Route::post('task/toggle-subtask/{id}', [taskController::class, 'toggleSubTask'])->name('task.toggle-subtask');
    Route::resource('task', taskController::class);
    Route::post('craftingBuild', [taskController::class, 'craftingBuild'])->name('craftingBuild');
    Route::post('editMaterial', [taskController::class, 'editMaterial'])->name('editMaterial');
    Route::get('taskComplete/{id}', [taskController::class, 'upgradeTaskComplete']);

    // ─── Artifact Scoring ─────────────────────────────────────────────────────
    Route::prefix('artifact-scoring')->name('artifact-scoring.')->group(function () {
        Route::get('/', [ArtifactScoringController::class, 'index'])->name('index');
        Route::post('/score-one/{artifact}', [ArtifactScoringController::class, 'scoreOne'])->name('score-one');
        Route::post('/score-all', [ArtifactScoringController::class, 'scoreAll'])->name('score-all');
        Route::post('/update-substats/{artifact}', [ArtifactScoringController::class, 'updateSubStats'])->name('update-substats');
        Route::post('/generate-mock-substats', [ArtifactScoringController::class, 'generateMockSubStats'])->name('generate-mock-substats');
        Route::post('/sync-enka', [ArtifactScoringController::class, 'syncEnka'])->name('sync-enka');
        Route::get('/rules', [ArtifactScoringController::class, 'rulesIndex'])->name('rules');
        Route::get('/rules/{character}', [ArtifactScoringController::class, 'showRule'])->name('rule');
        Route::post('/rules/{character}', [ArtifactScoringController::class, 'saveRule'])->name('rule.save');
        Route::get('/preset', [ArtifactScoringController::class, 'loadPreset'])->name('preset');
    });

    // ─── Calculator ───────────────────────────────────────────────────────────
    Route::prefix('calculator')->name('calculator.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CalculatorController::class, 'index'])->name('index');
        Route::post('/calculate', [\App\Http\Controllers\CalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/create-task', [\App\Http\Controllers\CalculatorController::class, 'createTask'])->name('createTask');
    });

    // ─── HoYoLAB Microservice ─────────────────────────────────────────────────
    Route::prefix('hoyolab')->name('hoyolab.')->group(function () {
        Route::post('import-characters', [HoyoLabController::class, 'importCharactersAsTasks'])->name('import-characters');
        Route::get('ping', [HoyoLabController::class, 'pingMicroservice'])->name('ping');
    });

    // ─── Party Analyzer ───────────────────────────────────────────────────────
    Route::prefix('party')->name('party.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PartyAnalyzerController::class, 'index'])->name('index');
        Route::post('/analyze-ajax', [\App\Http\Controllers\PartyAnalyzerController::class, 'analyzeAjax'])->name('analyze-ajax');
        Route::post('/auto-generate', [\App\Http\Controllers\PartyAnalyzerController::class, 'autoGenerateAjax'])->name('auto-generate');
        Route::post('/save', [\App\Http\Controllers\PartyAnalyzerController::class, 'saveParty'])->name('save');
        Route::delete('/saved/{savedParty}', [\App\Http\Controllers\PartyAnalyzerController::class, 'deleteParty'])->name('delete-saved');
    });

    // ─── Daily Resin & Auto Check-in ──────────────────────────────────────────
    Route::prefix('daily-resin')->name('daily-resin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DailyResinController::class, 'index'])->name('index');
        Route::get('/data/{gameAccount}', [\App\Http\Controllers\DailyResinController::class, 'ajaxData'])->name('data');
        Route::post('/checkin/{gameAccount}', [\App\Http\Controllers\DailyResinController::class, 'claimCheckin'])->name('checkin');
        Route::post('/settings/{gameAccount}', [\App\Http\Controllers\DailyResinController::class, 'updateSettings'])->name('settings');
        Route::post('/alerts/{gameAccount}/mark-read', [\App\Http\Controllers\DailyResinController::class, 'markAlertsAsRead'])->name('alerts.mark-read');
        Route::post('/alerts/{gameAccount}/test', [\App\Http\Controllers\DailyResinController::class, 'testResinAlert'])->name('alerts.test');
    });

    // ─── Planners ─────────────────────────────────────────────────────────────
    Route::get('farming-planner', [\App\Http\Controllers\FarmingPlannerController::class, 'index'])
        ->name('farming-planner.index');
    Route::get('build-planner', [\App\Http\Controllers\BuildPlannerController::class, 'index'])
        ->name('build-planner.index');

});