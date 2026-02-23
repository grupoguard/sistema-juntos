<?php

use App\Http\Controllers\AditionalController;
use App\Http\Controllers\AnomalyCodeController;
use App\Http\Controllers\AtualizarExcelTXT;
use App\Http\Controllers\CalendarEdpController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ComissionController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DependentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EvidenceDocumentController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\LogMovementController;
use App\Http\Controllers\LogRegisterController;
use App\Http\Controllers\MoveCodeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PartnerCategoryController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerPlanController;
use App\Http\Controllers\PlanilhaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\ReturnCodeController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViaCepController;
use App\Http\Controllers\DashboardController;
use App\Imports\PlanilhaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->hasAnyRole(['ADMIN', 'COOP', 'SELLER', 'FINANCIAL'])) {
            return redirect()->route('admin.dashboard');
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Sem permissão de acesso.');
    }

    return redirect('/login');
});

Route::group([
    'middleware' => ['auth'], 
    'prefix' => 'admin', 
    'as' => 'admin.'
    ], function () {
	
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view-dashboard')
        ->name('dashboard');
    
        Route::get('orders/financial-divergences', [OrderController::class, 'financialDivergences'])
            ->middleware('permission:orders.edit') // ou permission:reports.financial se preferir
            ->name('orders.financial-divergences');

	// Adicionais
    Route::resource('aditionals', AditionalController::class)
    ->middlewareFor(['index', 'show'], 'permission:aditionals.view')
    ->middlewareFor(['create', 'store'], 'permission:aditionals.create')
    ->middlewareFor(['edit', 'update'], 'permission:aditionals.edit')
    ->middlewareFor(['destroy'], 'permission:aditionals.delete');

    // Códigos de Anomalia
    Route::resource('anomaly_codes', AnomalyCodeController::class)
        ->middlewareFor(['index', 'show'], 'permission:anomaly.view')
        ->middlewareFor(['create', 'store'], 'permission:anomaly.create')
        ->middlewareFor(['edit', 'update'], 'permission:anomaly.edit')
        ->middlewareFor(['destroy'], 'permission:anomaly.delete');

    // Calendário EDP
    Route::resource('calendar_edp', CalendarEdpController::class)
        ->middlewareFor(['index', 'show'], 'permission:calendar.view')
        ->middlewareFor(['create', 'store'], 'permission:calendar.create')
        ->middlewareFor(['edit', 'update'], 'permission:calendar.edit')
        ->middlewareFor(['destroy'], 'permission:calendar.delete');


    // Clientes
    Route::resource('clients', ClientController::class)
        ->middlewareFor(['index', 'show'], 'permission:clients.view')
        ->middlewareFor(['create', 'store'], 'permission:clients.create')
        ->middlewareFor(['edit', 'update'], 'permission:clients.edit')
        ->middlewareFor(['destroy'], 'permission:clients.delete');

    // Comissões
    Route::resource('comission', ComissionController::class)
        ->middlewareFor(['index', 'show'], 'permission:comission.view')
        ->middlewareFor(['create', 'store'], 'permission:comission.create')
        ->middlewareFor(['edit', 'update'], 'permission:comission.edit')
        ->middlewareFor(['destroy'], 'permission:comission.delete');

    // Dependentes
    Route::resource('dependents', DependentController::class)
        ->middlewareFor(['index', 'show'], 'permission:dependents.view')
        ->middlewareFor(['create', 'store'], 'permission:dependents.create')
        ->middlewareFor(['edit', 'update'], 'permission:dependents.edit')
        ->middlewareFor(['destroy'], 'permission:dependents.delete');

    // Funcionários
    Route::resource('employees', EmployeeController::class)
        ->middlewareFor(['index', 'show'], 'permission:employees.view')
        ->middlewareFor(['create', 'store'], 'permission:employees.create')
        ->middlewareFor(['edit', 'update'], 'permission:employees.edit')
        ->middlewareFor(['destroy'], 'permission:employees.delete');

    // Documentos de Evidência (somente index e show)
    Route::resource('evidence_documents', EvidenceDocumentController::class)->only(['index', 'show'])
        ->middlewareFor(['index', 'show'], 'permission:evidences.view');

    // Registros Financeiros
    Route::resource('financial', FinancialController::class)
        ->middlewareFor(['index', 'show'], 'permission:financial.view')
        ->middlewareFor(['create', 'store'], 'permission:financial.create')
        ->middlewareFor(['edit', 'update'], 'permission:financial.edit')
        ->middlewareFor(['destroy'], 'permission:financial.delete');
    
    Route::get('financial/reports', [FinancialController::class, 'reports'])
        ->middleware('permission:financial.reports')
        ->name('financial.reports');

    Route::get('financial/export', [FinancialController::class, 'export'])
        ->middleware('permission:financial.export')
        ->name('financial.export');

    // Cooperativas
    Route::resource('groups', GroupController::class)
        ->middlewareFor(['index', 'show'], 'permission:groups.view')
        ->middlewareFor(['create', 'store'], 'permission:groups.create')
        ->middlewareFor(['edit', 'update'], 'permission:groups.edit')
        ->middlewareFor(['destroy'], 'permission:groups.delete');

    // Logs (apenas visualização)
    Route::resource('log_movement', LogMovementController::class)->only(['index', 'show']);
    Route::resource('log_register', LogRegisterController::class)->only(['index', 'show']);

    // Códigos de Movimento
    Route::resource('move_codes', MoveCodeController::class);

    // Listar Atualizações EDP
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('edp', [ReportsController::class, 'edp'])->name('edp');
        Route::get('financial', [ReportsController::class, 'financial'])->name('financial');
    });

    // Pedidos e seus detalhes
    Route::resource('orders', OrderController::class)
        ->middlewareFor(['index', 'show'], 'permission:orders.view')
        ->middlewareFor(['create', 'store'], 'permission:orders.create')
        ->middlewareFor(['edit', 'update'], 'permission:orders.edit')
        ->middlewareFor(['destroy'], 'permission:orders.delete');

    Route::get('order-easy-form', [OrderController::class, 'easyform'])
        ->middleware('auth', 'permission:orders.create')
        ->name('orders.easy-create');

    Route::resource('order_aditionals', OrderController::class);
    Route::resource('order_dependents', OrderController::class);
    Route::resource('order_prices', OrderController::class);

    // Parceiros e Categorias
    Route::resource('partners', PartnerController::class)
        ->middlewareFor(['index', 'show'], 'permission:partner.view')
        ->middlewareFor(['create', 'store'], 'permission:partner.create')
        ->middlewareFor(['edit', 'update'], 'permission:partner.edit')
        ->middlewareFor(['destroy'], 'permission:partner.delete');

    Route::resource('partner_categories', PartnerCategoryController::class)
        ->middlewareFor(['index', 'show'], 'permission:partner_categories.view')
        ->middlewareFor(['create', 'store'], 'permission:orders.create')
        ->middlewareFor(['edit', 'update'], 'permission:orders.edit')
        ->middlewareFor(['destroy'], 'permission:partner_categories.cancel');

    Route::resource('partner_plans', PartnerPlanController::class)
        ->middlewareFor(['index', 'show'], 'permission:partner_plans.view')
        ->middlewareFor(['create', 'store'], 'permission:partner_plans.create')
        ->middlewareFor(['edit', 'update'], 'permission:partner_plans.edit')
        ->middlewareFor(['destroy'], 'permission:partner_plans.delete');

    // Produtos e suas vinculações
    Route::resource('products', ProductController::class)
        ->middlewareFor(['index', 'show'], 'permission:products.view')
        ->middlewareFor(['create', 'store'], 'permission:products.create')
        ->middlewareFor(['edit', 'update'], 'permission:products.edit')
        ->middlewareFor(['destroy'], 'permission:products.delete');
    
    Route::resource('product-additionals', ProductController::class)
        ->middlewareFor(['index', 'show'], 'permission:product_aditionals.view')
        ->middlewareFor(['create', 'store'], 'permission:product_aditionals.create')
        ->middlewareFor(['edit', 'update'], 'permission:product_aditionals.edit')
        ->middlewareFor(['destroy'], 'permission:product_aditionals.delete');
        
    Route::post('products/{product}/aditionals', [ProductController::class, 'attachAditional'])->name('products.attachAditional');

    // Códigos de Retorno
    Route::resource('return_codes', ReturnCodeController::class)
        ->middlewareFor(['index', 'show'], 'permission:return.view')
        ->middlewareFor(['create', 'store'], 'permission:return.create')
        ->middlewareFor(['edit', 'update'], 'permission:return.edit')
        ->middlewareFor(['destroy'], 'permission:return.delete');

    // Vendedores
    Route::resource('sellers', SellerController::class)
        ->middlewareFor(['index', 'show'], 'permission:sellers.view')
        ->middlewareFor(['create', 'store'], 'permission:sellers.create')
        ->middlewareFor(['edit', 'update'], 'permission:sellers.edit')
        ->middlewareFor(['destroy'], 'permission:sellers.delete');

    // Usuários
    Route::resource('users', UserController::class)
        ->middlewareFor(['index', 'show'], 'permission:users.view')
        ->middlewareFor(['create', 'store'], 'permission:users.create')
        ->middlewareFor(['edit', 'update'], 'permission:users.edit')
        ->middlewareFor(['destroy'], 'permission:users.delete');

    // Usuários
    Route::get('profile', function () {
		return view('profile');
	})->name('profile');

    Route::post('profile', [ProfileController::class, 'store'])->name('profile.store');

	Route::get('billing', function () {
		return view('billing');
	})->name('billing');

	Route::get('user-management', function () {
		return view('laravel-examples/user-management');
	})->name('user-management');

    Route::get('static-sign-in', function () {
		return view('static-sign-in');
	})->name('sign-in');

    Route::get('static-sign-up', function () {
		return view('static-sign-up');
	})->name('sign-up');

    Route::post('/logout', [SessionsController::class, 'destroy'])->name('logout');

	Route::get('/user-profile', [InfoUserController::class, 'create']);
	Route::post('/user-profile', [InfoUserController::class, 'store']);

    Route::get('/import-csv', [CsvImportController::class, 'showForm'])->name('csv.form');
    Route::post('/import-csv', [CsvImportController::class, 'import'])->name('csv.import');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
	Route::get('/login/forgot-password', [ResetController::class, 'create']);
	Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
	Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
	Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

Route::get('/login', function () {
    return view('session/login-session');
})
->middleware('guest')
->name('login');

Route::prefix('api')->group(function () {
    Route::get('/buscar-cep/{cep}', [ViaCepController::class, 'buscarCep']);
});

Route::get('/evidences', 
    [PlanilhaController::class, 'index']
)->name('evidences');

Route::get('/evidences/disparo', 
	[PlanilhaController::class, 'disparo']
)->name('evidences.disparo');

Route::get('/evidences/processamento', 
	[PlanilhaController::class, 'processamento']
)->name('evidences.processamento');

Route::post('/evidences/send', 
	[PlanilhaController::class, 'upload']
)->name('evidences.send');

Route::post('/evidences/gerartxt', 
	[PlanilhaController::class, 'gerarTxt']
)->name('evidences.gerartxt');

Route::get('/evidences/download-feedback', [PlanilhaController::class, 'downloadFeedback'])->name('evidences.downloadFeedback');

Route::get('/atualizar-excel', [AtualizarExcelTXT::class, 'index'])->name('atualizar.excel');

Route::post('/atualizar-excel/upload', [AtualizarExcelTXT::class, 'upload'])->name('atualizar.excel.upload');

Route::get('/access-denied', function() {
    return view('access-denied');
})->name('access.denied');