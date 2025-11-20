<?php

use App\Http\Controllers\ChartOfAccountsController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GlossaryController;
use App\Http\Controllers\Auth\AzureController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware(['tenant.identify', 'auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resources - All authenticated users can view (index, show)
    // IMPORTANT: Define specific routes (like 'create', 'export') BEFORE parameterized routes to avoid route conflicts
    Route::get('chart-of-accounts', [ChartOfAccountsController::class, 'index'])->name('chart-of-accounts.index');
    Route::get('journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('fixed-assets', [FixedAssetController::class, 'index'])->name('fixed-assets.index');
    Route::get('asset-categories', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
    Route::get('item-categories', [ItemCategoryController::class, 'index'])->name('item-categories.index');
    Route::get('/glossary', [GlossaryController::class, 'index'])->name('glossary.index');

    // Admin and Accountant can create, edit, delete
    // Define these BEFORE the show routes to avoid route conflicts
    Route::middleware(['role:admin,accountant'])->group(function () {
        Route::get('chart-of-accounts/create', [ChartOfAccountsController::class, 'create'])->name('chart-of-accounts.create');
        Route::post('chart-of-accounts', [ChartOfAccountsController::class, 'store'])->name('chart-of-accounts.store');
        Route::get('chart-of-accounts/{chart_of_account}/edit', [ChartOfAccountsController::class, 'edit'])->name('chart-of-accounts.edit');
        Route::put('chart-of-accounts/{chart_of_account}', [ChartOfAccountsController::class, 'update'])->name('chart-of-accounts.update');
        Route::delete('chart-of-accounts/{chart_of_account}', [ChartOfAccountsController::class, 'destroy'])->name('chart-of-accounts.destroy');
        
        Route::get('journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
        Route::post('journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
        Route::get('journal-entries/{journal_entry}/edit', [JournalEntryController::class, 'edit'])->name('journal-entries.edit');
        Route::put('journal-entries/{journal_entry}', [JournalEntryController::class, 'update'])->name('journal-entries.update');
        Route::delete('journal-entries/{journal_entry}', [JournalEntryController::class, 'destroy'])->name('journal-entries.destroy');
        
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        
        Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
        
        Route::get('bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('bills/{bill}', [BillController::class, 'update'])->name('bills.update');
        Route::delete('bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
        
        Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        
        Route::get('fixed-assets/create', [FixedAssetController::class, 'create'])->name('fixed-assets.create');
        Route::post('fixed-assets', [FixedAssetController::class, 'store'])->name('fixed-assets.store');
        Route::get('fixed-assets/{fixed_asset}/edit', [FixedAssetController::class, 'edit'])->name('fixed-assets.edit');
        Route::put('fixed-assets/{fixed_asset}', [FixedAssetController::class, 'update'])->name('fixed-assets.update');
        Route::delete('fixed-assets/{fixed_asset}', [FixedAssetController::class, 'destroy'])->name('fixed-assets.destroy');
        
        Route::get('asset-categories/create', [AssetCategoryController::class, 'create'])->name('asset-categories.create');
        Route::post('asset-categories', [AssetCategoryController::class, 'store'])->name('asset-categories.store');
        Route::get('asset-categories/{asset_category}/edit', [AssetCategoryController::class, 'edit'])->name('asset-categories.edit');
        Route::put('asset-categories/{asset_category}', [AssetCategoryController::class, 'update'])->name('asset-categories.update');
        Route::delete('asset-categories/{asset_category}', [AssetCategoryController::class, 'destroy'])->name('asset-categories.destroy');
        
        Route::get('item-categories/create', [ItemCategoryController::class, 'create'])->name('item-categories.create');
        Route::post('item-categories', [ItemCategoryController::class, 'store'])->name('item-categories.store');
        Route::get('item-categories/{item_category}/edit', [ItemCategoryController::class, 'edit'])->name('item-categories.edit');
        Route::put('item-categories/{item_category}', [ItemCategoryController::class, 'update'])->name('item-categories.update');
        Route::delete('item-categories/{item_category}', [ItemCategoryController::class, 'destroy'])->name('item-categories.destroy');
        
        Route::post('/glossary', [GlossaryController::class, 'store'])->name('glossary.store');
        Route::get('/glossary/create', [GlossaryController::class, 'create'])->name('glossary.create');
        Route::delete('/glossary/{glossary}', [GlossaryController::class, 'destroy'])->name('glossary.destroy');
    });

    // Show routes - Must be defined AFTER create/edit routes to avoid route conflicts
    Route::get('chart-of-accounts/{chart_of_account}', [ChartOfAccountsController::class, 'show'])->name('chart-of-accounts.show');
    Route::get('journal-entries/{journal_entry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::get('bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('fixed-assets/{fixed_asset}', [FixedAssetController::class, 'show'])->name('fixed-assets.show');
    Route::get('asset-categories/{asset_category}', [AssetCategoryController::class, 'show'])->name('asset-categories.show');
    Route::get('item-categories/{item_category}', [ItemCategoryController::class, 'show'])->name('item-categories.show');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/trial-balance/export', [ReportController::class, 'exportTrialBalance'])->name('trial-balance.export');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/export', [ReportController::class, 'exportProfitLoss'])->name('profit-loss.export');
        Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/export', [ReportController::class, 'exportBalanceSheet'])->name('balance-sheet.export');
    });
    
    // Dashboard Routes
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/visualization', [DashboardController::class, 'visualization'])->name('dashboard.visualization');
    Route::get('/chart-of-accounts/export', [ChartOfAccountsController::class, 'export'])->name('chart-of-accounts.export');
    Route::get('/journal-entries/export', [JournalEntryController::class, 'export'])->name('journal-entries.export');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('/invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/vendors/export', [VendorController::class, 'export'])->name('vendors.export');
    Route::get('/bills/export', [BillController::class, 'export'])->name('bills.export');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::get('/fixed-assets/export', [FixedAssetController::class, 'export'])->name('fixed-assets.export');

    // Logs - All authenticated users can view, only admin can delete
    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/logs/export', [LogsController::class, 'export'])->name('logs.export');
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('/logs/{log}', [LogsController::class, 'destroy'])->name('logs.destroy');
    });

    // Status Changes and Actions - Only Admin and Accountant
    Route::middleware(['role:admin,accountant'])->group(function () {
        // Journal Entry Status Changes
        Route::post('journal-entries/{id}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
        Route::post('journal-entries/{id}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
        
        // Invoice Status Changes
        Route::post('invoices/{id}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('invoices/{id}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('invoices/{id}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        
        // Bill Status Changes
        Route::post('bills/{id}/mark-received', [BillController::class, 'markReceived'])->name('bills.mark-received');
        Route::post('bills/{id}/mark-paid', [BillController::class, 'markPaid'])->name('bills.mark-paid');
        Route::post('bills/{id}/cancel', [BillController::class, 'cancel'])->name('bills.cancel');
        
        // Fixed Asset Status Changes
        Route::post('fixed-assets/{id}/dispose', [FixedAssetController::class, 'dispose'])->name('fixed-assets.dispose');
        
        // Master Data Active/Inactive Toggle
        Route::post('customers/{id}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
        Route::post('vendors/{id}/toggle-active', [VendorController::class, 'toggleActive'])->name('vendors.toggle-active');
        Route::post('chart-of-accounts/{id}/toggle-active', [ChartOfAccountsController::class, 'toggleActive'])->name('chart-of-accounts.toggle-active');
        Route::post('inventory/{id}/toggle-active', [InventoryController::class, 'toggleActive'])->name('inventory.toggle-active');
    });
});

Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::get('/login/azure', [AzureController::class, 'redirect'])->middleware('guest')->name('login.azure');
Route::get('/login/azure/callback', [AzureController::class, 'callback'])->middleware('guest')->name('login.azure.callback');

// User profile picture route
Route::middleware(['tenant.identify', 'auth'])->group(function () {
    Route::get('/user/{id}/profile-picture', [\App\Http\Controllers\UserProfileController::class, 'profilePicture'])->name('user.profile-picture');
});

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware('guest');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

Route::get('/', function () {
    return redirect('/dashboard');
});
