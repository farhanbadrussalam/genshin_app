<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\taskController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\familyController;
use App\Http\Controllers\HoyoLabController;
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

Route::post('craftingBuild', [taskController::class, 'craftingBuild'])->name('craftingBuild');
Route::post('editMaterial', [taskController::class, 'editMaterial'])->name('editMaterial');
Route::get('taskComplete/{id}', [taskController::class, 'upgradeTaskComplete']);

// ─── HoYoLAB Microservice Integration ────────────────────────────────────────
Route::prefix('hoyolab')->name('hoyolab.')->group(function () {
    // Import karakter dari HoYoLAB → simpan sebagai draft Task
    Route::post('import-characters', [HoyoLabController::class, 'importCharactersAsTasks'])
        ->name('import-characters');

    // Cek apakah microservice Python aktif
    Route::get('ping', [HoyoLabController::class, 'pingMicroservice'])
        ->name('ping');
});