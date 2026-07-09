<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\taskController;
use App\Http\Controllers\materialController;
use App\Http\Controllers\familyController;
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