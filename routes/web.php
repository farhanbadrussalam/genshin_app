<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\taskController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\familyController;
use App\Http\Controllers\HoyoLabController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\InventoryCharacterController;
use App\Http\Controllers\ArtifactScoringController;
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
Route::get('/', function(){
    return view('welcome', ['title' => 'Menu']);
});

Route::resource('family', familyController::class);
Route::resource('material', materialController::class);
Route::resource('task', taskController::class);
Route::post('character/sync-all', [CharacterController::class, 'syncAll'])->name('character.sync-all');
Route::resource('character', CharacterController::class);
Route::get('api/characters', [CharacterController::class, 'apiList'])->name('character.api');
Route::post('weapon/sync-all', [\App\Http\Controllers\WeaponController::class, 'syncAll'])->name('weapon.sync-all');
Route::resource('weapon', \App\Http\Controllers\WeaponController::class);
Route::get('api/weapons', [\App\Http\Controllers\WeaponController::class, 'apiList'])->name('weapon.api');
Route::post('artifact/sync-all', [\App\Http\Controllers\ArtifactSetController::class, 'syncAll'])->name('artifact.sync-all');
Route::resource('artifact', \App\Http\Controllers\ArtifactSetController::class);
Route::get('api/artifacts', [\App\Http\Controllers\ArtifactSetController::class, 'apiList'])->name('artifact.api');

// ─── Inventory: Game Accounts ─────────────────────────────────────────────────
Route::resource('game-accounts', GameAccountController::class)
    ->only(['index', 'store', 'update', 'destroy']);

// ─── Inventory: Characters, Weapons & Artifacts ────────────────────────────────
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\InventoryDashboardController::class, 'index'])->name('dashboard');
    Route::post('sync-all', [\App\Http\Controllers\InventoryDashboardController::class, 'syncAll'])->name('sync-all');

    Route::get('characters', [InventoryCharacterController::class, 'index'])->name('characters.index');
    Route::post('characters', [InventoryCharacterController::class, 'store'])->name('characters.store');
    Route::post('characters/sync-hoyolab', [InventoryCharacterController::class, 'syncFromHoyoLab'])->name('characters.sync-hoyolab');
    Route::post('characters/sync-enka', [InventoryCharacterController::class, 'syncFromEnka'])->name('characters.sync-enka');
    Route::put('characters/{inventoryCharacter}', [InventoryCharacterController::class, 'update'])->name('characters.update');
    Route::delete('characters/{inventoryCharacter}', [InventoryCharacterController::class, 'destroy'])->name('characters.destroy');

    Route::get('weapons', [\App\Http\Controllers\InventoryWeaponController::class, 'index'])->name('weapons.index');
    Route::post('weapons', [\App\Http\Controllers\InventoryWeaponController::class, 'store'])->name('weapons.store');
    Route::post('weapons/sync-hoyolab', [\App\Http\Controllers\InventoryWeaponController::class, 'syncFromHoyoLab'])->name('weapons.sync-hoyolab');
    Route::post('weapons/sync-enka', [\App\Http\Controllers\InventoryWeaponController::class, 'syncFromEnka'])->name('weapons.sync-enka');
    Route::put('weapons/{inventoryWeapon}', [\App\Http\Controllers\InventoryWeaponController::class, 'update'])->name('weapons.update');
    Route::delete('weapons/{inventoryWeapon}', [\App\Http\Controllers\InventoryWeaponController::class, 'destroy'])->name('weapons.destroy');

    Route::get('artifacts', [\App\Http\Controllers\InventoryArtifactController::class, 'index'])->name('artifacts.index');
    Route::post('artifacts', [\App\Http\Controllers\InventoryArtifactController::class, 'store'])->name('artifacts.store');
    Route::post('artifacts/sync-hoyolab', [\App\Http\Controllers\InventoryArtifactController::class, 'syncFromHoyoLab'])->name('artifacts.sync-hoyolab');
    Route::post('artifacts/sync-enka', [\App\Http\Controllers\InventoryArtifactController::class, 'syncFromEnka'])->name('artifacts.sync-enka');
    Route::put('artifacts/{inventoryArtifact}', [\App\Http\Controllers\InventoryArtifactController::class, 'update'])->name('artifacts.update');
    Route::delete('artifacts/{inventoryArtifact}', [\App\Http\Controllers\InventoryArtifactController::class, 'destroy'])->name('artifacts.destroy');

    Route::get('materials', [\App\Http\Controllers\InventoryMaterialController::class, 'index'])->name('materials.index');
    Route::post('materials/update-amount', [\App\Http\Controllers\InventoryMaterialController::class, 'updateAmount'])->name('materials.updateAmount');
    Route::post('materials/quick-adjust', [\App\Http\Controllers\InventoryMaterialController::class, 'quickAdjust'])->name('materials.quickAdjust');
});

Route::post('craftingBuild', [taskController::class, 'craftingBuild'])->name('craftingBuild');
Route::post('editMaterial', [taskController::class, 'editMaterial'])->name('editMaterial');
Route::get('taskComplete/{id}', [taskController::class, 'upgradeTaskComplete']);

// ─── Artifact Scoring System ──────────────────────────────────────────────────
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

// ─── Kalkulator Upgrade & Task Integration ────────────────────────────────────
Route::prefix('calculator')->name('calculator.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CalculatorController::class, 'index'])->name('index');
    Route::post('/calculate', [\App\Http\Controllers\CalculatorController::class, 'calculate'])->name('calculate');
    Route::post('/create-task', [\App\Http\Controllers\CalculatorController::class, 'createTask'])->name('createTask');
});

// ─── HoYoLAB Microservice Integration ────────────────────────────────────────
Route::prefix('hoyolab')->name('hoyolab.')->group(function () {
    // Import karakter dari HoYoLAB → simpan sebagai draft Task
    Route::post('import-characters', [HoyoLabController::class, 'importCharactersAsTasks'])
        ->name('import-characters');

    // Cek apakah microservice Python aktif
    Route::get('ping', [HoyoLabController::class, 'pingMicroservice'])
        ->name('ping');
});