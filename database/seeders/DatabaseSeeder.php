<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Address;
use App\Models\Area;
use App\Models\BranchOffice;
use App\Models\ContactInfo;
use App\Models\GroupMenu;
use App\Models\ModelFunctional;
use App\Models\Person;
use App\Models\Reception;
use App\Models\Subcontract;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(DepartmentSeeder::class);
        // $this->call(ProvinceSeeder::class);
        // Datos AREA
        $branch_office = [
            ['id' => '1', 'name' => 'Sucursal A', 'location' => 'Ciudad X', 'address' => 'Calle 123', 'state' => '1'],
            ['id' => '2', 'name' => 'Sucursal B', 'location' => 'Ciudad Y', 'address' => 'Calle 124', 'state' => '1'],
        ];

        // Iterar sobre los registros
        foreach ($branch_office as $branch) {
            // Buscar el registro por su ID
            $branch1 = BranchOffice::find($branch['id']);

            // Si el area existe, actualizarlo; de lo contrario, crear uno nuevo
            if ($branch1) {
                $branch1->update($branch);

            } else {
                BranchOffice::create($branch);

            }
        }
        $this->call(BoxesSeeder::class);
        // Datos
        $pople = [
            ['id' => '1', 'typeofDocument' => 'dni',
                'documentNumber' => '12345658', 'names' => 'admin',
                'fatherSurname' => 'apellido P', 'motherSurname' =>
                'apeliddo M', 'businessName' => null,
                'birthDate' => '2002-03-09',
                'state' => '1', 'branchOffice_id' => '1'],
            ['id' => '2', 'typeofDocument' => 'dni',
                'documentNumber' => '00000000', 'names' => 'VARIOS',
                'fatherSurname' => '', 'motherSurname' =>
                '', 'businessName' => null,
                'birthDate' => '2002-03-09',
                'state' => '1', 'branchOffice_id' => '1'],
            [
                'id' => '3',
                'typeofDocument' => 'dni',
                'documentNumber' => '13456784',
                'names' => 'Luis Alberto',
                'fatherSurname' => 'Gonzales',
                'motherSurname' => 'López',
                'businessName' => null,
                'birthDate' => '1990-05-15',
                'state' => '1',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '4',
                'typeofDocument' => 'dni',
                'documentNumber' => '87654321',
                'names' => 'Miguel',
                'fatherSurname' => 'Guevara',
                'motherSurname' => 'Cajusol',
                'businessName' => null,
                'birthDate' => '1985-11-30',
                'state' => '1',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '5',
                'typeofDocument' => 'dni',
                'documentNumber' => '23456789',
                'names' => 'Jorge Luis',
                'fatherSurname' => 'Pérez',
                'motherSurname' => 'Chávez',
                'businessName' => null,
                'birthDate' => '1988-07-20',
                'state' => '1',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '6',
                'typeofDocument' => 'dni',
                'documentNumber' => '34567890',
                'names' => 'Ana Lucía',
                'fatherSurname' => 'García',
                'motherSurname' => 'Torres',
                'businessName' => null,
                'birthDate' => '1992-04-18',
                'state' => '1',
                'branchOffice_id' => '1',
            ],

            ['id' => '7', 'typeofDocument' => 'ruc',
                'documentNumber' => '10123456789', 'names' => null,
                'fatherSurname' => null, 'motherSurname' => null,
                'businessName' => 'company SAC', 'birthDate' => '2002-03-10',
                'state' => '1', 'branchOffice_id' => '1'],
        ];

        // Iterar sobre los registros
        foreach ($pople as $person) {
            // Buscar el registro por su ID
            $person1 = Person::withTrashed()->find($person['id']);

            // Si el registro existe, actualizarlo; de lo contrario, crear uno nuevo
            if (!$person1 == null) {
                $person1->update($person);

            } else {
                Person::create($person);

            }
        }

        // Datos AREA
        $areas = [
            ['id' => '1', 'name' => 'Area 1', 'state' => '1'],
            ['id' => '2', 'name' => 'Area 2', 'state' => '1'],

        ];

        // Iterar sobre los registros
        foreach ($areas as $area) {
            // Buscar el registro por su ID
            $area1 = Area::withTrashed()->find($area['id']);

            // Si el area existe, actualizarlo; de lo contrario, crear uno nuevo
            if ($area1) {
                $area1->update($area);

            } else {
                Area::create($area);

            }
        }

        // Datos worker
        $startDate = Carbon::createFromFormat('d/m/Y', '1/01/2023')->format('Y-m-d');
        $workers = [
            ['id' => '1', 'district_id' => 1, 'code' => '123', 'department' => 'Department A',
                'province' => 'Province A',
                'district' => 'District A', 'maritalStatus' => 'Single',
                'levelInstitution' => 'University',
                'occupation' => 'Administrador',
                'startDate' => $startDate, 'endDate' => null,
                'person_id' => '3', 'area_id' => '1',
                'branchOffice_id' => '1'],
            ['id' => '2', 'district_id' => 1, 'code' => '123', 'department' => 'Department A',
                'province' => 'Province A',
                'district' => 'District A', 'maritalStatus' => 'Single',
                'levelInstitution' => 'University',
                'occupation' => 'Conductor',
                'startDate' => $startDate, 'endDate' => null,
                'person_id' => '4', 'area_id' => '1',
                'branchOffice_id' => '1'],
            ['id' => '3', 'district_id' => 1, 'code' => '123', 'department' => 'Department A',
                'province' => 'Province A',
                'district' => 'District A', 'maritalStatus' => 'Single',
                'levelInstitution' => 'University',
                'occupation' => 'Cajero',
                'startDate' => $startDate, 'endDate' => null,
                'person_id' => '5', 'area_id' => '1',
                'branchOffice_id' => '1'],
            ['id' => '4', 'district_id' => 1, 'code' => '123', 'department' => 'Department A',
                'province' => 'Province A',
                'district' => 'District A', 'maritalStatus' => 'Single',
                'levelInstitution' => 'University',
                'occupation' => 'Conductor',
                'startDate' => $startDate, 'endDate' => null,
                'person_id' => '6', 'area_id' => '1',
                'branchOffice_id' => '1'],

        ];

        // Iterar sobre los registros
        foreach ($workers as $worker) {

            // Buscar el registro por su ID
            $worker1 = Worker::withTrashed()->find($worker['id']);

            // Si el area existe, actualizarlo; de lo contrario, crear uno nuevo
            if ($worker1) {
                $worker1->update($worker);
            } else {
                Worker::create($worker);
            }
        }

        $type_ofe_users = [
            ['id' => '1', 'name' => 'Administrador Backend', 'guard_name' => 'web'],
            ['id' => '2', 'name' => 'Administrador Transporte', 'guard_name' => 'web'],
            ['id' => '3', 'name' => 'Trabajador', 'guard_name' => 'web'],
            ['id' => '4', 'name' => 'Cajero', 'guard_name' => 'web'],
        ];

        foreach ($type_ofe_users as $type_user) {
            // Buscar el registro por su ID
            $typeOfuser1 = Role::find($type_user['id']);
            Role::create($type_user);
        }

        $array = [
            ['id' => '1', 'name' => 'Operaciones', 'icon' => 'user_circle'],
            ['id' => '2', 'name' => 'Programaciones', 'icon' => 'transaksi'],
            ['id' => '3', 'name' => 'Caja', 'icon' => 'perusahaan'],
            ['id' => '4', 'name' => 'Usuario', 'icon' => 'pusatunduhdata'],
            ['id' => '5', 'name' => 'Configuraciones', 'icon' => 'config'],
        ];

        foreach ($array as $object) {
            $typeOfuser1 = GroupMenu::find($object['id']);
            if ($typeOfuser1) {
                $typeOfuser1->update($object);
            } else {
                GroupMenu::create($object);
            }
        }

        $array = [
            ['id' => '1', 'name' => 'Sucursales', 'route' => 'sucursal', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '2', 'name' => 'Puntos de viaje', 'route' => 'puntos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '3', 'name' => 'Cajas', 'route' => 'cajas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],

            ['id' => '4', 'name' => 'Recepción de Carga', 'route' => 'recepcion', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],
            ['id' => '5', 'name' => 'Programación de Viaje', 'route' => 'viajes', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],

            ['id' => '6', 'name' => 'Concepto', 'route' => 'concepto', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '7', 'name' => 'Caja Chica', 'route' => 'cajachica', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '8', 'name' => 'Nota de Crédito', 'route' => 'notacredito', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],

            ['id' => '9', 'name' => 'Personas', 'route' => 'persona', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '10', 'name' => 'Trabajadores', 'route' => 'trabajador', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '11', 'name' => 'Usuarios', 'route' => 'usuarios', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],
            ['id' => '12', 'name' => 'Gestionar Accesos', 'route' => 'accesos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],

            ['id' => '13', 'name' => 'Logout', 'route' => 'logout', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 5],

            ['id' => '14', 'name' => 'Ventas', 'route' => 'ventas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],
            ['id' => '15', 'name' => 'Conductores', 'route' => 'conductores', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 4],

            ['id' => '16', 'name' => 'Guias Transporte', 'route' => 'guias', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 2],
            ['id' => '17', 'name' => 'Vehiculos', 'route' => 'vehiculos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],

            ['id' => '18', 'name' => 'Modelos', 'route' => 'modelos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            // ['id' => '19', 'name' => 'Flotas', 'route' => 'flotas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '20', 'name' => 'Rutas', 'route' => 'rutas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '21', 'name' => 'Areas', 'route' => 'areas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '22', 'name' => 'Cuentas por Cobrar', 'route' => 'cuentas', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 3],

            ['id' => '23', 'name' => 'Tipo Carroceria ', 'route' => 'tipocarroceria ', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '24', 'name' => 'Empresa vehiculo', 'route' => 'tipoempresavehiculo', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '25', 'name' => 'Documentos', 'route' => 'documentos', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 1],
            ['id' => '26', 'name' => 'Cambiar Sucursal', 'route' => 'seleccionar-sucursal', 'icon' => 'dot', 'guard_name' => 'web', 'groupMenu_id' => 5],
       
        ];

        foreach ($array as $object) {
            $typeOfuser1 = Permission::find($object['id']);
            if ($typeOfuser1) {
                $typeOfuser1->update($object);
            } else {
                Permission::create($object);
            }
        }
        Role::find(1)->syncPermissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25,26]);
       
        Role::find(2)->syncPermissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25,26]);
        Role::find(3)->syncPermissions([3, 6, 7, 8, 9, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25]);
        Role::find(4)->syncPermissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25]);
        $users = [
            ['id' => '1', 'username' => 'administrador', 'password' => 'adminTransporte', 'state' => '1', 'worker_id' => '4', 'typeofUser_id' => '2', 'box_id' => '1'],

            ['id' => '2', 'username' => 'worker', 'password' => 'workerTransporte', 'state' => '1', 'worker_id' => '2', 'typeofUser_id' => '3', 'box_id' => '1'],
            ['id' => '3', 'username' => 'cajero', 'password' => 'cajeroTransporte', 'state' => '1', 'worker_id' => '3', 'typeofUser_id' => '4', 'box_id' => '1'],
            ['id' => '4', 'username' => 'administradorBack', 'password' => 'adminBack', 'state' => '1', 'worker_id' => '1', 'typeofUser_id' => '1', 'box_id' => '1'],

        ];

        foreach ($users as $user) {
            // Buscar el registro por su ID
            $user1 = User::withTrashed()->find($user['id']);

            // Hashear la contraseña
            $user['password'] = Hash::make($user['password']);

            // Si el usuario existe, actualizarlo; de lo contrario, crear uno nuevo
            if ($user1) {
                $user1->update($user);
            } else {
                User::create($user);
            }
        }

        $this->call(PlaceSeeder::class);

        $contacts_info = [
            ['id' => '1',
                'typeOfDocument' => 'dni',
                'documentNumber' => '252158027',
                'names' => 'nombbre 1',
                'fatherSurname' => 'apellido1',
                'motherSurname' => 'apellido2',
                'address' => 'direccion1',
                'telephone' => '123456789',
                'email' => 'miguel@gmail.com',
                'person_id' => 1,

            ],
            ['id' => '2',
                'typeOfDocument' => 'dni',
                'documentNumber' => '15158072',
                'names' => 'nombbre 1',
                'fatherSurname' => 'apellido1',
                'motherSurname' => 'apellido2',
                'address' => 'direccion1',
                'telephone' => '123456789',
                'email' => 'miguel@gmail.com',
                'person_id' => 1,

            ],

        ];

        foreach ($contacts_info as $object) {
            $object1 = ContactInfo::find($object['id']);
            if ($object1) {
                $object1->update($object);
            } else {
                ContactInfo::create($object);
            }
        }

        $addresses = [
            ['id' => '1',
                'name' => 'direccion 1',
                'reference' => 'refernce 1',
                'client_id' => 1,

            ],
            ['id' => '2',
                'name' => 'direccion 2',
                'reference' => 'refernce 2',
                'client_id' => 1,
            ],

        ];

        foreach ($addresses as $object) {
            $object1 = Address::find($object['id']);
            if ($object1) {
                $object1->update($object);
            } else {
                Address::create($object);
            }
        }
        $tipo = 'R001';
        $resultado = DB::select('SELECT COALESCE(MAX(CAST(SUBSTRING(codeReception, LOCATE("-", codeReception) + 1) AS SIGNED)), 0) + 1 AS siguienteNum FROM receptions WHERE SUBSTRING(codeReception, 1, 4) = ?', [$tipo])[0]->siguienteNum;
        $siguienteNum = (int) $resultado;

        $reception = [
            [
                "id" => 1,
                "codeReception" => $tipo . '-' . str_pad($siguienteNum, 8, '0', STR_PAD_LEFT),
                'paymentAmount' => 5000.00,
                'debtAmount' => 5000.00,
                "typeService" => "Type of service",
                "type_responsiblePay" => "Remitente",
                "typeDelivery" => "Type of delivery",
                "conditionPay" => "Contado",
                "comment" => "Documents Anexos",

                "receptionDate" => "2024-05-08",
                "transferLimitDate" => "2024-05-10",
                "origin_id" => 1,
                "destination_id" => 2,
                "sender_id" => 1,
                "recipient_id" => 2,
                "pickupResponsible_id" => 1,
                "payResponsible_id" => 1,
                "seller_id" => 1,
                "pointDestination_id" => 1,
                "pointSender_id" => 2,
                'branchOffice_id' => '1',

            ],

        ];

        foreach ($reception as $object) {
            $object1 = Reception::find($object['id']);

            if ($object1) {
                $object1->update($object);
            } else {
                Reception::create($object);
            }
        }

        $modelFunctional = [
            ['id' => '1',
                'name' => 'modelo funcional1',
                'abbreviation' => 'MF1',
                'reference' => 'refernce 1',

            ],
            ['id' => '2',
                'name' => 'modelo funcional2',
                'abbreviation' => 'MF2',
                'reference' => 'refernce 2',
            ],

        ];

        foreach ($modelFunctional as $object) {
            $object1 = ModelFunctional::find($object['id']);
            if ($object1) {
                $object1->update($object);
            } else {
                ModelFunctional::create($object);
            }
        }

        $fleet = [
            ['id' => '1',
                'name' => 'fleet7',
                'abbreviation' => 'FLEET7',
                'reference' => 'refernce 8',

            ],
            ['id' => '2',
                'name' => 'fleet8',
                'abbreviation' => 'FLEET8',
                'reference' => 'refernce 8',
            ],

        ];

        $this->call(CompanyTypeTableSeeder::class);
        $this->call(TypeCarroceriaTableSeeder::class);

        $Vehicles = [
            [
                'id' => '1',
                'currentPlate' => 'CURR5678',
                'numberMtc' => 'MTC987654',
                'brand' => 'Toyota',
                'numberModel' => 'Model X',
                'tara' => 1500,
                'netWeight' => 2000,
                'usefulLoad' => 500,
                'ownerCompany' => 'Company ABC',
                'length' => 6.5,
                'width' => 2.5,
                'height' => 2.0,
                'ejes' => 2,
                'wheels' => 6,
                'typeCar' => 'Carreta',

                'color' => 'Red',
                'year' => 2022,
                'tireType' => 'Radial',
                'tireSuspension' => 'Air',

                'modelVehicle_id' => 1,
                'typeCarroceria_id' => 1,
                'status' => 'Activo',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '2',
                'currentPlate' => 'CURR1234',
                'numberMtc' => 'MTC123456',
                'brand' => 'Ford',
                'numberModel' => 'Model Y',
                'tara' => 1600,
                'netWeight' => 2200,
                'usefulLoad' => 600,
                'ownerCompany' => 'Company XYZ',
                'length' => 7.0,
                'width' => 2.6,
                'height' => 2.2,
                'ejes' => 3,
                'wheels' => 8,
                'typeCar' => 'Carreta',

                'color' => 'Blue',
                'year' => 2021,
                'tireType' => 'Tubeless',
                'tireSuspension' => 'Leaf Spring',

                'modelVehicle_id' => 1,
                'typeCarroceria_id' => 1,
                'status' => 'Activo',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '3',
                'currentPlate' => 'CURR9012',
                'numberMtc' => 'MTC345678',
                'brand' => 'Chevrolet',
                'numberModel' => 'Model Z',
                'tara' => 1700,
                'netWeight' => 2300,
                'usefulLoad' => 600,
                'ownerCompany' => 'Company LMN',
                'length' => 6.8,
                'width' => 2.4,
                'height' => 2.1,
                'ejes' => 2,
                'wheels' => 6,
                'typeCar' => 'Tracto',

                'color' => 'White',
                'year' => 2020,
                'tireType' => 'Radial',
                'tireSuspension' => 'Air',

                'modelVehicle_id' => 1,
                'typeCarroceria_id' => 1,
                'status' => 'Activo',
                'branchOffice_id' => 1,
            ],
            [
                'id' => '4',
                'currentPlate' => 'CURR3456',
                'numberMtc' => 'MTC567890',
                'brand' => 'Nissan',
                'numberModel' => 'Model A',
                'tara' => 1800,
                'netWeight' => 2400,
                'usefulLoad' => 600,
                'ownerCompany' => 'Company DEF',
                'length' => 7.2,
                'width' => 2.7,
                'height' => 2.3,
                'ejes' => 3,
                'wheels' => 10,
                'typeCar' => 'Carreta',

                'color' => 'Black',
                'year' => 2019,
                'tireType' => 'Tubeless',
                'tireSuspension' => 'Leaf Spring',

                'modelVehicle_id' => 1,
                'typeCarroceria_id' => 1,
                'status' => 'Activo',
                'branchOffice_id' => '1',
            ],
            [
                'id' => '5',
                'currentPlate' => 'CURR7890',
                'numberMtc' => 'MTC789012',
                'brand' => 'Honda',
                'numberModel' => 'Model B',
                'tara' => 1900,
                'netWeight' => 2500,
                'usefulLoad' => 600,
                'ownerCompany' => 'Company GHI',
                'length' => 6.9,
                'width' => 2.5,
                'height' => 2.2,
                'ejes' => 2,
                'wheels' => 8,
                'typeCar' => 'Tracto',

                'color' => 'Green',
                'year' => 2023,
                'tireType' => 'Radial',
                'tireSuspension' => 'Air',

                'modelVehicle_id' => 1,
                'typeCarroceria_id' => 1,
                'status' => 'Activo',
                'branchOffice_id' => 1,
            ],
        ];

        foreach ($Vehicles as $object) {
            $object1 = Vehicle::find($object['id']);
            if ($object1) {
                $object1->update($object);
            } else {
                Vehicle::create($object);
            }
        }

        $subcontracts = [
            ['id' => '1',
                'name' => 'subcontract 1',

            ],
            ['id' => '2',
                'name' => 'subcontract 2',

            ],

        ];

        foreach ($subcontracts as $object) {
            $object1 = Subcontract::find($object['id']);
            if ($object1) {
                $object1->update($object);
            } else {
                Subcontract::create($object);
            }
        }
        $this->call(MotiveTableSeeder::class);
        $this->call(ProgrammingSeeder::class);
        $this->call(ReceptionSeeder::class);
        $this->call(DetailReceptionSeeder::class);
        $this->call(ExpensesConceptSeeder::class);
        $this->call(ConeptPaySeeder::class);
        $this->call(BankSeeder::class);
        $this->call(RouteSeeder::class);
        $this->call(UnitiesSeeder::class);
        $this->call(AddresbyBranchOfficeSeeder::class);
        
        DB::unprepared('
        DROP FUNCTION IF EXISTS obtenerFormaPagoPorCaja;
    ');
        DB::unprepared('
                   CREATE DEFINER=`root`@`localhost` FUNCTION `obtenerFormaPagoPorCaja`(`caja_id` INT)
            RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
            READS SQL DATA
            BEGIN
                DECLARE forma_pago VARCHAR(255);

                SELECT CONCAT_WS(\',\',
                    CASE WHEN cash > 0 THEN \'Efectivo\' END,
                    CASE WHEN card > 0 THEN \'Tarjeta\' END,
                    CASE WHEN yape > 0 THEN \'Yape\' END,
                    CASE WHEN deposit > 0 THEN \'Depósito\' END,
                    CASE WHEN plin > 0 THEN \'Plin\' END
                ) INTO forma_pago
                FROM moviments
                WHERE id = caja_id;

                RETURN forma_pago;
            END;
    ');
    }
}
