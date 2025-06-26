<?php

use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\areaController;
use App\Http\Controllers\Api\BoxController;
use App\Http\Controllers\Api\BranchOfficeController;
use App\Http\Controllers\Api\CargoController;
use App\Http\Controllers\Api\CarrierGuideController;
use App\Http\Controllers\Api\ComissionAgentController;
use App\Http\Controllers\Api\ContactInfoController;
use App\Http\Controllers\Api\CreditNoteController;
use App\Http\Controllers\Api\DetailGrtController;
use App\Http\Controllers\Api\DetailReceptionController;
use App\Http\Controllers\Api\DetailWorkerController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DriverExpenseController;
use App\Http\Controllers\Api\ExcelController;
use App\Http\Controllers\Api\ExpensesConceptController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\InstallmentController;
use App\Http\Controllers\Api\ModelFunctionalController;
use App\Http\Controllers\Api\MotiveController;
use App\Http\Controllers\Api\MovimentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PayInstallmentController;
use App\Http\Controllers\Api\PaymentConceptController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\ProgrammingController;
use App\Http\Controllers\Api\ReceptionController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SubContractController;
use App\Http\Controllers\Api\TypeCarroceriaController;
use App\Http\Controllers\Api\TypeCompanyController;
use App\Http\Controllers\Api\TypeofUserController;
use App\Http\Controllers\Api\UbigeoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Collection\CollectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::post('login', [UserController::class, 'login']);

Route::get('branchOffice', [BranchOfficeController::class, 'index']);
Route::get('branchOffice/{id}', [BranchOfficeController::class, 'show']);

Route::group(["middleware" => ["auth:sanctum"]], function () {

    //AUTHENTICATE
    Route::get('logout', [UserController::class, 'logout']);
    Route::get('authenticate', [UserController::class, 'authenticate']);

// SEARCH
    Route::get('searchByDni/{dni}', [UserController::class, 'searchByDni']);
    Route::get('searchByRuc/{ruc}', [UserController::class, 'searchByRuc']);

//CLIENTS
    Route::get('clients', [PersonController::class, 'index']);
    Route::get('legalEntity', [PersonController::class, 'legalEntity']);
    Route::get('naturalPerson', [PersonController::class, 'naturalPerson']);
    Route::post('clients', [PersonController::class, 'store']);
    Route::get('clients/{id}', [PersonController::class, 'show']);
    Route::put('clients/{id}', [PersonController::class, 'update']);
    Route::delete('clients/{id}', [PersonController::class, 'destroy']);
    Route::put('clients/{id}/changeState', [PersonController::class, 'changeState']);
    Route::get('personsWithDebt', [PersonController::class, 'personsWithDebt']);
    Route::get('clientsFiltter', [PersonController::class, 'indexFiltro']);

    // TYPEOF_USER
    Route::get('typeofUser', [TypeofUserController::class, 'index']);
    Route::post('typeofUser', [TypeofUserController::class, 'store']);
    Route::get('typeofUser/{id}', [TypeofUserController::class, 'show']);
    Route::put('typeofUser/{id}', [TypeofUserController::class, 'update']);
    Route::delete('typeofUser/{id}', [TypeofUserController::class, 'destroy']);
    Route::put('setAccess/{typeUserId}', [TypeofUserController::class, 'setAccess']);
    Route::get('getAccess/{id}', [TypeofUserController::class, 'getAccess']);

    // COMISSION_AGENT
    Route::get('comissionAgent', [ComissionAgentController::class, 'index']);
    Route::post('comissionAgent', [ComissionAgentController::class, 'store']);
    Route::get('comissionAgent/{id}', [ComissionAgentController::class, 'show']);
    Route::put('comissionAgent/{id}', [ComissionAgentController::class, 'update']);
    Route::delete('comissionAgent/{id}', [ComissionAgentController::class, 'destroy']);

    // BRANCH_OFFICE

    Route::post('branchOffice', [BranchOfficeController::class, 'store']);
    Route::put('branchOffice/{id}', [BranchOfficeController::class, 'update']);
    Route::delete('branchOffice/{id}', [BranchOfficeController::class, 'destroy']);

    // /RECEPTION
    Route::get('reception', [ReceptionController::class, 'index']);
    Route::get('reception/{id}', [ReceptionController::class, 'show']);
    Route::post('reception', [ReceptionController::class, 'store']);
    Route::delete('reception/{id}', [ReceptionController::class, 'destroy']);
    Route::put('reception/{id}', [ReceptionController::class, 'update']);

    // ADDRESS
    Route::get('address', [AddressController::class, 'index']);
    Route::get('addressForPerson/{idPersona}', [AddressController::class, 'addressForPerson']);

    Route::get('address/{id}', [AddressController::class, 'show']);
    Route::post('address', [AddressController::class, 'store']);
    Route::delete('address/{id}', [AddressController::class, 'destroy']);
    Route::put('address/{id}', [AddressController::class, 'update']);

    // PLACE
    Route::get('place', [PlaceController::class, 'index']);
    Route::get('place/{id}', [PlaceController::class, 'show']);
    Route::post('place', [PlaceController::class, 'store']);
    Route::delete('place/{id}', [PlaceController::class, 'destroy']);
    Route::put('place/{id}', [PlaceController::class, 'update']);

    // WORKER
    Route::get('worker', [WorkerController::class, 'index']);
    Route::get('worker/{id}', [WorkerController::class, 'show']);
    Route::post('worker', [WorkerController::class, 'store']);
    Route::delete('worker/{id}', [WorkerController::class, 'destroy']);
    Route::put('worker/{id}/changestatus', [WorkerController::class, 'change_status']);

    Route::put('worker/{id}', [WorkerController::class, 'update']);
    Route::get('worker/{id}/historyProgramming', [WorkerController::class, 'getWorkerHistory']);
    Route::post('worker/{id}', [WorkerController::class, 'createOrUpdate']);

    // CONTACT INFO
    Route::get('contactInfo', [ContactInfoController::class, 'index']);
    Route::get('contactInfo/{id}', [ContactInfoController::class, 'show']);
    Route::get('contactsForPerson/{idPersona}', [ContactInfoController::class, 'contactsForPerson']);

    Route::post('contactInfo', [ContactInfoController::class, 'store']);
    Route::delete('contactInfo/{id}', [ContactInfoController::class, 'destroy']);
    Route::put('contactInfo/{id}', [ContactInfoController::class, 'update']);

    // DETAIL RECEPTION
    Route::get('detailReception', [DetailReceptionController::class, 'index']);
    Route::get('detailReceptionWithoutProgramming', [DetailReceptionController::class,
        'indexWithoutProgramming']);
    Route::get('detailReception/{id}', [DetailReceptionController::class, 'show']);
    Route::get('detailReceptionForReception/{id}', [DetailReceptionController::class, 'showForReception']);
    Route::post('detailReception', [DetailReceptionController::class, 'store']);
    Route::delete('detailReception/{id}', [DetailReceptionController::class, 'destroy']);
    Route::put('detailReception/{id}', [DetailReceptionController::class, 'update']);

    // FLEET
    Route::get('fleet', [FleetController::class, 'index']);
    Route::get('fleet/{id}', [FleetController::class, 'show']);
    Route::post('fleet', [FleetController::class, 'store']);
    Route::delete('fleet/{id}', [FleetController::class, 'destroy']);
    Route::put('fleet/{id}', [FleetController::class, 'update']);

    // PLACE
    Route::get('motive', [MotiveController::class, 'index']);
    Route::get('motive/{id}', [MotiveController::class, 'show']);
    Route::post('motive', [MotiveController::class, 'store']);
    Route::delete('motive/{id}', [MotiveController::class, 'destroy']);
    Route::put('motive/{id}', [MotiveController::class, 'update']);

    // MODEL FUNCTIONAL
    Route::get('modelFunctional', [ModelFunctionalController::class, 'index']);
    Route::get('modelFunctional/{id}', [ModelFunctionalController::class, 'show']);
    Route::post('modelFunctional', [ModelFunctionalController::class, 'store']);
    Route::delete('modelFunctional/{id}', [ModelFunctionalController::class, 'destroy']);
    Route::put('modelFunctional/{id}', [ModelFunctionalController::class, 'update']);

    // VEHICLE
    Route::get('vehicleAll', [VehicleController::class, 'showAll']);
    Route::get('vehicle', [VehicleController::class, 'index']);
    Route::get('vehicle/{id}', [VehicleController::class, 'show']);
        Route::get('vehicle/{id}/docs', [VehicleController::class, 'getDocumentsByVehicleIdGrouped']);
    Route::post('vehicle', [VehicleController::class, 'store']);
    Route::delete('vehicle/{id}', [VehicleController::class, 'destroy']);
    Route::post('vehicle/{id}', [VehicleController::class, 'createOrUpdate']);
    Route::get('getVehicleHistory/{id}', [VehicleController::class, 'getVehicleHistory']);
    Route::get('vehicleExcel', [ExcelController::class, 'reporteVehiclesExcel']);

    // CARRIER GUIDE
    Route::get('carrierGuide', [CarrierGuideController::class, 'index']);
        Route::get('carrierGuide_export_excel', [CarrierGuideController::class, 'export_excel']);
    Route::get('carrierGuide/{id}', [CarrierGuideController::class, 'show']);
    Route::post('carrierGuide', [CarrierGuideController::class, 'store']);
    Route::delete('carrierGuide/{id}', [CarrierGuideController::class, 'destroy']);
    Route::put('carrierGuide/{id}', [CarrierGuideController::class, 'update']);
    Route::put('carrierGuide/{id}/status', [CarrierGuideController::class, 'updateStatus']);
Route::get('algoritmoanexos/{cadena}', [CarrierGuideController::class, 'algoritmoanexos']);
    Route::post('consultarstatus', [CarrierGuideController::class, 'getStatusFacturacion']);

    // PROGRAMMING
    Route::get('programming', [ProgrammingController::class, 'index']);
    Route::get('programming/{id}', [ProgrammingController::class, 'show']);
    Route::get('programmingLiquidado/{id}', [ProgrammingController::class, 'programmingLiquidado']);

    Route::post('programming', [ProgrammingController::class, 'store']);
    Route::delete('programming/{id}', [ProgrammingController::class, 'destroy']);
    Route::put('programming/{id}', [ProgrammingController::class, 'update']);
    Route::put('finishProgramming/{id}', [ProgrammingController::class, 'finishProgramming']);
    Route::get('getPlatformByVehicleId/{id}', [ProgrammingController::class, 'getPlatformByVehicleId']);
    Route::post('reprogramming/{id}', [ProgrammingController::class, 'reprogramming']);

    // DETAIL GRT
    Route::get('detailGrt', [DetailGrtController::class, 'index']);
    Route::get('detailGrt/{id}', [DetailGrtController::class, 'show']);
    Route::post('detailGrt', [DetailGrtController::class, 'store']);
    Route::delete('detailGrt/{id}', [DetailGrtController::class, 'destroy']);
    Route::put('detailGrt/{id}', [DetailGrtController::class, 'update']);

    // DETAIL WORKER
    Route::get('detailWorker', [DetailWorkerController::class, 'index']);
    Route::get('detailWorker/{id}', [DetailWorkerController::class, 'show']);
    Route::get('detailWorkerForProgramming/{id}', [DetailWorkerController::class, 'showForProgramming']);
    Route::post('detailWorker', [DetailWorkerController::class, 'store']);
    Route::delete('detailWorker/{id}', [DetailWorkerController::class, 'destroy']);
    Route::put('detailWorker/{id}', [DetailWorkerController::class, 'update']);

    // DETAIL WORKER
    Route::get('driverExpense', [DriverExpenseController::class, 'index']);
    Route::get('driverExpense/{id}', [DriverExpenseController::class, 'show']);
    Route::post('driverExpense', [DriverExpenseController::class, 'store']);
    Route::delete('driverExpense/{id}', [DriverExpenseController::class, 'destroy']);
    Route::put('driverExpense/{id}', [DriverExpenseController::class, 'update']);
    Route::post('devolverMontoaCaja', [DriverExpenseController::class, 'devolverMontoaCaja']);

    // DETAIL AREA
    Route::get('area', [areaController::class, 'index']);
    Route::get('area/{id}', [areaController::class, 'show']);
    Route::post('area', [areaController::class, 'store']);
    Route::delete('area/{id}', [areaController::class, 'destroy']);
    Route::put('area/{id}', [areaController::class, 'update']);

    // DETAIL SUBCONTRACT
    Route::get('subcontract', [SubContractController::class, 'index']);
    Route::get('subcontract/{id}', [SubContractController::class, 'show']);
    Route::post('subcontract', [SubContractController::class, 'store']);
    Route::delete('subcontract/{id}', [SubContractController::class, 'destroy']);
    Route::put('subcontract/{id}', [SubContractController::class, 'update']);

    Route::get('dataCollection', [CollectionController::class, 'index']);

    // BOXES
    Route::get('box', [BoxController::class, 'index']);
    Route::get('boxAll', [BoxController::class, 'indexAll']);
    Route::get('boxByBranch/{id}', [BoxController::class, 'getBoxByBrandId']);
    // /Route::get('boxByBranch/{id}', [BoxController::class, 'indexNotAssigned']);
    Route::get('box/{id}', [BoxController::class, 'show']);
    Route::put('box/{id}/updateuser', [BoxController::class, 'update_usuario']);
    Route::post('box', [BoxController::class, 'store']);
    Route::delete('box/{id}', [BoxController::class, 'destroy']);
    Route::put('box/{id}', [BoxController::class, 'update']);

    // MOVIMENT
    Route::get('moviment', [MovimentController::class, 'index']);
    Route::get('moviment/last/{idBox}', [MovimentController::class, 'showLastMovPayment']);
    Route::get('moviment/{id}', [MovimentController::class, 'show']);
    Route::post('moviment', [MovimentController::class, 'store']);
    Route::post('movimentAperturaCierre', [MovimentController::class, 'aperturaCierre']);
    Route::delete('moviment/{id}', [MovimentController::class, 'destroy']);
    Route::put('moviment/{id}', [MovimentController::class, 'update']);
    Route::get('validateBox', [MovimentController::class, 'validateBox']);
    Route::get('showAperturaMovements', [MovimentController::class, 'showAperturaMovements']);

    // INSTALLMENT
    Route::get('installment', [InstallmentController::class, 'index']);
    Route::get('installment/{id}', [InstallmentController::class, 'show']);
    Route::post('installment', [InstallmentController::class, 'store']);
    Route::delete('installment/{id}', [InstallmentController::class, 'destroy']);
    Route::put('installment/{id}', [InstallmentController::class, 'update']);
    Route::put('installment/{id}/pay', [InstallmentController::class, 'payInstallment']);
    Route::post('payMasivo/{id}', [InstallmentController::class, 'payMasivo']);

    Route::delete('payinstallment/{id}', [PayInstallmentController::class, 'destroy']);
    Route::post('generateMovBox/{id}', [PayInstallmentController::class, 'generateMovBox']);

    // PAYMENT CONCEPT
    Route::get('paymentConcept', [PaymentConceptController::class, 'index']);
    Route::get('paymentConcept/{id}', [PaymentConceptController::class, 'show']);
    Route::post('paymentConcept', [PaymentConceptController::class, 'store']);
    Route::delete('paymentConcept/{id}', [PaymentConceptController::class, 'destroy']);
    Route::put('paymentConcept/{id}', [PaymentConceptController::class, 'update']);

    //REPORTS
    Route::get('saveguia/{id}', [PdfController::class, 'guia'])->name('guia');
    Route::get('manifiesto/{id}', [PdfController::class, 'manifiesto'])->name('manifiesto');
    Route::get('manifiestoConductor/{id}', [PdfController::class, 'manifiestoConductor'])->name('manifiestoConductor');

    Route::get('reportCaja', [PdfController::class, 'reportCaja'])->name('reportCaja');
    Route::get('reportCajaExcel', [PdfController::class, 'reporteIngresosExcel'])->name('reportCajaExcel');

    Route::get('reporteCuentasPorCobrarExcel', [PdfController::class, 'reporteCuentasPorCobrarExcel'])->name('reportCajaExcel');
    Route::get('salesExcel', [ExcelController::class, 'reporteVentasExcel'])->name('reportVentasExcel');
    Route::get('reporteRecepcionesExcel', [ReceptionController::class, 'reporteRecepcionesExcel'])->name('reporteRecepcionesExcel');

    Route::get('reporteDocumentsExcel', [ExcelController::class, 'reporteDocumentsExcel'])->name('reporteDocumentsExcel');
    Route::get('reporteManifiestoExcel/{id}', [ExcelController::class, 'reporteManifiestoExcel'])->name('reporteManifiestoExcel');
    Route::get('reporteDriverConceptExcel/{id}', [ExcelController::class, 'reporteDriverConceptExcel'])->name('reporteDriverConceptExcel');
    Route::get('reporteGuides', [ExcelController::class, 'guidesExcel'])->name('guidesExcel');
    Route::put('changeStatusFacturado/{id}', [CarrierGuideController::class, 'changeStatusFacturacion']);

    // /Route::get('reporteReception/{id}', [PdfController::class, 'reporteReception'])->name('reporteReception');

    //USERS
    Route::get('user', [UserController::class, 'index']);
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::post('user', [UserController::class, 'store']);
    Route::delete('user/{id}', [UserController::class, 'destroy']);
    Route::put('user/{id}', [UserController::class, 'update']);
    //USERS
    Route::get('access', [AccessController::class, 'index']);

    // MOVIMENT
    Route::get('sale', [VentaController::class, 'index']);
    Route::get('saleIdNumber', [VentaController::class, 'saleIdNumber']);
    Route::get('receptionWithoutSale', [VentaController::class, 'receptionWithoutSale']);
    Route::get('getArchivosDocument/{id}/{tipodocumento}', [VentaController::class, 'getArchivosDocument']);

    Route::get('declararBoletaFactura/{id}/{idtipodocumento}', [VentaController::class, 'declararBoletaFactura']);
    Route::get('declararBoletaFacturaById/{id}/{idtipodocumento}', [VentaController::class, 'declararBoletaFacturaById']);
    Route::get('declararVentasHoy', [VentaController::class, 'declararVentasHoy']);
    Route::get('declararNCHoy', [CreditNoteController::class, 'declararNCHoy']);
    Route::get('declararNotaCredito/{id}', [CreditNoteController::class, 'declararNotaCredito']);
    Route::get('declararGuia/{id}', [CarrierGuideController::class, 'declararGuia']);
    Route::get('declararGuiaBack', [CarrierGuideController::class, 'declararGuiaBack']);
    


    Route::get('saleWithoutCreditNote', [VentaController::class, 'saleWithoutCreditNote']);
    Route::get('salesbynumber', [VentaController::class, 'salesbynumber']);
    Route::post('updateNroVenta/{id}', [VentaController::class, 'updateNroVenta']);

    Route::get('sale/{id}', [VentaController::class, 'show']);
    Route::post('sale', [VentaController::class, 'store']);
    Route::delete('sale/{id}', [VentaController::class, 'destroy']);
    Route::put('saleManual/{id}', [VentaController::class, 'updateManual']);
    Route::put('saleReceptions/{id}', [VentaController::class, 'updateReceptions']);
    Route::put('saleUpdateMontos/{id}', [VentaController::class, 'updateMontos']);

    Route::post('saleWithReceptions', [VentaController::class, 'storeWithReceptions']);
    Route::post('saleManual', [VentaController::class, 'storeManual']);
    Route::get('getNextCorrelative/{prefix}/{id}', [VentaController::class, 'getNextCorrelative']);
    // CREDIT NOTE
    Route::get('creditNote', [CreditNoteController::class, 'index']);
    Route::get('creditNote/{id}', [CreditNoteController::class, 'show']);
    Route::post('creditNote', [CreditNoteController::class, 'store']);
    Route::delete('creditNote/{id}', [CreditNoteController::class, 'destroy']);
    Route::put('creditNote/{id}', [CreditNoteController::class, 'update']);

    // EXPENSES CONCEPT
    Route::get('expensesConcept', [ExpensesConceptController::class, 'index']);
    Route::get('expensesConcept/{id}', [ExpensesConceptController::class, 'show']);
    Route::post('expensesConcept', [ExpensesConceptController::class, 'store']);
    Route::delete('expensesConcept/{id}', [ExpensesConceptController::class, 'destroy']);
    Route::put('expensesConcept/{id}', [ExpensesConceptController::class, 'update']);

    // /ROUTES PLACE
    Route::get('routes', [RouteController::class, 'index']);
    Route::get('routesFather', [RouteController::class, 'indexRoutesFather']);

    Route::get('routes/{id}', [RouteController::class, 'show']);
    Route::post('routes', [RouteController::class, 'store']);
    Route::post('routeStore', [RouteController::class, 'storeRoute']);
    Route::get('searchRoute', [RouteController::class, 'searchRoute']);
    Route::post('addSubRoute', [RouteController::class, 'addSubRoute']);

    Route::post('routes/assign-parent', [RouteController::class, 'assignParentRoute']);

    Route::delete('routes/{id}', [RouteController::class, 'destroy']);
    Route::put('routes/{id}', [RouteController::class, 'update']);

    Route::get('departments', [UbigeoController::class, 'indexDepartments']);
    Route::get('provinces/{departmentId}', [UbigeoController::class, 'indexProvinces']);
    Route::get('districts/{provinceId}', [UbigeoController::class, 'indexDistricts']);
    Route::get('ubigeos', [UbigeoController::class, 'ubigeos']);

    // TYPE COMPANY
    Route::get('typecompany', [TypeCompanyController::class, 'index']);
    Route::get('typecompany/{id}', [TypeCompanyController::class, 'show']);
    Route::post('typecompany', [TypeCompanyController::class, 'store']);
    Route::delete('typecompany/{id}', [TypeCompanyController::class, 'destroy']);
    Route::put('typecompany/{id}', [TypeCompanyController::class, 'update']);

    // TYPE CARROCERY
    Route::get('typecarroceria', [TypeCarroceriaController::class, 'index']);
    Route::get('typecarroceria/{id}', [TypeCarroceriaController::class, 'show']);
    Route::post('typecarroceria', [TypeCarroceriaController::class, 'store']);
    Route::delete('typecarroceria/{id}', [TypeCarroceriaController::class, 'destroy']);
    Route::put('typecarroceria/{id}', [TypeCarroceriaController::class, 'update']);

    // TYPE CARROCERY
    Route::get('document', [DocumentController::class, 'index']);
    // /Route::get('indexReport', [DocumentController::class, 'indexReport']);

    Route::get('document/{id}', [DocumentController::class, 'show']);
    Route::post('document', [DocumentController::class, 'store']);
    Route::delete('document/{id}', [DocumentController::class, 'destroy']);
    Route::put('document/{id}', [DocumentController::class, 'update']);
    Route::post('document/{id}', [DocumentController::class, 'createOrUpdate']);

    //CARGOS
    Route::get('cargosByReception/{id}', [CargoController::class, 'indexByReception']);
    Route::post('cargo', [CargoController::class, 'store']);
    Route::delete('cargo/{id}', [CargoController::class, 'destroy']);

    Route::get('notification', [NotificationController::class, 'index']);
    Route::get('report-workers', [WorkerController::class, 'index_export_excel']);

    require __DIR__ . '/Api/CarrierApi.php';            //CARRIER GUIDE
    require __DIR__ . '/Api/SaleApi.php';               //SALES
    require __DIR__ . '/Api/CargaDocumentApi.php';      //DOCUMENTO CARGA
    require __DIR__ . '/Api/TarifarioApi.php';          //TARIFARIO
    require __DIR__ . '/Api/UnityApi.php';              //UNIDAD
    require __DIR__ . '/Api/ProductApi.php';            //PRODUCT
    require __DIR__ . '/Api/BankApi.php';               //BANK
    require __DIR__ . '/Api/TransactionConceptApi.php'; //TransactionConcept
    require __DIR__ . '/Api/BankAccountApi.php';        //BANK ACCOUNT
    require __DIR__ . '/Api/BankMovementApi.php';       //BANK MOVIMENT
    require __DIR__ . '/Api/DriverExpenseApi.php';      //DRIVER EXPENSE

    require __DIR__ . '/Api/ProgrammingApi.php';  //DRIVER EXPENSE
    require __DIR__ . '/Api/TypeDocumentApi.php'; //TYPE DOCUMENT
    require __DIR__ . '/Api/PayableApi.php';      //TYPE DOCUMENT

    require __DIR__ . '/Api/TallerApi.php';        //TALLER
    require __DIR__ . '/Api/CategoryApi.php';      //CATEGORY
    require __DIR__ . '/Api/RepuestoApi.php';      //REPUESTO
    require __DIR__ . '/Api/CheckListItemApi.php'; //CHECK LIST ITEM

    require __DIR__ . '/Api/AlmacenApi.php';   //ALMACEN
    require __DIR__ . '/Api/SeccionApi.php';   //SECCION
    require __DIR__ . '/Api/CheckListApi.php'; //CHECK LIST

    require __DIR__ . '/Api/MaintanenceApi.php';          //CHECK LIST
    require __DIR__ . '/Api/MaintanenceDetailApi.php';    //CHECK LIST
    require __DIR__ . '/Api/ServiceApi.php';              //SERVICE
    require __DIR__ . '/Api/WorkerApi.php';               //WORKER
    require __DIR__ . '/Api/MovimentApi.php';             //MOVIMENT
    require __DIR__ . '/Api/OrderCompraApi.php';          //ORDER COMPRA
    require __DIR__ . '/Api/OrderCompraDetailApi.php';    //ORDER COMPRA DETAIL
    require __DIR__ . '/Api/MovimentCompraApi.php';       //MOVIMENT COMPRA
    require __DIR__ . '/Api/MovimentCompraDetailApi.php'; //MOVIMENT COMPRA DETAIL


    
});
