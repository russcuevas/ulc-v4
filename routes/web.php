<?php
// AUTH
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\ChatController;

// ADMIN
use App\Http\Controllers\admin\AdminChangePasswordController;
use App\Http\Controllers\admin\AdminCollectorController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminSecretaryController;
use App\Http\Controllers\admin\area\AdminCollectionController;
use App\Http\Controllers\admin\area\AdminManilaClientsController;
use App\Http\Controllers\admin\area\AdminManilaController;
use App\Http\Controllers\client\ClientAuthController;
use App\Http\Controllers\client\ClientDashboardController;
use App\Http\Controllers\collector\CollectorCollectionController;
use App\Http\Controllers\collector\CollectorDashboardController;
use App\Http\Controllers\NotificationsController;
// SECRETARY
use App\Http\Controllers\secretary\area\SecretaryAreaController;
use App\Http\Controllers\secretary\area\SecretaryClientsController;
use App\Http\Controllers\secretary\area\SecretaryCollectionController;
use App\Http\Controllers\secretary\SecretaryCollectorController;
use App\Http\Controllers\secretary\SecretaryDashboardController;

// COLLECTOR
use App\Http\Controllers\management\ManagementDashboardController;
use App\Http\Controllers\management\ManagementAreaController;
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

Route::get('/', function () {
    return view('welcome');
});

// ADMIN, SECRETARY, COLLECTOR ROUTES
Route::get('/login', [AuthController::class, 'LoginPage'])->name('auth.login.page');
Route::post('/login/request', [AuthController::class, 'LoginRequest'])->name('auth.login.request');
Route::post('/logout', [AuthController::class, 'LogoutRequest'])->name('auth.logout.request');


// CLIENT ROUTE
Route::get('/client/login', [ClientAuthController::class, 'ClientLoginPage'])->name('client.login.page');
Route::post('/client/login/request', [ClientAuthController::class, 'ClientLoginRequest'])->name('client.login.request');
Route::post('/client/logout', [ClientAuthController::class, 'ClientLogoutRequest'])->name('client.logout.request');

Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'ClientDashboardPage'])->name('dashboard.page');
    Route::get('/chat', [ChatController::class, 'ClientChatPage'])->name('chat.page');
});

// ADMIN ROUTES

Route::middleware('role:admin')->prefix('admin')->group(function () {

    // CHANGE PASSWORD
    Route::put('/change-password', [AdminChangePasswordController::class, 'updatePassword'])
        ->name('admin.change_password');

    // DASHBOARD
    Route::get('/dashboard', [AdminDashboardController::class, 'AdminDashboardPage'])
        ->name('admin.dashboard.page');

    // SECRETARY
    Route::get('/secretary', [AdminSecretaryController::class, 'AdminSecretaryPage'])
        ->name('admin.secretary.page');

    Route::post('/secretary/add', [AdminSecretaryController::class, 'AdminAddSecretary'])
        ->name('admin.secretary.store');

    Route::post('/secretary/assign-areas/{id}', [AdminSecretaryController::class, 'AdminAssignSecretaryAreas'])
        ->name('admin.secretary.assign_areas');

    Route::put('/secretary/update/{id}', [AdminSecretaryController::class, 'AdminUpdateSecretary'])
        ->name('admin.secretary.update');

    // COLLECTOR
    Route::get('/collector', [AdminCollectorController::class, 'AdminCollectorPage'])
        ->name('admin.collector.page');

    Route::post('/collector/add', [AdminCollectorController::class, 'AdminAddCollector'])
        ->name('admin.collector.store');

    Route::post('/collector/assign-areas/{id}', [AdminCollectorController::class, 'AdminAssignCollectorAreas'])
        ->name('admin.collector.assign_areas');

    Route::put('/collector/update/{id}', [AdminCollectorController::class, 'AdminUpdateCollector'])
        ->name('admin.collector.update');

    // AREAS
    Route::get('/areas', [AdminManilaController::class, 'AdminAreasPage'])
        ->name('admin.areas.page');

    Route::post('/areas/sales-report', [AdminManilaController::class, 'AdminSalesReportPrint'])
        ->name('admin.areas.sales.report.print');

    Route::get('/areas/clients/{id}', [AdminManilaClientsController::class, 'AdminManilaClientsPage'])
        ->name('admin.areas.clients.page');

    Route::post('/areas/clients/{id}/add', [AdminManilaClientsController::class, 'AdminManilaAddClientRequest'])
        ->name('admin.area.clients.add');

    Route::get('/areas/clients/{id}/loans', [AdminManilaClientsController::class, 'AdminManilaViewClientLoans'])
        ->name('admin.area.clients.loans');

    Route::get('/areas/clients/{id}/print-summary-loan', [AdminManilaClientsController::class, 'AdminPrintSummaryLoan'])
        ->name('admin.area.clients.print_summary_loan');

    Route::put('/areas/clients/{id}/update', [AdminManilaClientsController::class, 'AdminManilaUpdateClientRequest'])
        ->name('admin.area.clients.update');

    Route::delete('/areas/clients/{id}/delete', [AdminManilaClientsController::class, 'AdminManilaDeleteClient'])
        ->name('admin.area.clients.delete');

    Route::post('/areas/clients/{id}/renew-loan', [AdminManilaClientsController::class, 'AdminManilaSubmitRenewLoan'])
        ->name('admin.area.clients.renew.loan.add');

    Route::get('/areas/clients/soa/{loanId}', [AdminManilaClientsController::class, 'AdminManilaGenerateSOA'])
        ->name('admin.area.clients.generate.soa');

    Route::get('/areas/{areaId}/collections', [AdminCollectionController::class, 'AdminCollectionReferencesPage'])
        ->name('admin.areas.collections.references');

    Route::get('/collections/{referenceNumber}', [AdminCollectionController::class, 'AdminCollectionDetailPage'])
        ->name('admin.collections.detail');

    Route::post('/areas/collections/collect/{refNo}', [AdminCollectionController::class, 'AdminCollectClientsPayment'])
        ->name('admin.collections.collect');

    Route::get('/collections/print/{refNo}', [AdminCollectionController::class, 'AdminPrintCollection'])
        ->name('admin.collections.print');

    Route::put('/collections/payment/{paymentId}/collection', [AdminCollectionController::class, 'AdminEditPaymentCollection'])
        ->name('admin.collections.payment.edit');

    Route::post('/collections/payment/save', [AdminCollectionController::class, 'AdminSavePaymentCollection'])
        ->name('admin.collections.payment.save');

    Route::post('/collections/savings/save', [AdminCollectionController::class, 'AdminSaveSavingsAmount'])
        ->name('admin.collections.savings.save');

    Route::post('/collections/savings/reverse', [AdminCollectionController::class, 'AdminReverseSavingsAmount'])
        ->name('admin.collections.savings.reverse');

    Route::post('/collections/payment/reverse', [AdminCollectionController::class, 'AdminReversePaymentCollection'])
        ->name('admin.collections.payment.reverse');

    Route::get('/areas/{areaId}/collections/summary-print', [AdminCollectionController::class, 'AdminPrintSummaryCollection'])
        ->name('admin.areas.collections.summary.print');



    Route::get('/areas/{location}', [AdminManilaController::class, 'AdminManilaPage'])
        ->name('admin.areas.location.page');

    Route::get('/chat', [ChatController::class, 'StaffChatPage'])
        ->name('admin.chat.page');
});

// SECRETARY ROUTES
Route::middleware('role:secretary')->prefix('secretary')->name('secretary.')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [SecretaryDashboardController::class, 'SecretaryDashboardPage'])
        ->name('dashboard.page');

    // COLLECTOR
    Route::get('/collector', [SecretaryCollectorController::class, 'SecretaryCollectorPage'])
        ->name('collector.page');

    Route::put('/collector/update/{id}', [SecretaryCollectorController::class, 'SecretaryUpdateCollector'])
        ->name('collector.update');

    // AREAS PAGE
    Route::get('/areas', [SecretaryAreaController::class, 'SecretaryAreasPage'])
        ->name('areas.page');

    // SALES REPORT (modal post)
    Route::post('/areas/sales-report', [SecretaryAreaController::class, 'SecretarySalesReportPrint'])
        ->name('areas.sales.report.print');


    // AREAS CLIENTS PAGE

    Route::get('/areas/clients/{id}', [SecretaryClientsController::class, 'SecretaryClientsPage'])
        ->name('areas.clients.page');

    Route::post('/areas/clients/{id}/add', [SecretaryClientsController::class, 'SecretaryAddClientRequest'])
        ->name('area.clients.add');

    Route::get('/areas/clients/{id}/loans', [SecretaryClientsController::class, 'SecretaryViewClientLoans'])
        ->name('area.clients.loans');

    // PRINT SUMMARY LOAN
    Route::get('/areas/clients/{id}/print-summary-loan', [SecretaryClientsController::class, 'SecretaryPrintSummaryLoan'])
        ->name('area.clients.print_summary_loan');

    Route::put('/areas/clients/{id}/update', [SecretaryClientsController::class, 'SecretaryUpdateClientRequest'])
        ->name('area.clients.update');

    Route::post('/areas/clients/{id}/renew-loan', [SecretaryClientsController::class, 'SecretarySubmitRenewLoan'])
        ->name('area.clients.renew.loan.add');

    // PRINT SOA
    Route::get('/areas/clients/soa/{loanId}', [SecretaryClientsController::class, 'SecretaryGenerateSOA'])
        ->name('area.clients.generate.soa');

    // AREAS COLLECTION PAGE
    Route::get('/areas/{areaId}/collections', [SecretaryCollectionController::class, 'SecretaryCollectionReferencesPage'])
        ->name('areas.collections.references');


    Route::get('/collections/{referenceNumber}', [SecretaryCollectionController::class, 'SecretaryCollectionDetailPage'])
        ->name('collections.detail');

    Route::post('/areas/collections/collect/{refNo}', [SecretaryCollectionController::class, 'SecretaryCollectClientsPayment'])
        ->name('collections.collect');

    Route::post('/collections/payment/save', [SecretaryCollectionController::class, 'SecretarySavePaymentCollection'])
        ->name('collections.payment.save');

    Route::post('/collections/savings/save', [SecretaryCollectionController::class, 'SecretarySaveSavingsAmount'])
        ->name('collections.savings.save');

    Route::post('/collections/savings/reverse', [SecretaryCollectionController::class, 'SecretaryReverseSavingsAmount'])
        ->name('collections.savings.reverse');

    Route::post('/collections/payment/reverse', [SecretaryCollectionController::class, 'SecretaryReversePaymentCollection'])
        ->name('collections.payment.reverse');

    // PRINT COLLECTION
    Route::get('/collections/print/{refNo}', [SecretaryCollectionController::class, 'SecretaryPrintCollection'])
        ->name('collections.print');

    // PRINT SUMMARY COLLECTION
    Route::get('/areas/{areaId}/collections/summary-print', [SecretaryCollectionController::class, 'SecretaryPrintSummaryCollection'])
        ->name('areas.collections.summary.print');

    Route::get('/chat', [ChatController::class, 'StaffChatPage'])
        ->name('chat.page');
});

// COLLECTOR ROUTES
Route::middleware('role:collector')->prefix('collector')->name('collector.')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [CollectorDashboardController::class, 'CollectorDashboardPage'])
        ->name('dashboard.page');

    // COLLECTIONS PAGE
    Route::get('/collections', [CollectorCollectionController::class, 'CollectorCollectionPage'])
        ->name('collections.page');
    Route::post('/collections/store', [CollectorCollectionController::class, 'CollectorCollectPaymentRequest'])
        ->name('collections.store');
    Route::post('/collections/bulk-store', [CollectorCollectionController::class, 'CollectorBulkCollectPaymentRequest'])
        ->name('collections.bulk-store');

    Route::get('/chat', [ChatController::class, 'StaffChatPage'])
        ->name('chat.page');
});

// MANAGEMENT ROUTES
Route::middleware('role:management')->prefix('management')->name('management.')->group(function () {
    // DASHBOARD
    Route::get('/dashboard', [ManagementDashboardController::class, 'ManagementDashboardPage'])
        ->name('dashboard.page');

    // AREAS
    Route::get('/areas', [ManagementAreaController::class, 'ManagementAreasPage'])
        ->name('areas.page');
    Route::get('/areas/{location}', [ManagementAreaController::class, 'ManagementManilaPage'])
        ->name('areas.location.page');
    Route::get('/areas/{areaId}/collections', [ManagementAreaController::class, 'ManagementCollectionReferencesPage'])
        ->name('areas.collections.references');
    Route::get('/collections/{referenceNumber}', [ManagementAreaController::class, 'ManagementCollectionDetailPage'])
        ->name('collections.detail');
    Route::get('/collections/print/{refNo}', [ManagementAreaController::class, 'ManagementPrintCollection'])
        ->name('collections.print');

    // COLLECTION REPORT
    Route::get('/collection-report', [ManagementAreaController::class, 'ManagementCollectionReportPage'])
        ->name('collection.report.page');
});

// Notifications page (shared)
Route::get('/notifications', [NotificationsController::class, 'index'])
    ->name('notifications.index');

Route::post('/notifications/mark-read', [NotificationsController::class, 'markAsRead'])
    ->name('notifications.mark.read');

Route::post('/notifications/mark-all-read', [NotificationsController::class, 'markAllAsRead'])
    ->name('notifications.mark.all');

// SECURE API CHAT ENDPOINTS
Route::get('/api/chat/conversations', [ChatController::class, 'getConversations'])->name('api.chat.conversations');
Route::get('/api/chat/messages/{conversationId}', [ChatController::class, 'getMessages'])->name('api.chat.messages');
Route::post('/api/chat/send', [ChatController::class, 'sendMessage'])->name('api.chat.send');
Route::post('/api/chat/clear', [ChatController::class, 'clearMessages'])->name('api.chat.clear');
