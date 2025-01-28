<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = [
            // Amazonas
            ['id' => 1, 'name' => 'Chachapoyas', 'ubigeo_code' => '0101', 'department_id' => 1],
            ['id' => 2, 'name' => 'Bagua', 'ubigeo_code' => '0102', 'department_id' => 1],
            ['id' => 3, 'name' => 'Bongará', 'ubigeo_code' => '0103', 'department_id' => 1],
            ['id' => 4, 'name' => 'Condorcanqui', 'ubigeo_code' => '0104', 'department_id' => 1],
            ['id' => 5, 'name' => 'Luya', 'ubigeo_code' => '0105', 'department_id' => 1],
            ['id' => 6, 'name' => 'Rodríguez de Mendoza', 'ubigeo_code' => '0106', 'department_id' => 1],
            ['id' => 7, 'name' => 'Utcubamba', 'ubigeo_code' => '0107', 'department_id' => 1],

            // Áncash
            ['id' => 8, 'name' => 'Huaraz', 'ubigeo_code' => '0201', 'department_id' => 2],
            ['id' => 9, 'name' => 'Aija', 'ubigeo_code' => '0202', 'department_id' => 2],
            ['id' => 10, 'name' => 'Antonio Raimondi', 'ubigeo_code' => '0203', 'department_id' => 2],
            ['id' => 11, 'name' => 'Asunción', 'ubigeo_code' => '0204', 'department_id' => 2],
            ['id' => 12, 'name' => 'Bolognesi', 'ubigeo_code' => '0205', 'department_id' => 2],
            ['id' => 13, 'name' => 'Carhuaz', 'ubigeo_code' => '0206', 'department_id' => 2],
            ['id' => 14, 'name' => 'Caraz', 'ubigeo_code' => '0207', 'department_id' => 2],
            ['id' => 15, 'name' => 'Casma', 'ubigeo_code' => '0208', 'department_id' => 2],
            ['id' => 16, 'name' => 'Corongo', 'ubigeo_code' => '0209', 'department_id' => 2],
            ['id' => 17, 'name' => 'Huallanca', 'ubigeo_code' => '0210', 'department_id' => 2],
            ['id' => 18, 'name' => 'Huari', 'ubigeo_code' => '0211', 'department_id' => 2],
            ['id' => 19, 'name' => 'Huaylas', 'ubigeo_code' => '0212', 'department_id' => 2],
            ['id' => 20, 'name' => 'María', 'ubigeo_code' => '0213', 'department_id' => 2],
            ['id' => 21, 'name' => 'Ocros', 'ubigeo_code' => '0214', 'department_id' => 2],
            ['id' => 22, 'name' => 'Pallasca', 'ubigeo_code' => '0215', 'department_id' => 2],
            ['id' => 23, 'name' => 'Pomabamba', 'ubigeo_code' => '0216', 'department_id' => 2],
            ['id' => 24, 'name' => 'Recuay', 'ubigeo_code' => '0217', 'department_id' => 2],
            ['id' => 25, 'name' => 'Santa', 'ubigeo_code' => '0218', 'department_id' => 2],
            ['id' => 26, 'name' => 'Sihuas', 'ubigeo_code' => '0219', 'department_id' => 2],
            ['id' => 27, 'name' => 'Yungay', 'ubigeo_code' => '0220', 'department_id' => 2],

            // Apurímac
            ['id' => 28, 'name' => 'Abancay', 'ubigeo_code' => '0301', 'department_id' => 3],
            ['id' => 29, 'name' => 'Antabamba', 'ubigeo_code' => '0302', 'department_id' => 3],
            ['id' => 30, 'name' => 'Aymaraes', 'ubigeo_code' => '0303', 'department_id' => 3],
            ['id' => 31, 'name' => 'Cotabambas', 'ubigeo_code' => '0304', 'department_id' => 3],
            ['id' => 32, 'name' => 'Grau', 'ubigeo_code' => '0305', 'department_id' => 3],

            // Arequipa
            ['id' => 33, 'name' => 'Arequipa', 'ubigeo_code' => '0401', 'department_id' => 4],
            ['id' => 34, 'name' => 'Camana', 'ubigeo_code' => '0402', 'department_id' => 4],
            ['id' => 35, 'name' => 'Caraveli', 'ubigeo_code' => '0403', 'department_id' => 4],
            ['id' => 36, 'name' => 'Castilla', 'ubigeo_code' => '0404', 'department_id' => 4],
            ['id' => 37, 'name' => 'Caylloma', 'ubigeo_code' => '0405', 'department_id' => 4],
            ['id' => 38, 'name' => 'La Unión', 'ubigeo_code' => '0406', 'department_id' => 4],

            // Ayacucho
            ['id' => 39, 'name' => 'Ayacucho', 'ubigeo_code' => '0501', 'department_id' => 5],
            ['id' => 40, 'name' => 'Cangallo', 'ubigeo_code' => '0502', 'department_id' => 5],
            ['id' => 41, 'name' => 'Huamanga', 'ubigeo_code' => '0503', 'department_id' => 5],
            ['id' => 42, 'name' => 'Huanca Sancos', 'ubigeo_code' => '0504', 'department_id' => 5],
            ['id' => 43, 'name' => 'Huanta', 'ubigeo_code' => '0505', 'department_id' => 5],
            ['id' => 44, 'name' => 'La Mar', 'ubigeo_code' => '0506', 'department_id' => 5],
            ['id' => 45, 'name' => 'Lucanas', 'ubigeo_code' => '0507', 'department_id' => 5],
            ['id' => 46, 'name' => 'Parinacochas', 'ubigeo_code' => '0508', 'department_id' => 5],
            ['id' => 47, 'name' => 'Páucar del Sara Sara', 'ubigeo_code' => '0509', 'department_id' => 5],
            ['id' => 48, 'name' => 'Sucre', 'ubigeo_code' => '0510', 'department_id' => 5],
            ['id' => 49, 'name' => 'Victor Fajardo', 'ubigeo_code' => '0511', 'department_id' => 5],
            ['id' => 50, 'name' => 'Vilcas Huamán', 'ubigeo_code' => '0512', 'department_id' => 5],

            // Cajamarca
            ['id' => 51, 'name' => 'Cajamarca', 'ubigeo_code' => '0601', 'department_id' => 6],
            ['id' => 52, 'name' => 'Cajabamba', 'ubigeo_code' => '0602', 'department_id' => 6],
            ['id' => 53, 'name' => 'Celendín', 'ubigeo_code' => '0603', 'department_id' => 6],
            ['id' => 54, 'name' => 'Chota', 'ubigeo_code' => '0604', 'department_id' => 6],
            ['id' => 55, 'name' => 'Contumazá', 'ubigeo_code' => '0605', 'department_id' => 6],
            ['id' => 56, 'name' => 'Hualgayoc', 'ubigeo_code' => '0606', 'department_id' => 6],
            ['id' => 57, 'name' => 'Jaén', 'ubigeo_code' => '0607', 'department_id' => 6],
            ['id' => 58, 'name' => 'San Ignacio', 'ubigeo_code' => '0608', 'department_id' => 6],
            ['id' => 59, 'name' => 'San Marcos', 'ubigeo_code' => '0609', 'department_id' => 6],
            ['id' => 60, 'name' => 'San Pablo', 'ubigeo_code' => '0610', 'department_id' => 6],
            ['id' => 61, 'name' => 'San Miguel', 'ubigeo_code' => '0611', 'department_id' => 6],
            ['id' => 62, 'name' => 'San Rafael', 'ubigeo_code' => '0612', 'department_id' => 6],

            // Callao
            ['id' => 63, 'name' => 'Callao', 'ubigeo_code' => '0701', 'department_id' => 7],

            // Cusco
            ['id' => 64, 'name' => 'Cusco', 'ubigeo_code' => '0801', 'department_id' => 8],
            ['id' => 65, 'name' => 'Acomayo', 'ubigeo_code' => '0802', 'department_id' => 8],
            ['id' => 66, 'name' => 'Anta', 'ubigeo_code' => '0803', 'department_id' => 8],
            ['id' => 67, 'name' => 'Calca', 'ubigeo_code' => '0804', 'department_id' => 8],
            ['id' => 68, 'name' => 'Canas', 'ubigeo_code' => '0805', 'department_id' => 8],
            ['id' => 69, 'name' => 'Canchis', 'ubigeo_code' => '0806', 'department_id' => 8],
            ['id' => 70, 'name' => 'Chumbivilcas', 'ubigeo_code' => '0807', 'department_id' => 8],
            ['id' => 71, 'name' => 'Espinar', 'ubigeo_code' => '0808', 'department_id' => 8],
            ['id' => 72, 'name' => 'La Convención', 'ubigeo_code' => '0809', 'department_id' => 8],
            ['id' => 73, 'name' => 'Paruro', 'ubigeo_code' => '0810', 'department_id' => 8],
            ['id' => 74, 'name' => 'Paucarpata', 'ubigeo_code' => '0811', 'department_id' => 8],
            ['id' => 75, 'name' => 'Quispicanchi', 'ubigeo_code' => '0812', 'department_id' => 8],
            ['id' => 76, 'name' => 'Urubamba', 'ubigeo_code' => '0813', 'department_id' => 8],

            // Huancavelica
            ['id' => 77, 'name' => 'Huancavelica', 'ubigeo_code' => '0901', 'department_id' => 9],
            ['id' => 78, 'name' => 'Acobamba', 'ubigeo_code' => '0902', 'department_id' => 9],
            ['id' => 79, 'name' => 'Angaraes', 'ubigeo_code' => '0903', 'department_id' => 9],
            ['id' => 80, 'name' => 'Castrovirreyna', 'ubigeo_code' => '0904', 'department_id' => 9],
            ['id' => 81, 'name' => 'Churcampa', 'ubigeo_code' => '0905', 'department_id' => 9],
            ['id' => 82, 'name' => 'Huaytará', 'ubigeo_code' => '0906', 'department_id' => 9],
            ['id' => 83, 'name' => 'Tayacaja', 'ubigeo_code' => '0907', 'department_id' => 9],

            // Huánuco
            ['id' => 84, 'name' => 'Huánuco', 'ubigeo_code' => '1001', 'department_id' => 10],
            ['id' => 85, 'name' => 'Ambo', 'ubigeo_code' => '1002', 'department_id' => 10],
            ['id' => 86, 'name' => 'Dos de Mayo', 'ubigeo_code' => '1003', 'department_id' => 10],
            ['id' => 87, 'name' => 'Huacaybamba', 'ubigeo_code' => '1004', 'department_id' => 10],
            ['id' => 88, 'name' => 'Huamalíes', 'ubigeo_code' => '1005', 'department_id' => 10],
            ['id' => 89, 'name' => 'Leoncio Prado', 'ubigeo_code' => '1006', 'department_id' => 10],
            ['id' => 90, 'name' => 'Marañón', 'ubigeo_code' => '1007', 'department_id' => 10],
            ['id' => 91, 'name' => 'Pachitea', 'ubigeo_code' => '1008', 'department_id' => 10],
            ['id' => 92, 'name' => 'Puerto Inca', 'ubigeo_code' => '1009', 'department_id' => 10],
            ['id' => 93, 'name' => 'Yarowilca', 'ubigeo_code' => '1010', 'department_id' => 10],

            // Ica
            ['id' => 94, 'name' => 'Ica', 'ubigeo_code' => '1101', 'department_id' => 11],
            ['id' => 95, 'name' => 'Chincha', 'ubigeo_code' => '1102', 'department_id' => 11],
            ['id' => 96, 'name' => 'Nazca', 'ubigeo_code' => '1103', 'department_id' => 11],
            ['id' => 97, 'name' => 'Palpa', 'ubigeo_code' => '1104', 'department_id' => 11],
            ['id' => 98, 'name' => 'Pisco', 'ubigeo_code' => '1105', 'department_id' => 11],

            // Junín
            ['id' => 99, 'name' => 'Huancayo', 'ubigeo_code' => '1201', 'department_id' => 12],
            ['id' => 100, 'name' => 'Concepción', 'ubigeo_code' => '1202', 'department_id' => 12],
            ['id' => 101, 'name' => 'Jauja', 'ubigeo_code' => '1203', 'department_id' => 12],
            ['id' => 102, 'name' => 'Junín', 'ubigeo_code' => '1204', 'department_id' => 12],
            ['id' => 103, 'name' => 'Santiago de Chuco', 'ubigeo_code' => '1205', 'department_id' => 12],
            ['id' => 104, 'name' => 'Tarma', 'ubigeo_code' => '1206', 'department_id' => 12],
            ['id' => 105, 'name' => 'Yauli', 'ubigeo_code' => '1207', 'department_id' => 12],

            // La Libertad
            ['id' => 106, 'name' => 'Trujillo', 'ubigeo_code' => '1301', 'department_id' => 13],
            ['id' => 107, 'name' => 'Ascope', 'ubigeo_code' => '1302', 'department_id' => 13],
            ['id' => 108, 'name' => 'Bolívar', 'ubigeo_code' => '1303', 'department_id' => 13],
            ['id' => 109, 'name' => 'Chepén', 'ubigeo_code' => '1304', 'department_id' => 13],
            ['id' => 110, 'name' => 'Gran Chimú', 'ubigeo_code' => '1305', 'department_id' => 13],
            ['id' => 111, 'name' => 'Julcán', 'ubigeo_code' => '1306', 'department_id' => 13],
            ['id' => 112, 'name' => 'Otuzco', 'ubigeo_code' => '1307', 'department_id' => 13],
            ['id' => 113, 'name' => 'Pacasmayo', 'ubigeo_code' => '1308', 'department_id' => 13],
            ['id' => 114, 'name' => 'Pataz', 'ubigeo_code' => '1309', 'department_id' => 13],
            ['id' => 115, 'name' => ' Sánchez Carrión', 'ubigeo_code' => '1310', 'department_id' => 13],
            ['id' => 116, 'name' => 'Santiago de Chuco', 'ubigeo_code' => '1311', 'department_id' => 13],
            ['id' => 117, 'name' => 'Virú', 'ubigeo_code' => '1312', 'department_id' => 13],

            // Lambayeque
            ['id' => 118, 'name' => 'Chiclayo', 'ubigeo_code' => '1401', 'department_id' => 14],
            ['id' => 119, 'name' => 'Ferreñafe', 'ubigeo_code' => '1402', 'department_id' => 14],
            ['id' => 120, 'name' => 'Lambayeque', 'ubigeo_code' => '1403', 'department_id' => 14],

            // Lima
            ['id' => 121, 'name' => 'Lima', 'ubigeo_code' => '1501', 'department_id' => 15],
            ['id' => 122, 'name' => 'Barranca', 'ubigeo_code' => '1502', 'department_id' => 15],
            ['id' => 123, 'name' => 'Cajatambo', 'ubigeo_code' => '1503', 'department_id' => 15],
            ['id' => 124, 'name' => 'Canta', 'ubigeo_code' => '1504', 'department_id' => 15],
            ['id' => 125, 'name' => 'Cañete', 'ubigeo_code' => '1505', 'department_id' => 15],
            ['id' => 126, 'name' => 'Huaral', 'ubigeo_code' => '1506', 'department_id' => 15],
            ['id' => 127, 'name' => 'Huarochirí', 'ubigeo_code' => '1507', 'department_id' => 15],
            ['id' => 128, 'name' => 'Huaura', 'ubigeo_code' => '1508', 'department_id' => 15],
            ['id' => 129, 'name' => 'Lima Provincias', 'ubigeo_code' => '1509', 'department_id' => 15],
            ['id' => 130, 'name' => 'Oyón', 'ubigeo_code' => '1510', 'department_id' => 15],
            ['id' => 131, 'name' => 'Yauyos', 'ubigeo_code' => '1511', 'department_id' => 15],

            // Loreto
            ['id' => 132, 'name' => 'Maynas', 'ubigeo_code' => '1601', 'department_id' => 16],
            ['id' => 133, 'name' => 'Alto Amazonas', 'ubigeo_code' => '1602', 'department_id' => 16],
            ['id' => 134, 'name' => 'Datem del Marañón', 'ubigeo_code' => '1603', 'department_id' => 16],
            ['id' => 135, 'name' => 'Loreto', 'ubigeo_code' => '1604', 'department_id' => 16],
            ['id' => 136, 'name' => 'Mariscal Ramón Castilla', 'ubigeo_code' => '1605', 'department_id' => 16],
            ['id' => 137, 'name' => 'Requena', 'ubigeo_code' => '1606', 'department_id' => 16],
            ['id' => 138, 'name' => 'Ucayali', 'ubigeo_code' => '1607', 'department_id' => 16],

            // Madre de Dios
            ['id' => 139, 'name' => 'Tambopata', 'ubigeo_code' => '1701', 'department_id' => 17],
            ['id' => 140, 'name' => 'Manu', 'ubigeo_code' => '1702', 'department_id' => 17],
            ['id' => 141, 'name' => 'Madre de Dios', 'ubigeo_code' => '1703', 'department_id' => 17],

            // Moquegua
            ['id' => 142, 'name' => 'Moquegua', 'ubigeo_code' => '1801', 'department_id' => 18],
            ['id' => 143, 'name' => 'Ilo', 'ubigeo_code' => '1802', 'department_id' => 18],
            ['id' => 144, 'name' => 'Mariscal Nieto', 'ubigeo_code' => '1803', 'department_id' => 18],

            // Pasco
            ['id' => 145, 'name' => 'Pasco', 'ubigeo_code' => '1901', 'department_id' => 19],
            ['id' => 146, 'name' => 'Daniel Alcides Carrión', 'ubigeo_code' => '1902', 'department_id' => 19],
            ['id' => 147, 'name' => 'Oxapampa', 'ubigeo_code' => '1903', 'department_id' => 19],

            // Piura
            ['id' => 148, 'name' => 'Piura', 'ubigeo_code' => '2001', 'department_id' => 20],
            ['id' => 149, 'name' => 'Ayabaca', 'ubigeo_code' => '2002', 'department_id' => 20],
            ['id' => 150, 'name' => 'Huancabamba', 'ubigeo_code' => '2003', 'department_id' => 20],
            ['id' => 151, 'name' => 'Paita', 'ubigeo_code' => '2004', 'department_id' => 20],
            ['id' => 152, 'name' => 'Sullana', 'ubigeo_code' => '2005', 'department_id' => 20],
            ['id' => 153, 'name' => 'Talara', 'ubigeo_code' => '2006', 'department_id' => 20],

            // Puno
            ['id' => 154, 'name' => 'Puno', 'ubigeo_code' => '2101', 'department_id' => 21],
            ['id' => 155, 'name' => 'Azángaro', 'ubigeo_code' => '2102', 'department_id' => 21],
            ['id' => 156, 'name' => 'Carabaya', 'ubigeo_code' => '2103', 'department_id' => 21],
            ['id' => 157, 'name' => 'Chucuito', 'ubigeo_code' => '2104', 'department_id' => 21],
            ['id' => 158, 'name' => 'El Collao', 'ubigeo_code' => '2105', 'department_id' => 21],
            ['id' => 159, 'name' => 'Huancané', 'ubigeo_code' => '2106', 'department_id' => 21],
            ['id' => 160, 'name' => 'Lampa', 'ubigeo_code' => '2107', 'department_id' => 21],
            ['id' => 161, 'name' => 'Melgar', 'ubigeo_code' => '2108', 'department_id' => 21],
            ['id' => 162, 'name' => 'San Antonio de Putina', 'ubigeo_code' => '2109', 'department_id' => 21],
            ['id' => 163, 'name' => 'San Román', 'ubigeo_code' => '2110', 'department_id' => 21],
            ['id' => 164, 'name' => 'Yunguyo', 'ubigeo_code' => '2111', 'department_id' => 21],

            // San Martín
            ['id' => 165, 'name' => 'Moyobamba', 'ubigeo_code' => '2201', 'department_id' => 22],
            ['id' => 166, 'name' => 'Bellavista', 'ubigeo_code' => '2202', 'department_id' => 22],
            ['id' => 167, 'name' => 'El Dorado', 'ubigeo_code' => '2203', 'department_id' => 22],
            ['id' => 168, 'name' => 'Huallaga', 'ubigeo_code' => '2204', 'department_id' => 22],
            ['id' => 169, 'name' => 'Lamas', 'ubigeo_code' => '2205', 'department_id' => 22],
            ['id' => 170, 'name' => 'Mariscal Cáceres', 'ubigeo_code' => '2206', 'department_id' => 22],
            ['id' => 171, 'name' => 'Picota', 'ubigeo_code' => '2207', 'department_id' => 22],
            ['id' => 172, 'name' => 'Río Mayo', 'ubigeo_code' => '2208', 'department_id' => 22],
            ['id' => 173, 'name' => 'San Martín', 'ubigeo_code' => '2209', 'department_id' => 22],
            ['id' => 174, 'name' => 'Tingo María', 'ubigeo_code' => '2210', 'department_id' => 22],

            // Tacna
            ['id' => 175, 'name' => 'Tacna', 'ubigeo_code' => '2301', 'department_id' => 23],
            ['id' => 176, 'name' => 'Candarave', 'ubigeo_code' => '2302', 'department_id' => 23],
            ['id' => 177, 'name' => 'Jorge Basadre', 'ubigeo_code' => '2303', 'department_id' => 23],
            ['id' => 178, 'name' => 'Tarata', 'ubigeo_code' => '2304', 'department_id' => 23],

            // Tumbes
            ['id' => 179, 'name' => 'Tumbes', 'ubigeo_code' => '2401', 'department_id' => 24],
            ['id' => 180, 'name' => 'Zorritos', 'ubigeo_code' => '2402', 'department_id' => 24],

            // Ucayali
            ['id' => 181, 'name' => 'Coronel Portillo', 'ubigeo_code' => '2501', 'department_id' => 25],
            ['id' => 182, 'name' => 'Atalaya', 'ubigeo_code' => '2502', 'department_id' => 25],
            ['id' => 183, 'name' => 'Padre Abad', 'ubigeo_code' => '2503', 'department_id' => 25],
            ['id' => 184, 'name' => 'Purús', 'ubigeo_code' => '2504', 'department_id' => 25],
        ];

        DB::table('provinces')->insert($provinces);
    }
}
