<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = [
    
            ['id' => 1, 'name' => 'Chachapoyas', 'ubigeo_code' => '010101', 'province_id' => 1],
            ['id' => 2, 'name' => 'Bagua', 'ubigeo_code' => '0102', 'province_id' => 2],
            ['id' => 3, 'name' => 'Bongará', 'ubigeo_code' => '0103', 'province_id' => 3],
            ['id' => 4, 'name' => 'Condorcanqui', 'ubigeo_code' => '0104', 'province_id' => 4],
            ['id' => 5, 'name' => 'Luya', 'ubigeo_code' => '0105', 'province_id' => 5],
            ['id' => 6, 'name' => 'Rodríguez de Mendoza', 'ubigeo_code' => '0106', 'province_id' => 6],
            ['id' => 7, 'name' => 'Utcubamba', 'ubigeo_code' => '0107', 'province_id' => 7],

            // Áncash
            ['id' => 8, 'name' => 'Huaraz', 'ubigeo_code' => '0201', 'province_id' => 8],
            ['id' => 9, 'name' => 'Aija', 'ubigeo_code' => '0202', 'province_id' => 9],
            ['id' => 10, 'name' => 'Anta', 'ubigeo_code' => '0203', 'province_id' => 10],
            ['id' => 11, 'name' => 'Carlos Fermín Fitzcarrald', 'ubigeo_code' => '0204', 'province_id' => 11],
            ['id' => 12, 'name' => 'Casma', 'ubigeo_code' => '0205', 'province_id' => 12],
            ['id' => 13, 'name' => 'Corongo', 'ubigeo_code' => '0206', 'province_id' => 13],
            ['id' => 14, 'name' => 'Huaylas', 'ubigeo_code' => '0207', 'province_id' => 14],
            ['id' => 15, 'name' => 'Huari', 'ubigeo_code' => '0208', 'province_id' => 15],
            ['id' => 16, 'name' => 'Mariscal Luzuriaga', 'ubigeo_code' => '0209', 'province_id' => 16],
            ['id' => 17, 'name' => 'Ocros', 'ubigeo_code' => '0210', 'province_id' => 17],
            ['id' => 18, 'name' => 'Pallasca', 'ubigeo_code' => '0211', 'province_id' => 18],
            ['id' => 19, 'name' => 'Pomabamba', 'ubigeo_code' => '0212', 'province_id' => 19],
            ['id' => 20, 'name' => 'Recuay', 'ubigeo_code' => '0213', 'province_id' => 20],
            ['id' => 21, 'name' => 'Santa', 'ubigeo_code' => '0214', 'province_id' => 21],
            ['id' => 22, 'name' => 'Sihuas', 'ubigeo_code' => '0215', 'province_id' => 22],
            ['id' => 23, 'name' => 'Yungay', 'ubigeo_code' => '0216', 'province_id' => 23],

            // Apurímac
            ['id' => 24, 'name' => 'Abancay', 'ubigeo_code' => '0301', 'province_id' => 24],
            ['id' => 25, 'name' => 'Andahuaylas', 'ubigeo_code' => '0302', 'province_id' => 25],
            ['id' => 26, 'name' => 'Antabamba', 'ubigeo_code' => '0303', 'province_id' => 26],
            ['id' => 27, 'name' => 'Aymaraes', 'ubigeo_code' => '0304', 'province_id' => 27],
            ['id' => 28, 'name' => 'Cotabambas', 'ubigeo_code' => '0305', 'province_id' => 28],
            ['id' => 29, 'name' => 'Chincheros', 'ubigeo_code' => '0306', 'province_id' => 29],
            ['id' => 30, 'name' => 'Huañec', 'ubigeo_code' => '0307', 'province_id' => 30],

            // Arequipa
            ['id' => 31, 'name' => 'Arequipa', 'ubigeo_code' => '0401', 'province_id' => 31],
            ['id' => 32, 'name' => 'Camana', 'ubigeo_code' => '0402', 'province_id' => 32],
            ['id' => 33, 'name' => 'Caravelí', 'ubigeo_code' => '0403', 'province_id' => 33],
            ['id' => 34, 'name' => 'Castilla', 'ubigeo_code' => '0404', 'province_id' => 34],
            ['id' => 35, 'name' => 'Caylloma', 'ubigeo_code' => '0405', 'province_id' => 35],
            ['id' => 36, 'name' => 'Condesuyos', 'ubigeo_code' => '0406', 'province_id' => 36],
            ['id' => 37, 'name' => 'Islay', 'ubigeo_code' => '0407', 'province_id' => 37],
            ['id' => 38, 'name' => 'La Unión', 'ubigeo_code' => '0408', 'province_id' => 38],

            // Ayacucho
            ['id' => 39, 'name' => 'Ayacucho', 'ubigeo_code' => '0501', 'province_id' => 39],
            ['id' => 40, 'name' => 'Cangallo', 'ubigeo_code' => '0502', 'province_id' => 40],
            ['id' => 41, 'name' => 'Huamanga', 'ubigeo_code' => '0503', 'province_id' => 41],
            ['id' => 42, 'name' => 'Huanca Sancos', 'ubigeo_code' => '0504', 'province_id' => 42],
            ['id' => 43, 'name' => 'Huanta', 'ubigeo_code' => '0505', 'province_id' => 43],
            ['id' => 44, 'name' => 'La Mar', 'ubigeo_code' => '0506', 'province_id' => 44],
            ['id' => 45, 'name' => 'Lucanas', 'ubigeo_code' => '0507', 'province_id' => 45],
            ['id' => 46, 'name' => 'Parinacochas', 'ubigeo_code' => '0508', 'province_id' => 46],
            ['id' => 47, 'name' => 'Páucar del Sara Sara', 'ubigeo_code' => '0509', 'province_id' => 47],
            ['id' => 48, 'name' => 'Sucre', 'ubigeo_code' => '0510', 'province_id' => 48],
            ['id' => 49, 'name' => 'Victor Fajardo', 'ubigeo_code' => '0511', 'province_id' => 49],
            ['id' => 50, 'name' => 'Vilcas Huamán', 'ubigeo_code' => '0512', 'province_id' => 50],

            // Callao
            ['id' => 51, 'name' => 'Callao', 'ubigeo_code' => '0601', 'province_id' => 51],

            // Cusco
            ['id' => 52, 'name' => 'Cusco', 'ubigeo_code' => '0701', 'province_id' => 52],
            ['id' => 53, 'name' => 'Calca', 'ubigeo_code' => '0702', 'province_id' => 53],
            ['id' => 54, 'name' => 'Canas', 'ubigeo_code' => '0703', 'province_id' => 54],
            ['id' => 55, 'name' => 'Canchis', 'ubigeo_code' => '0704', 'province_id' => 55],
            ['id' => 56, 'name' => 'Chumbivilcas', 'ubigeo_code' => '0705', 'province_id' => 56],
            ['id' => 57, 'name' => 'Espinar', 'ubigeo_code' => '0706', 'province_id' => 57],
            ['id' => 58, 'name' => 'La Convención', 'ubigeo_code' => '0707', 'province_id' => 58],
            ['id' => 59, 'name' => 'Paruro', 'ubigeo_code' => '0708', 'province_id' => 59],
            ['id' => 60, 'name' => 'Paucartambo', 'ubigeo_code' => '0709', 'province_id' => 60],
            ['id' => 61, 'name' => 'Quispicanchi', 'ubigeo_code' => '0710', 'province_id' => 61],
            ['id' => 62, 'name' => 'Urubamba', 'ubigeo_code' => '0711', 'province_id' => 62],

            // Huancavelica
            ['id' => 63, 'name' => 'Huancavelica', 'ubigeo_code' => '0801', 'province_id' => 63],
            ['id' => 64, 'name' => 'Acobamba', 'ubigeo_code' => '0802', 'province_id' => 64],
            ['id' => 65, 'name' => 'Angaraes', 'ubigeo_code' => '0803', 'province_id' => 65],
            ['id' => 66, 'name' => 'Castrovirreyna', 'ubigeo_code' => '0804', 'province_id' => 66],
            ['id' => 67, 'name' => 'Churcampa', 'ubigeo_code' => '0805', 'province_id' => 67],
            ['id' => 68, 'name' => 'Huaytará', 'ubigeo_code' => '0806', 'province_id' => 68],
            ['id' => 69, 'name' => 'Tayacaja', 'ubigeo_code' => '0807', 'province_id' => 69],

            // Huánuco
            ['id' => 70, 'name' => 'Huánuco', 'ubigeo_code' => '0901', 'province_id' => 70],
            ['id' => 71, 'name' => 'Ambo', 'ubigeo_code' => '0902', 'province_id' => 71],
            ['id' => 72, 'name' => 'Dos de Mayo', 'ubigeo_code' => '0903', 'province_id' => 72],
            ['id' => 73, 'name' => 'Huacaybamba', 'ubigeo_code' => '0904', 'province_id' => 73],
            ['id' => 74, 'name' => 'Huallanca', 'ubigeo_code' => '0905', 'province_id' => 74],
            ['id' => 75, 'name' => 'Huánuco', 'ubigeo_code' => '0906', 'province_id' => 75],
            ['id' => 76, 'name' => 'La Unión', 'ubigeo_code' => '0907', 'province_id' => 76],
            ['id' => 77, 'name' => 'Mancos', 'ubigeo_code' => '0908', 'province_id' => 77],
            ['id' => 78, 'name' => 'Panao', 'ubigeo_code' => '0909', 'province_id' => 78],
            ['id' => 79, 'name' => 'Rupa-Rupa', 'ubigeo_code' => '0910', 'province_id' => 79],
            ['id' => 80, 'name' => 'Yarusyacán', 'ubigeo_code' => '0911', 'province_id' => 80],

            // Ica
            ['id' => 81, 'name' => 'Ica', 'ubigeo_code' => '1001', 'province_id' => 81],
            ['id' => 82, 'name' => 'Chincha', 'ubigeo_code' => '1002', 'province_id' => 82],
            ['id' => 83, 'name' => 'Nazca', 'ubigeo_code' => '1003', 'province_id' => 83],
            ['id' => 84, 'name' => 'Palpa', 'ubigeo_code' => '1004', 'province_id' => 84],
            ['id' => 85, 'name' => 'Pisco', 'ubigeo_code' => '1005', 'province_id' => 85],

            // Junín
            ['id' => 86, 'name' => 'Huancayo', 'ubigeo_code' => '1101', 'province_id' => 86],
            ['id' => 87, 'name' => 'Concepción', 'ubigeo_code' => '1102', 'province_id' => 87],
            ['id' => 88, 'name' => 'Jauja', 'ubigeo_code' => '1103', 'province_id' => 88],
            ['id' => 89, 'name' => 'Junín', 'ubigeo_code' => '1104', 'province_id' => 89],
            ['id' => 90, 'name' => 'Santiago de Palla', 'ubigeo_code' => '1105', 'province_id' => 90],
            ['id' => 91, 'name' => 'Tarma', 'ubigeo_code' => '1106', 'province_id' => 91],
            ['id' => 92, 'name' => 'Yauli', 'ubigeo_code' => '1107', 'province_id' => 92],

            // La Libertad
            ['id' => 93, 'name' => 'Trujillo', 'ubigeo_code' => '1201', 'province_id' => 93],
            ['id' => 94, 'name' => 'Ascope', 'ubigeo_code' => '1202', 'province_id' => 94],
            ['id' => 95, 'name' => 'Bolívar', 'ubigeo_code' => '1203', 'province_id' => 95],
            ['id' => 96, 'name' => 'Chepén', 'ubigeo_code' => '1204', 'province_id' => 96],
            ['id' => 97, 'name' => 'Julcán', 'ubigeo_code' => '1205', 'province_id' => 97],
            ['id' => 98, 'name' => 'Otuzco', 'ubigeo_code' => '1206', 'province_id' => 98],
            ['id' => 99, 'name' => 'Pacasmayo', 'ubigeo_code' => '1207', 'province_id' => 99],
            ['id' => 100, 'name' => 'Pataz', 'ubigeo_code' => '1208', 'province_id' => 100],
            ['id' => 101, 'name' => 'Sánchez Carrión', 'ubigeo_code' => '1209', 'province_id' => 101],
            ['id' => 102, 'name' => 'Santa Catalina', 'ubigeo_code' => '1210', 'province_id' => 102],
            ['id' => 103, 'name' => 'Virú', 'ubigeo_code' => '1211', 'province_id' => 103],

            // Lambayeque
            ['id' => 104, 'name' => 'Chiclayo', 'ubigeo_code' => '1301', 'province_id' => 104],
            ['id' => 105, 'name' => 'Chongoyape', 'ubigeo_code' => '1302', 'province_id' => 105],
            ['id' => 106, 'name' => 'Ferreñafe', 'ubigeo_code' => '1303', 'province_id' => 106],
            ['id' => 107, 'name' => 'Lambayeque', 'ubigeo_code' => '1304', 'province_id' => 107],

            // Lima
            ['id' => 108, 'name' => 'Lima', 'ubigeo_code' => '1401', 'province_id' => 108],
            ['id' => 109, 'name' => 'Callao', 'ubigeo_code' => '1402', 'province_id' => 109],

            // Loreto
            ['id' => 110, 'name' => 'Maynas', 'ubigeo_code' => '1501', 'province_id' => 110],
            ['id' => 111, 'name' => 'Alto Amazonas', 'ubigeo_code' => '1502', 'province_id' => 111],
            ['id' => 112, 'name' => 'Datem del Marañón', 'ubigeo_code' => '1503', 'province_id' => 112],
            ['id' => 113, 'name' => 'Mariscal Ramón Castilla', 'ubigeo_code' => '1504', 'province_id' => 113],
            ['id' => 114, 'name' => 'Requena', 'ubigeo_code' => '1505', 'province_id' => 114],

            // Madre de Dios
            ['id' => 115, 'name' => 'Madre de Dios', 'ubigeo_code' => '1601', 'province_id' => 115],
            ['id' => 116, 'name' => 'Manu', 'ubigeo_code' => '1602', 'province_id' => 116],
            ['id' => 117, 'name' => 'Tahuamanu', 'ubigeo_code' => '1603', 'province_id' => 117],

            // Moquegua
            ['id' => 118, 'name' => 'Mariscal Nieto', 'ubigeo_code' => '1701', 'province_id' => 118],
            ['id' => 119, 'name' => 'General Sánchez Cerro', 'ubigeo_code' => '1702', 'province_id' => 119],

            // Pasco
            ['id' => 120, 'name' => 'Pasco', 'ubigeo_code' => '1901', 'province_id' => 120],
            ['id' => 121, 'name' => 'Daniel Alcides Carrión', 'ubigeo_code' => '1902', 'province_id' => 121],
            ['id' => 122, 'name' => 'Oxapampa', 'ubigeo_code' => '1903', 'province_id' => 122],

            // Piura
            ['id' => 123, 'name' => 'Piura', 'ubigeo_code' => '2001', 'province_id' => 123],
            ['id' => 124, 'name' => 'Ayabaca', 'ubigeo_code' => '2002', 'province_id' => 124],
            ['id' => 125, 'name' => 'Huancabamba', 'ubigeo_code' => '2003', 'province_id' => 125],
            ['id' => 126, 'name' => 'Morropón', 'ubigeo_code' => '2004', 'province_id' => 126],
            ['id' => 127, 'name' => 'Paita', 'ubigeo_code' => '2005', 'province_id' => 127],
            ['id' => 128, 'name' => 'Sullana', 'ubigeo_code' => '2006', 'province_id' => 128],
            ['id' => 129, 'name' => 'Talara', 'ubigeo_code' => '2007', 'province_id' => 129],

            // Puno
            ['id' => 130, 'name' => 'Puno', 'ubigeo_code' => '2101', 'province_id' => 130],
            ['id' => 131, 'name' => 'Azángaro', 'ubigeo_code' => '2102', 'province_id' => 131],
            ['id' => 132, 'name' => 'Carabaya', 'ubigeo_code' => '2103', 'province_id' => 132],
            ['id' => 133, 'name' => 'Chucuito', 'ubigeo_code' => '2104', 'province_id' => 133],
            ['id' => 134, 'name' => 'El Collao', 'ubigeo_code' => '2105', 'province_id' => 134],
            ['id' => 135, 'name' => 'Huancané', 'ubigeo_code' => '2106', 'province_id' => 135],
            ['id' => 136, 'name' => 'Lampa', 'ubigeo_code' => '2107', 'province_id' => 136],
            ['id' => 137, 'name' => 'Melgar', 'ubigeo_code' => '2108', 'province_id' => 137],
            ['id' => 138, 'name' => 'San Antonio de Putina', 'ubigeo_code' => '2109', 'province_id' => 138],
            ['id' => 139, 'name' => 'San Román', 'ubigeo_code' => '2110', 'province_id' => 139],
            ['id' => 140, 'name' => 'Yunguyo', 'ubigeo_code' => '2111', 'province_id' => 140],

            // San Martín
            ['id' => 141, 'name' => 'San Martín', 'ubigeo_code' => '2201', 'province_id' => 141],
            ['id' => 142, 'name' => 'Bellavista', 'ubigeo_code' => '2202', 'province_id' => 142],
            ['id' => 143, 'name' => 'El Dorado', 'ubigeo_code' => '2203', 'province_id' => 143],
            ['id' => 144, 'name' => 'Huallaga', 'ubigeo_code' => '2204', 'province_id' => 144],
            ['id' => 145, 'name' => 'Lamas', 'ubigeo_code' => '2205', 'province_id' => 145],
            ['id' => 146, 'name' => 'Mariscal Cáceres', 'ubigeo_code' => '2206', 'province_id' => 146],
            ['id' => 147, 'name' => 'Picota', 'ubigeo_code' => '2207', 'province_id' => 147],
            ['id' => 148, 'name' => 'Rioja', 'ubigeo_code' => '2208', 'province_id' => 148],
            ['id' => 149, 'name' => 'San Martín', 'ubigeo_code' => '2209', 'province_id' => 149],
            ['id' => 150, 'name' => 'Tocache', 'ubigeo_code' => '2210', 'province_id' => 150],

            // Tacna
            ['id' => 151, 'name' => 'Tacna', 'ubigeo_code' => '2301', 'province_id' => 151],
            ['id' => 152, 'name' => 'Candarave', 'ubigeo_code' => '2302', 'province_id' => 152],
            ['id' => 153, 'name' => 'Jorge Basadre', 'ubigeo_code' => '2303', 'province_id' => 153],

            // Tumbes
            ['id' => 154, 'name' => 'Tumbes', 'ubigeo_code' => '2401', 'province_id' => 154],
            ['id' => 155, 'name' => 'Zorritos', 'ubigeo_code' => '2402', 'province_id' => 155],

            // Ucayali
            ['id' => 156, 'name' => 'Coronel Portillo', 'ubigeo_code' => '2501', 'province_id' => 156],
            ['id' => 157, 'name' => 'Atalaya', 'ubigeo_code' => '2502', 'province_id' => 157],
            ['id' => 158, 'name' => 'Padre Abad', 'ubigeo_code' => '2503', 'province_id' => 158],
            ['id' => 159, 'name' => 'Purús', 'ubigeo_code' => '2504', 'province_id' => 159],
        ];

    }
}
