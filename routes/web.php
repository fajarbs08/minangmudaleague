<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LineupListController;
use App\Http\Controllers\MatchScheduleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\SearchController;

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

require __DIR__ . '/auth.php';

Route::get('hasil-scan/pemain/{player}', [PlayerController::class, 'scanResult'])->name('players.scan-result');
Route::get('hasil-scan/official/{official}', [OfficialController::class, 'scanResult'])->name('officials.scan-result');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [DashboardController::class, 'index'])->name('root');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.home');
    Route::get('dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('dashboard/club-workflow-pdf', [DashboardController::class, 'workflowPdf'])
        ->middleware('role:club')
        ->name('dashboard.workflow-pdf');
    Route::get('dashboard/admin-manual-pdf', [DashboardController::class, 'adminManualPdf'])
        ->middleware('role:admin')
        ->name('dashboard.admin-manual-pdf');
    Route::get('dashboard/club-manual-pdf', [DashboardController::class, 'clubManualPdf'])
        ->middleware('role:club')
        ->name('dashboard.club-manual-pdf');

    Route::middleware('role:admin,club')->group(function () {
        Route::get('clubs', [ClubController::class, 'index'])->name('clubs.index');
        Route::get('clubs/create', [ClubController::class, 'create'])->name('clubs.create');
        Route::get('clubs/statement-template', [ClubController::class, 'statementTemplate'])->name('clubs.statement-template');
        Route::post('clubs', [ClubController::class, 'store'])->name('clubs.store');
        Route::get('clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');
        Route::get('clubs/{club}/edit', [ClubController::class, 'edit'])->name('clubs.edit');
        Route::put('clubs/{club}', [ClubController::class, 'update'])->name('clubs.update');
        Route::post('clubs/{club}/submit', [ClubController::class, 'submit'])->name('clubs.submit');
        Route::post('clubs/{club}/review', [ClubController::class, 'review'])->middleware('role:admin')->name('clubs.review');
        Route::post('clubs/bulk-review', [ClubController::class, 'bulkReview'])->middleware('role:admin')->name('clubs.bulk-review');

        Route::resource('officials', OfficialController::class)->except('show');
        Route::resource('players', PlayerController::class)->except('show');
        Route::resource('lineup-lists', LineupListController::class);
        Route::get('players/id-cards/{ageGroup}', [PlayerController::class, 'idCards'])->name('players.id-cards');
        Route::get('players/id-cards/{ageGroup}/pdf', [PlayerController::class, 'exportIdCards'])->name('players.id-cards.export');
        Route::get('officials/id-cards/{ageGroup}', [OfficialController::class, 'idCards'])->name('officials.id-cards');
        Route::get('officials/id-cards/{ageGroup}/pdf', [OfficialController::class, 'exportIdCards'])->name('officials.id-cards.export');
        Route::get('officials/{official}', [OfficialController::class, 'show'])->name('officials.show');
        Route::get('officials/{official}/id-card/{ageGroup}', [OfficialController::class, 'idCard'])->name('officials.id-card');
        Route::get('officials/{official}/id-card/{ageGroup}/pdf', [OfficialController::class, 'exportIdCard'])->name('officials.id-card.export');
        Route::delete('officials/{official}/age-registrations/{ageGroup}', [OfficialController::class, 'destroyAgeRegistration'])->name('officials.age-registrations.destroy');
        Route::get('players/{player}', [PlayerController::class, 'show'])->name('players.show');
        Route::get('players/{player}/id-card/{ageGroup}', [PlayerController::class, 'idCard'])->name('players.id-card');
        Route::get('players/{player}/id-card/{ageGroup}/pdf', [PlayerController::class, 'exportIdCard'])->name('players.id-card.export');
        Route::patch('players/{player}/age-registrations/{ageGroup}', [PlayerController::class, 'updateAgeRegistration'])->name('players.age-registrations.update');
        Route::delete('players/{player}/age-registrations/{ageGroup}', [PlayerController::class, 'destroyAgeRegistration'])->name('players.age-registrations.destroy');
        Route::post('officials/{official}/submit', [OfficialController::class, 'submit'])->name('officials.submit');
        Route::post('officials/{official}/review', [OfficialController::class, 'review'])->middleware('role:admin')->name('officials.review');
        Route::post('officials/bulk-review', [OfficialController::class, 'bulkReview'])->middleware('role:admin')->name('officials.bulk-review');
        Route::post('players/{player}/submit', [PlayerController::class, 'submit'])->name('players.submit');
        Route::post('players/{player}/review', [PlayerController::class, 'review'])->middleware('role:admin')->name('players.review');
        Route::post('players/bulk-review', [PlayerController::class, 'bulkReview'])->middleware('role:admin')->name('players.bulk-review');
        Route::post('lineup-lists/{lineup_list}/submit', [LineupListController::class, 'submit'])->name('lineup-lists.submit');
        Route::post('lineup-lists/{lineup_list}/review', [LineupListController::class, 'review'])->middleware('role:admin')->name('lineup-lists.review');
        Route::post('lineup-lists/bulk-review', [LineupListController::class, 'bulkReview'])->middleware('role:admin')->name('lineup-lists.bulk-review');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('matches', MatchScheduleController::class)->except('show');
        Route::get('admin-accounts', [AdminAccountController::class, 'index'])->name('admin-accounts.index');
        Route::post('admin-accounts', [AdminAccountController::class, 'store'])->name('admin-accounts.store');
        Route::get('admin-accounts/{adminAccount}/edit', [AdminAccountController::class, 'edit'])->name('admin-accounts.edit');
        Route::put('admin-accounts/{adminAccount}', [AdminAccountController::class, 'update'])->name('admin-accounts.update');
        Route::delete('admin-accounts/{adminAccount}', [AdminAccountController::class, 'destroy'])->name('admin-accounts.destroy');
        Route::get('club-accounts/create', [SettingsController::class, 'createClubAccount'])->name('club-accounts.create');
        Route::post('club-accounts', [SettingsController::class, 'storeClubAccount'])->name('club-accounts.store');
        Route::get('club-accounts/{clubAccount}/edit', [SettingsController::class, 'editClubAccount'])->name('club-accounts.edit');
        Route::put('club-accounts/{clubAccount}', [SettingsController::class, 'updateClubAccount'])->name('club-accounts.update');
        Route::delete('club-accounts/{clubAccount}', [SettingsController::class, 'destroyClubAccount'])->name('club-accounts.destroy');
        Route::delete('clubs/{club}', [ClubController::class, 'destroy'])->name('clubs.destroy');

    });
});
