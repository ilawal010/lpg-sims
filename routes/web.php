<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\FirstLoginPasswordController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\LpgPricingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\AccessoryController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', function () {

    abort_if(
        auth()->user()?->hasRole('attendant'),
        403,
        'Sales Agents cannot access the Management Dashboard.'
    );

    return view('admin.dashboard');

})->middleware([
    'auth',
    'password.changed',
    'permission:dashboard.view',
])->name('dashboard');



Route::get(
    '/manager',
    [\App\Http\Controllers\Manager\DashboardController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:dashboard.view',
])
->name('manager.dashboard');

/*
|--------------------------------------------------------------------------
| Branch Manager — Sales History
|--------------------------------------------------------------------------
| Posted LPG and Accessories sales.
| Branch authorization is enforced by SalesHistoryController.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/sales-history',
    [\App\Http\Controllers\Manager\SalesHistoryController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:sales.view',
])
->name('manager.sales.index');

/*
|--------------------------------------------------------------------------
| Branch Manager — Sales Agents Register
|--------------------------------------------------------------------------
| Read-only register of Sales Agents assigned to the
| authenticated Manager's active branches.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/sales-agents',
    [\App\Http\Controllers\Manager\SalesAgentController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:sales.view',
])
->name('manager.agents.index');

/*
|--------------------------------------------------------------------------
| Branch Manager — LPG Stock Dashboard
|--------------------------------------------------------------------------
| Read-only operational inventory for assigned active branches.
| Financial valuation and costing remain Owner-only.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/lpg-stock',
    [\App\Http\Controllers\Manager\LpgStockController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:inventory.view',
])
->name('manager.lpg-stock.index');

/*
|--------------------------------------------------------------------------
| Branch Manager — Accessories Inventory
|--------------------------------------------------------------------------
| Read-only accessories register for assigned active branches.
| Buying prices and inventory costs remain Owner-only.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/accessories',
    [\App\Http\Controllers\Manager\AccessoryController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:inventory.view',
])
->name('manager.accessories.index');

/*
|--------------------------------------------------------------------------
| Branch Manager — LPG Storage Units
|--------------------------------------------------------------------------
| Read-only storage unit register for assigned active branches.
| No financial valuations or storage unit modifications.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/storage-units',
    [\App\Http\Controllers\Manager\LpgStorageUnitController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:inventory.view',
])
->name('manager.storage-units.index');

/*
|--------------------------------------------------------------------------
| Branch Manager — LPG Stock Movements
|--------------------------------------------------------------------------
| Read-only stock movement history for assigned active branches.
| Financial costs and valuations remain Owner-only.
|--------------------------------------------------------------------------
*/

Route::get(
    '/manager/stock-movements',
    [\App\Http\Controllers\Manager\LpgStockMovementController::class, 'index']
)
->middleware([
    'auth',
    'password.changed',
    'permission:inventory.view',
])
->name('manager.stock-movements.index');


/*
|--------------------------------------------------------------------------
| Manager LPG Supply Receipts
|--------------------------------------------------------------------------
| Branch authorization is enforced by the Manager controller
| and the LPG Stock Receipt Service.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        // Manager — Central LPG Supplier Register (Read Only)
        Route::get(
            '/suppliers',
            [\App\Http\Controllers\Manager\LpgSupplierController::class, 'index']
        )
            ->middleware('permission:inventory.view')
            ->name('suppliers.index');
        /*
        |--------------------------------------------------------------------------
        | Manager Finance — Expense Management
        |--------------------------------------------------------------------------
        */

        Route::get('/expenses', [
            \App\Http\Controllers\Manager\ExpenseController::class,
            'index'
        ])
            ->middleware('permission:expenses.view')
            ->name('expenses.index');

        Route::get('/expenses/create', [
            \App\Http\Controllers\Manager\ExpenseController::class,
            'create'
        ])
            ->middleware('permission:expenses.create')
            ->name('expenses.create');

        Route::post('/expenses', [
            \App\Http\Controllers\Manager\ExpenseController::class,
            'store'
        ])
            ->middleware('permission:expenses.create')
            ->name('expenses.store');
        Route::post('/expenses/{expense}/void', [
            \App\Http\Controllers\Manager\ExpenseController::class,
            'void',
        ])
            ->middleware('permission:expenses.void')
            ->name('expenses.void');
       
        Route::get('/lpg-stock-receipts',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'index']
        )
        ->middleware('permission:inventory.view')
        ->name('stock-receipts.index');

        Route::get('/lpg-stock-receipts/create',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'create']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.create');

        Route::post('/lpg-stock-receipts',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'store']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.store');

        Route::get('/lpg-stock-receipts/{receipt}/edit',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'edit']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.edit');

        Route::put('/lpg-stock-receipts/{receipt}',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'update']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.update');

        Route::post('/lpg-stock-receipts/{receipt}/cancel',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'cancel']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.cancel');

        Route::post('/lpg-stock-receipts/{receipt}/post',
            [\App\Http\Controllers\Manager\LpgStockReceiptController::class, 'post']
        )
        ->middleware('permission:inventory.receive')
        ->name('stock-receipts.post');
    });


/*
|--------------------------------------------------------------------------
| Sales Agent Landing Route
|--------------------------------------------------------------------------
| Restricted to Sales Agents (internal role: attendant).
|--------------------------------------------------------------------------
*/

Route::get('/agent', function () {
    abort_unless(
        auth()->user()?->hasRole('attendant'),
        403
    );

    return redirect()->route('admin.lpg-pos.index');
})->middleware([
    'auth',
    'password.changed',
    'permission:sales.create',
])->name('agent.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Sales Agent — My Sales
|--------------------------------------------------------------------------
| Access restricted to Sales Agents.
| Controller queries are scoped to the authenticated attendant.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:sales.create',
])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {

        Route::get('/sales', [
            \App\Http\Controllers\Agent\MySalesController::class,
            'index'
        ])->name('sales.index');


        /*
        |--------------------------------------------------------------------------
        | Sales Agent — Individual Sales Report
        |--------------------------------------------------------------------------
        | Date-filtered LPG and accessory sales belonging
        | exclusively to the authenticated Sales Agent.
        |--------------------------------------------------------------------------
        */

        Route::get('/reports/sales', [
            \App\Http\Controllers\Agent\MySalesReportController::class,
            'index'
        ])->name('reports.sales.index');


        /*
        |--------------------------------------------------------------------------
        | Sales Agent — PDF Sales Report
        |--------------------------------------------------------------------------
        | Generates a date-filtered PDF containing only the
        | authenticated Sales Agent's posted transactions.
        |--------------------------------------------------------------------------
        */

        Route::get('/reports/sales/pdf', [
            \App\Http\Controllers\Agent\MySalesReportController::class,
            'exportPdf'
        ])->name('reports.sales.pdf');



    });

/*
|--------------------------------------------------------------------------
| Mandatory First-Login Password Change
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get(
        '/first-login/change-password',
        [FirstLoginPasswordController::class, 'edit']
    )->name('password.first-change');

    Route::put(
        '/first-login/change-password',
        [FirstLoginPasswordController::class, 'update']
    )->name('password.first-change.update');

});

/*
|--------------------------------------------------------------------------
| Staff User Management
|--------------------------------------------------------------------------
| Restricted to authorized users.
*/
Route::middleware([
    'auth',
    'password.changed',
    'permission:users.manage',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->name('users.edit');

        Route::put('/users/{user}', [UserController::class, 'update'])
            ->name('users.update');

    
        /*
        |--------------------------------------------------------------------------
        | Roles & Permissions Management
        |--------------------------------------------------------------------------
        */
        Route::get('/roles', [RolePermissionController::class, 'index'])
            ->name('roles.index');

        Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])
            ->name('roles.edit');

        Route::put('/roles/{role}', [RolePermissionController::class, 'update'])
            ->name('roles.update');

        
        /*
        |--------------------------------------------------------------------------
        | Audit Logs
        |--------------------------------------------------------------------------
        */
        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');

        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
            ->name('audit-logs.show');

        /*
        |--------------------------------------------------------------------------
        | Business Settings
        |--------------------------------------------------------------------------
        */
        Route::get('/settings', [BusinessSettingController::class, 'edit'])
            ->name('settings.edit');

        Route::put('/settings', [BusinessSettingController::class, 'update'])
            ->name('settings.update');
        Route::post('/settings/logo', [
            BusinessSettingController::class,
            'uploadLogo'
        ])->name('settings.logo.upload');

        Route::delete('/settings/logo', [
            BusinessSettingController::class,
            'removeLogo'
        ])->name('settings.logo.remove');


        
        /*
        |--------------------------------------------------------------------------
        | LPG Supplier Management
        |--------------------------------------------------------------------------
        | These routes are inside the existing admin-prefixed route group.
        |--------------------------------------------------------------------------
        */

        Route::get('/suppliers', [
            \App\Http\Controllers\Admin\LpgSupplierController::class,
            'index'
        ])->name('suppliers.index');

        Route::get('/suppliers/create', [
            \App\Http\Controllers\Admin\LpgSupplierController::class,
            'create'
        ])->name('suppliers.create');

        Route::post('/suppliers', [
            \App\Http\Controllers\Admin\LpgSupplierController::class,
            'store'
        ])->name('suppliers.store');

        Route::get('/suppliers/{supplier}/edit', [
            \App\Http\Controllers\Admin\LpgSupplierController::class,
            'edit'
        ])->name('suppliers.edit');

        Route::put('/suppliers/{supplier}', [
            \App\Http\Controllers\Admin\LpgSupplierController::class,
            'update'
        ])->name('suppliers.update');


          });  
/*
|--------------------------------------------------------------------------
| LPG Selling Price Management
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth',
    'password.changed',
    'permission:prices.manage',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/lpg-pricing', [
            LpgPricingController::class,
            'index'
        ])->name('lpg-pricing.index');

        Route::put('/lpg-pricing', [
            LpgPricingController::class,
            'update'
        ])->name('lpg-pricing.update');

    });


/*
|--------------------------------------------------------------------------
| Branch / Location Management
|--------------------------------------------------------------------------
| Owner-only access.
| Storage tanks and LPG storage cylinders will belong to branches.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:users.manage',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/branches', [BranchController::class, 'index'])
            ->name('branches.index');

        Route::get('/branches/create', [BranchController::class, 'create'])
            ->name('branches.create');

        Route::post('/branches', [BranchController::class, 'store'])
            ->name('branches.store');

        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])
            ->name('branches.edit');

        Route::put('/branches/{branch}', [BranchController::class, 'update'])
            ->name('branches.update');
    });


/*
|--------------------------------------------------------------------------
| LPG Storage Unit Management
|--------------------------------------------------------------------------
| Owner-managed registration and maintenance of LPG storage tanks
| and storage cylinders belonging to registered branches.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:users.manage',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/storage-units',
            [\App\Http\Controllers\Admin\LpgStorageUnitController::class, 'index']
        )->name('storage-units.index');

        Route::get(
            '/storage-units/create',
            [\App\Http\Controllers\Admin\LpgStorageUnitController::class, 'create']
        )->name('storage-units.create');

        Route::post(
            '/storage-units',
            [\App\Http\Controllers\Admin\LpgStorageUnitController::class, 'store']
        )->name('storage-units.store');

        Route::get(
            '/storage-units/{storageUnit}/edit',
            [\App\Http\Controllers\Admin\LpgStorageUnitController::class, 'edit']
        )->name('storage-units.edit');

        Route::put(
            '/storage-units/{storageUnit}',
            [\App\Http\Controllers\Admin\LpgStorageUnitController::class, 'update']
        )->name('storage-units.update');

    });

/*
|--------------------------------------------------------------------------
| LPG Opening Stock Management
|--------------------------------------------------------------------------
| Owner-controlled initial stock posting for registered LPG
| storage tanks and storage cylinders.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:users.manage',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/opening-stock',
            [\App\Http\Controllers\Admin\LpgOpeningStockController::class, 'index']
        )->name('opening-stock.index');

        Route::get(
            '/opening-stock/{storageUnit}/create',
            [\App\Http\Controllers\Admin\LpgOpeningStockController::class, 'create']
        )->name('opening-stock.create');

        Route::post(
            '/opening-stock/{storageUnit}',
            [\App\Http\Controllers\Admin\LpgOpeningStockController::class, 'store']
        )->name('opening-stock.store');
        // Accessories Inventory Register
        Route::get('/accessories', [AccessoryController::class, 'index'])
          ->name('accessories.index');
        // Display Add Accessory Form
        Route::get('/accessories/create', [AccessoryController::class, 'create'])
            ->name('accessories.create');
        // Save New Accessory
        Route::post('/accessories', [AccessoryController::class, 'store'])
            ->name('accessories.store');
        // Display Edit Accessory Form
        Route::get('/accessories/{accessory}/edit', [AccessoryController::class, 'edit'])
            ->name('accessories.edit');
        // Update Accessory Details and Restock
        Route::put('/accessories/{accessory}', [AccessoryController::class, 'update'])
            ->name('accessories.update');
        // View Accessory Restocking History
        Route::get('/accessories/{accessory}/history', [AccessoryController::class, 'history'])
            ->name('accessories.history');

    });


/*
|--------------------------------------------------------------------------
| LPG Sales Management
|--------------------------------------------------------------------------
| Sales History / LPG Sales Register.
| Restricted to users with sales.view permission.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:sales.view',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/lpg-sales',
            [\App\Http\Controllers\Admin\LpgSaleController::class, 'index']
        )->name('lpg-sales.index');
        /*
        |--------------------------------------------------------------------------
        | Reports & Analytics — Sales Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports/sales',
            [\App\Http\Controllers\Admin\SalesReportController::class, 'index']
        )->name('reports.sales.index');

        /*
        |--------------------------------------------------------------------------
        | Inventory Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports/inventory',
            [\App\Http\Controllers\Admin\InventoryReportController::class, 'index']
        )->name('reports.inventory.index');
        // Financial Reports
        Route::get(
            '/reports/financial',
            [\App\Http\Controllers\Admin\FinancialReportController::class, 'index']
        )->name('reports.financial.index');
        
        /*
        |--------------------------------------------------------------------------
        | Sales Reports — CSV Export
        |--------------------------------------------------------------------------
        */
        
        Route::get(
            '/reports/sales/export/csv',
            [\App\Http\Controllers\Admin\SalesReportController::class, 'exportCsv']
        )->name('reports.sales.export.csv');


        Route::get(
            '/reports/inventory/export/csv',
            [\App\Http\Controllers\Admin\InventoryReportController::class, 'exportCsv']
        )->name('reports.inventory.export.csv');
        // Financial Reports - CSV Export
        Route::get(
            '/reports/financial/export/csv',
            [\App\Http\Controllers\Admin\FinancialReportController::class, 'exportCsv']
        )->name('reports.financial.export.csv');
       
        /*
        |--------------------------------------------------------------------------
        | Sales Reports — PDF Export
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports/sales/export/pdf',
            [\App\Http\Controllers\Admin\SalesReportController::class, 'exportPdf']
        )->name('reports.sales.export.pdf');


        /*
        |--------------------------------------------------------------------------
        | Inventory Reports - PDF Export
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports/inventory/export/pdf',
            [\App\Http\Controllers\Admin\InventoryReportController::class, 'exportPdf']
        )->name('reports.inventory.export.pdf');


        // Financial Reports - PDF Export
        Route::get(
            '/reports/financial/export/pdf',
            [\App\Http\Controllers\Admin\FinancialReportController::class, 'exportPdf']
        )->name('reports.financial.export.pdf');


    });


/*
|--------------------------------------------------------------------------
| Reports & Analytics — Procurement Stock Settings
|--------------------------------------------------------------------------
| Owner: view and manage procurement thresholds.
| Manager: read-only access to authorized branch settings.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/reports/procurement/settings',
            [\App\Http\Controllers\Admin\ProcurementStockSettingController::class, 'index']
        )->name('reports.procurement.settings.index');

        Route::post(
            '/reports/procurement/settings',
            [\App\Http\Controllers\Admin\ProcurementStockSettingController::class, 'save']
        )->name('reports.procurement.settings.save');

    });

/*
|--------------------------------------------------------------------------
| Reports & Analytics — Procurement Advice
|--------------------------------------------------------------------------
| Owner: All branches.
| Manager: Authorized branches only.
| Agent: No access.
| Branch permissions are enforced by the controller.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:reports.view',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            '/reports/procurement',
            [\App\Http\Controllers\Admin\ProcurementAdviceController::class, 'index']
        )->name('reports.procurement.index');

        Route::get(
            '/reports/procurement/export/csv',
            [\App\Http\Controllers\Admin\ProcurementAdviceController::class, 'exportCsv']
        )->name('reports.procurement.export.csv');

        Route::get(
            '/reports/procurement/export/pdf',
            [\App\Http\Controllers\Admin\ProcurementAdviceController::class, 'exportPdf']
        )->name('reports.procurement.export.pdf');

    });

/*
|--------------------------------------------------------------------------
| LPG and Accessories Sales Receipts
|--------------------------------------------------------------------------
| Receipt access is controlled by the respective controllers.
| Sales Agents can view their own receipts.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // LPG Sales Receipt
        Route::get(
            '/lpg-sales/{sale}/receipt',
            [\App\Http\Controllers\Admin\LpgSaleController::class, 'receipt']
        )->name('lpg-sales.receipt');

        // Accessories Sales Receipt
        Route::get(
            '/accessory-sales/{sale}/receipt',
            [\App\Http\Controllers\Admin\AccessorySaleController::class, 'receipt']
        )->name('accessory-sales.receipt');

    });

/*
|--------------------------------------------------------------------------
| LPG Point of Sale
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed', 'permission:sales.create'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/lpg-pos',
            [\App\Http\Controllers\Admin\LpgPosController::class, 'index']
        )->name('lpg-pos.index');

        Route::post('/lpg-pos',
            [\App\Http\Controllers\Admin\LpgPosController::class, 'store']
        )->name('lpg-pos.store');
        Route::post('/lpg-pos/{sale}/post',
            [\App\Http\Controllers\Admin\LpgPosController::class, 'post']
        )->name('lpg-pos.post');
        
    });

/*
|--------------------------------------------------------------------------
| LPG Stock and Stock Movement Reports
|--------------------------------------------------------------------------
| Restricted to authorized inventory users.
| Sales Agents cannot access stock management records.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'password.changed',
    'permission:inventory.view',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/lpg-stock',
            [\App\Http\Controllers\Admin\LpgStockController::class, 'index']
        )->name('lpg-stock.index');

        Route::get('/lpg-stock/movements',
            [\App\Http\Controllers\Admin\LpgStockMovementController::class, 'index']
        )->name('lpg-stock.movements');

        Route::get('/lpg-stock/movements/{movement}',
            [\App\Http\Controllers\Admin\LpgStockMovementController::class, 'show']
        )->name('lpg-stock.movements.show');
        Route::get('/lpg-stock/add', [\App\Http\Controllers\Admin\LpgStockController::class,'create'])->name('lpg-stock.create');
        Route::post('/lpg-stock/add', [\App\Http\Controllers\Admin\LpgStockController::class,'store'])->name('lpg-stock.store');
        Route::post('/lpg-stock/receipts/{receipt}/post', [\App\Http\Controllers\Admin\LpgStockController::class,'post'])->name('lpg-stock.post');

        Route::get(
            '/lpg-stock/receipts/{receipt}/edit',
            [\App\Http\Controllers\Admin\LpgStockController::class, 'edit']
        )->name('lpg-stock.edit');

        Route::put(
            '/lpg-stock/receipts/{receipt}',
            [\App\Http\Controllers\Admin\LpgStockController::class, 'update']
        )->name('lpg-stock.update');

        Route::post(
            '/lpg-stock/receipts/{receipt}/cancel',
            [\App\Http\Controllers\Admin\LpgStockController::class, 'cancel']
        )->name('lpg-stock.cancel');

    });

Route::middleware(['auth', 'password.changed', 'permission:cashbook.view'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/cashbook', [\App\Http\Controllers\Finance\CashbookController::class, 'index'])
            ->name('cashbook.index');
        Route::get('/cashbook/export/csv', [\App\Http\Controllers\Finance\CashbookController::class, 'csv'])->name('cashbook.csv');
        Route::get('/cashbook/export/pdf', [\App\Http\Controllers\Finance\CashbookController::class, 'pdf'])->name('cashbook.pdf');
        Route::get('/cashbook/initialize',
            [\App\Http\Controllers\Finance\CashbookInitializationController::class, 'create']
        )->name('cashbook.initialize');

        Route::post('/cashbook/initialize',
            [\App\Http\Controllers\Finance\CashbookInitializationController::class, 'store']
        )->name('cashbook.initialize.store');
            });

Route::middleware(['auth', 'password.changed', 'permission:cashbook.view'])
    ->prefix('manager')->name('manager.')->group(function () {
        Route::get('/cashbook', [\App\Http\Controllers\Finance\CashbookController::class, 'index'])
            ->name('cashbook.index');
        Route::get('/cashbook/export/csv', [\App\Http\Controllers\Finance\CashbookController::class, 'csv'])->name('cashbook.csv');
        Route::get('/cashbook/export/pdf', [\App\Http\Controllers\Finance\CashbookController::class, 'pdf'])->name('cashbook.pdf');
            }); 
            
Route::middleware(['auth', 'password.changed'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'index'])
            ->middleware('permission:expenses.view')->name('expenses.index');
        Route::get('/expenses/create', [\App\Http\Controllers\Admin\ExpenseController::class, 'create'])
            ->middleware('permission:expenses.create')->name('expenses.create');
        Route::post('/expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'store'])
            ->middleware('permission:expenses.create')->name('expenses.store');
        Route::post('/expenses/{expense}/void', [\App\Http\Controllers\Admin\ExpenseController::class, 'void'])
            ->middleware('permission:expenses.void')->name('expenses.void');
        Route::get('/lpg-purchases/{purchase}/payments/create', [\App\Http\Controllers\Admin\LpgPurchasePaymentController::class, 'create'])
            ->middleware('permission:expenses.create')->name('lpg-purchase-payments.create');
        Route::post('/lpg-purchases/{purchase}/payments', [\App\Http\Controllers\Admin\LpgPurchasePaymentController::class, 'store'])
            ->middleware('permission:expenses.create')->name('lpg-purchase-payments.store');

    });
require __DIR__.'/auth.php';
