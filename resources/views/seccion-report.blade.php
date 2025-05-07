<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Seccion: {{ $seccion->name }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            margin: 20px;
            color: #333;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 15px;
            text-align: center;
            color: #2c3e50;
            text-transform: uppercase;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #34495e;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f4f6f8;
            font-weight: bold;
            color: #2c3e50;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 30px;
            color: #7f8c8d;
        }

        .footer span {
            display: block;
        }

        .container {
            padding: 0 15px;
        }

        .table-container {
            margin-top: 20px;
        }

        .table-container td {
            font-size: 13px;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #34495e; }
        .subtitle { font-size: 14px; color: #555; font-weight: bold; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="container">

        <div class="header">
            <div class="title">Reporte de Cargas</div>
            <div class="subtitle"Sección: {{ $seccion->name }}</div>
            <div class="subtitle">Almacén: {{  $seccion?->almacen?->name }}</div>
            <div>Generado el {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $index => $producto)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $producto->description }}</td>
                            <td>{{ $producto->pivot->stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


    </div>

</body>
</html>
