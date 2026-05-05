<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LineupListController;
use App\Http\Controllers\MatchScheduleController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SponsorController;
use Illuminate\Support\Facades\Route;

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

require __DIR__.'/auth.php';

Route::get('robots.txt', [DashboardController::class, 'robots'])->name('public.robots');
Route::get('sitemap.xml', [DashboardController::class, 'sitemap'])->name('public.sitemap');
Route::get('pemain/{playerSlug}', [PlayerController::class, 'publicShow'])->name('public.players.show');
Route::get('verifikasi/pemain/{playerSlug}', [PlayerController::class, 'publicScanShow'])->name('public.players.scan');
Route::get('ofisial/{officialSlug}', [OfficialController::class, 'publicShow'])->name('public.officials.show');
Route::get('verifikasi/ofisial/{officialSlug}', [OfficialController::class, 'publicScanShow'])->name('public.officials.scan');
Route::get('', [DashboardController::class, 'publicHome'])->name('public.home');
Route::get('jadwal-pertandingan', [DashboardController::class, 'publicSchedule'])->name('public.schedule');
Route::get('jadwal-pertandingan/{matchSlug}', [DashboardController::class, 'publicScheduleShow'])->name('public.schedule.show');
Route::get('hasil-pertandingan', [DashboardController::class, 'publicResults'])->name('public.results');
Route::get('bagan-knockout', [DashboardController::class, 'publicBracketsPage'])->name('public.brackets');
Route::get('hasil-pertandingan/{matchSlug}', [DashboardController::class, 'publicResultShow'])->name('public.results.show');
Route::get('klasemen', [DashboardController::class, 'publicStandingsPage'])->name('public.standings');
Route::get('klub', [DashboardController::class, 'publicClubs'])->name('public.clubs');
Route::get('klub/{clubSlug}', [DashboardController::class, 'publicClubShow'])->name('public.clubs.show');
Route::get('informasi', [DashboardController::class, 'publicInformationRedirect'])->name('public.information');
Route::get('informasi/file/{informationResource}', [DashboardController::class, 'publicInformationFileRedirect']);
Route::get('informasi/file/{informationResource}/unduh', [DashboardController::class, 'publicInformationFileRedirect']);
Route::get('informasi/{resourceSlug}', [DashboardController::class, 'publicInformationShowRedirect'])->name('public.information.show');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('home', [DashboardController::class, 'index'])->name('root');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.home');
    Route::get('dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/pencarian/saran', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('dashboard/pencarian', [SearchController::class, 'index'])->name('search.index');
    Route::post('dashboard/season-context', [SeasonController::class, 'select'])->name('seasons.select');
    Route::middleware('role:admin,club')->group(function () {
        Route::get('dashboard/hasil-pertandingan', [MatchScheduleController::class, 'results'])->name('match-results.index');
        Route::get('dashboard/laporan/klasemen', [MatchScheduleController::class, 'standings'])->name('reports.standings');
        Route::get('dashboard/laporan/klasemen/pdf', [MatchScheduleController::class, 'standingsPdf'])->name('reports.standings.pdf');
        Route::get('dashboard/laporan/top-skor', [MatchScheduleController::class, 'topScorers'])->name('reports.top-scorers');
        Route::get('dashboard/laporan/top-skor/pdf', [MatchScheduleController::class, 'topScorersPdf'])->name('reports.top-scorers.pdf');
        Route::get('dashboard/laporan/top-assist', [MatchScheduleController::class, 'topAssists'])->name('reports.top-assists');
        Route::get('dashboard/laporan/top-assist/pdf', [MatchScheduleController::class, 'topAssistsPdf'])->name('reports.top-assists.pdf');
        Route::get('dashboard/laporan/bagan-knockout', [MatchScheduleController::class, 'brackets'])->name('reports.brackets');
        Route::get('dashboard/laporan/bagan-knockout/print', [MatchScheduleController::class, 'bracketsPrint'])->name('reports.brackets.print');
        Route::get('dashboard/laporan/rekap', [MatchScheduleController::class, 'reports'])->name('reports.overview');
        Route::get('dashboard/laporan/rekap/pdf', [MatchScheduleController::class, 'reportsPdf'])->name('reports.overview.pdf');
        Route::get('dashboard/klub', [ClubController::class, 'index'])->name('clubs.index');
        Route::get('dashboard/klub/create', [ClubController::class, 'create'])->name('clubs.create');
        Route::get('dashboard/klub/template-surat-pernyataan', [ClubController::class, 'statementTemplate'])->name('clubs.statement-template');
        Route::post('dashboard/klub', [ClubController::class, 'store'])->name('clubs.store');
        Route::get('dashboard/klub/{club}/dokumen/pernyataan', [ClubController::class, 'downloadStatement'])->name('clubs.statement.download');
        Route::get('dashboard/klub/{club}', [ClubController::class, 'show'])->name('clubs.show');
        Route::get('dashboard/klub/{club}/edit', [ClubController::class, 'edit'])->name('clubs.edit');
        Route::put('dashboard/klub/{club}', [ClubController::class, 'update'])->name('clubs.update');
        Route::post('dashboard/klub/{club}/submit', [ClubController::class, 'submit'])->name('clubs.submit');
        Route::post('dashboard/klub/{club}/review', [ClubController::class, 'review'])->middleware('role:admin')->name('clubs.review');
        Route::post('dashboard/klub/bulk-review', [ClubController::class, 'bulkReview'])->middleware('role:admin')->name('clubs.bulk-review');

        Route::resource('dashboard/ofisial', OfficialController::class)
            ->except('show')
            ->parameters(['ofisial' => 'official'])
            ->names('officials');
        Route::resource('dashboard/pemain', PlayerController::class)
            ->except('show')
            ->parameters(['pemain' => 'player'])
            ->names('players');
        Route::resource('dashboard/dsp', LineupListController::class)
            ->parameters(['dsp' => 'lineupList'])
            ->names('lineup-lists');
        Route::get('dashboard/pemain/kartu-identitas', [PlayerController::class, 'idCardsAll'])->name('players.id-cards.all');
        Route::get('dashboard/pemain/kartu-identitas/pdf', [PlayerController::class, 'exportIdCardsAll'])->name('players.id-cards.all.export');
        Route::get('dashboard/pemain/kartu-identitas/{ageGroup}', [PlayerController::class, 'idCards'])->name('players.id-cards');
        Route::get('dashboard/pemain/kartu-identitas/{ageGroup}/pdf', [PlayerController::class, 'exportIdCards'])->name('players.id-cards.export');
        Route::get('dashboard/ofisial/kartu-identitas', [OfficialController::class, 'idCardsAll'])->name('officials.id-cards.all');
        Route::get('dashboard/ofisial/kartu-identitas/pdf', [OfficialController::class, 'exportIdCardsAll'])->name('officials.id-cards.all.export');
        Route::get('dashboard/ofisial/kartu-identitas/{ageGroup}', [OfficialController::class, 'idCards'])->name('officials.id-cards');
        Route::get('dashboard/ofisial/kartu-identitas/{ageGroup}/pdf', [OfficialController::class, 'exportIdCards'])->name('officials.id-cards.export');
        Route::get('dashboard/ofisial/{official}', [OfficialController::class, 'show'])->name('officials.show');
        Route::get('dashboard/ofisial/{official}/dokumen/{document}', [OfficialController::class, 'downloadDocument'])->name('officials.documents.download');
        Route::get('dashboard/ofisial/{official}/kartu-identitas/{ageGroup}', [OfficialController::class, 'idCard'])->name('officials.id-card');
        Route::get('dashboard/ofisial/{official}/kartu-identitas/{ageGroup}/pdf', [OfficialController::class, 'exportIdCard'])->name('officials.id-card.export');
        Route::delete('dashboard/ofisial/{official}/kelompok-usia/{ageGroup}', [OfficialController::class, 'destroyAgeRegistration'])->name('officials.age-registrations.destroy');
        Route::get('dashboard/pemain/{player}', [PlayerController::class, 'show'])->name('players.show');
        Route::get('dashboard/pemain/{player}/dokumen/{document}', [PlayerController::class, 'downloadDocument'])->name('players.documents.download');
        Route::get('dashboard/pemain/{player}/kartu-identitas/{ageGroup}', [PlayerController::class, 'idCard'])->name('players.id-card');
        Route::get('dashboard/pemain/{player}/kartu-identitas/{ageGroup}/pdf', [PlayerController::class, 'exportIdCard'])->name('players.id-card.export');
        Route::patch('dashboard/pemain/{player}/kelompok-usia/{ageGroup}', [PlayerController::class, 'updateAgeRegistration'])->name('players.age-registrations.update');
        Route::delete('dashboard/pemain/{player}/kelompok-usia/{ageGroup}', [PlayerController::class, 'destroyAgeRegistration'])->name('players.age-registrations.destroy');
        Route::post('dashboard/ofisial/{official}/submit', [OfficialController::class, 'submit'])->name('officials.submit');
        Route::post('dashboard/ofisial/{official}/review', [OfficialController::class, 'review'])->middleware('role:admin')->name('officials.review');
        Route::post('dashboard/ofisial/bulk-review', [OfficialController::class, 'bulkReview'])->middleware('role:admin')->name('officials.bulk-review');
        Route::post('dashboard/pemain/{player}/submit', [PlayerController::class, 'submit'])->name('players.submit');
        Route::post('dashboard/pemain/{player}/review', [PlayerController::class, 'review'])->middleware('role:admin')->name('players.review');
        Route::post('dashboard/pemain/bulk-review', [PlayerController::class, 'bulkReview'])->middleware('role:admin')->name('players.bulk-review');
        Route::post('dashboard/dsp/{lineup_list}/submit', [LineupListController::class, 'submit'])->name('lineup-lists.submit');
        Route::post('dashboard/dsp/{lineup_list}/review', [LineupListController::class, 'review'])->middleware('role:admin')->name('lineup-lists.review');
        Route::post('dashboard/dsp/bulk-review', [LineupListController::class, 'bulkReview'])->middleware('role:admin')->name('lineup-lists.bulk-review');
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('dashboard/pertandingan/bulk-delete', [MatchScheduleController::class, 'bulkDestroy'])->name('matches.bulk-delete');
        Route::get('dashboard/pertandingan', [DashboardController::class, 'legacyMatchesIndexRedirect'])->name('matches.legacy-index');
        Route::get('dashboard/pertandingan/liga', [MatchScheduleController::class, 'leagueIndex'])->name('matches.league.index');
        Route::get('dashboard/pertandingan/knockout', [MatchScheduleController::class, 'knockoutIndex'])->name('matches.knockout.index');
        Route::patch('dashboard/pertandingan/knockout/{match}/position', [MatchScheduleController::class, 'updateKnockoutPosition'])->name('matches.knockout.position');
        Route::resource('dashboard/pertandingan', MatchScheduleController::class)
            ->except(['index', 'show'])
            ->parameters(['pertandingan' => 'match'])
            ->names('matches');
        Route::patch('dashboard/hasil-pertandingan/{match}/result', [MatchScheduleController::class, 'updateResult'])->name('match-results.update');
        Route::get('dashboard/sponsor', [SponsorController::class, 'index'])->name('sponsors.index');
        Route::post('dashboard/sponsor', [SponsorController::class, 'store'])->name('sponsors.store');
        Route::get('dashboard/sponsor/{sponsor}/edit', [SponsorController::class, 'edit'])->name('sponsors.edit');
        Route::put('dashboard/sponsor/{sponsor}', [SponsorController::class, 'update'])->name('sponsors.update');
        Route::delete('dashboard/sponsor/{sponsor}', [SponsorController::class, 'destroy'])->name('sponsors.destroy');
        Route::get('dashboard/akun-admin', [AdminAccountController::class, 'index'])->name('admin-accounts.index');
        Route::post('dashboard/akun-admin', [AdminAccountController::class, 'store'])->name('admin-accounts.store');
        Route::get('dashboard/akun-admin/{adminAccount}/edit', [AdminAccountController::class, 'edit'])->name('admin-accounts.edit');
        Route::put('dashboard/akun-admin/{adminAccount}', [AdminAccountController::class, 'update'])->name('admin-accounts.update');
        Route::delete('dashboard/akun-admin/{adminAccount}', [AdminAccountController::class, 'destroy'])->name('admin-accounts.destroy');
        Route::get('dashboard/akun-klub/create', [SettingsController::class, 'createClubAccount'])->name('club-accounts.create');
        Route::post('dashboard/akun-klub', [SettingsController::class, 'storeClubAccount'])->name('club-accounts.store');
        Route::get('dashboard/akun-klub/{clubAccount}/edit', [SettingsController::class, 'editClubAccount'])->name('club-accounts.edit');
        Route::put('dashboard/akun-klub/{clubAccount}', [SettingsController::class, 'updateClubAccount'])->name('club-accounts.update');
        Route::delete('dashboard/akun-klub/{clubAccount}', [SettingsController::class, 'destroyClubAccount'])->name('club-accounts.destroy');
        Route::get('dashboard/season', [SeasonController::class, 'index'])->name('seasons.index');
        Route::post('dashboard/season', [SeasonController::class, 'store'])->name('seasons.store');
        Route::put('dashboard/season/{season}', [SeasonController::class, 'update'])->name('seasons.update');
        Route::post('dashboard/season/{season}/activate', [SeasonController::class, 'activate'])->name('seasons.activate');
        Route::post('dashboard/season/{season}/archive', [SeasonController::class, 'archive'])->name('seasons.archive');
        Route::delete('dashboard/klub/{club}', [ClubController::class, 'destroy'])->name('clubs.destroy');

    });
});
