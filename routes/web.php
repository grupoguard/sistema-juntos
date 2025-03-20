<?php

use App\Http\Controllers\AditionalController;
use App\Http\Controllers\AnomalyCodeController;
use App\Http\Controllers\AtualizarExcelTXT;
use App\Http\Controllers\CalendarEdpController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ComissionController;
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
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\ReturnCodeController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\UserController;
use App\Imports\PlanilhaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
	
    Route::get('/', [HomeController::class, 'home']);

	Route::get('dashboard', function () {
        return view('pages.admin.dashboard'); // Caminho correto para a view
    })->name('dashboard');
	// Adicionais
    Route::resource('aditionals', AditionalController::class);

    // Códigos de Anomalia
    Route::resource('anomaly_codes', AnomalyCodeController::class);

    // Calendário EDP
    Route::resource('calendar_edp', CalendarEdpController::class);

    // Clientes
    Route::resource('clients', ClientController::class);

    // Comissões
    Route::resource('comission', ComissionController::class);

    // Dependentes
    Route::resource('dependents', DependentController::class);

    // Funcionários
    Route::resource('employees', EmployeeController::class);

    // Documentos de Evidência (somente index e show)
    Route::resource('evidence_documents', EvidenceDocumentController::class)->only(['index', 'show']);

    // Registros Financeiros
    Route::resource('financial', FinancialController::class);

    // Cooperativas
    Route::resource('groups', GroupController::class);

    // Logs (apenas visualização)
    Route::resource('log_movement', LogMovementController::class)->only(['index', 'show']);
    Route::resource('log_register', LogRegisterController::class)->only(['index', 'show']);

    // Códigos de Movimento
    Route::resource('move_codes', MoveCodeController::class);

    // Pedidos e seus detalhes
    Route::resource('orders', OrderController::class);
    Route::resource('order_aditionals', OrderController::class);
    Route::resource('order_dependents', OrderController::class);
    Route::resource('order_prices', OrderController::class);

    // Parceiros e Categorias
    Route::resource('partners', PartnerController::class);
    Route::resource('partner_categories', PartnerCategoryController::class);
    Route::resource('partner_plans', PartnerPlanController::class);

    // Produtos e suas vinculações
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/aditionals', [ProductController::class, 'attachAditional'])->name('products.attachAditional');

    // Códigos de Retorno
    Route::resource('return_codes', ReturnCodeController::class);

    // Vendedores
    Route::resource('sellers', SellerController::class);

    // Usuários
    Route::resource('users', UserController::class);

	Route::get('billing', function () {
		return view('billing');
	})->name('billing');

	Route::get('profile', function () {
		return view('profile');
	})->name('profile');

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
    Route::get('/login', function () {
		return view('dashboard');
	})->name('sign-up');
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
})->name('login');

/* 
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
*/