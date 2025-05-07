@php use Carbon\Carbon; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Almacén {{ $almacen->numero }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 20mm 15mm; color: #2c3e50; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #34495e; }
        .subtitle { font-size: 14px; color: #555; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f4f6f8; }
        h4 { margin-bottom: 5px; color: #2c3e50; margin-top: 30px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Reporte de Cargas</div>
        <div class="subtitle">Almacén: {{ $almacen->name }}</div>

        <div>Generado el {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    @forelse ($productosPorSeccion as $seccionId => $productos)
        <h4>Sección: {{ $secciones[$seccionId] ?? 'Sin Nombre' }}</h4>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $index => $producto)
              <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $producto->description }}</td>
                        <td>{{ $producto->pivot->stock }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>No hay productos con stock disponible.</p>
    @endforelse

</body>
</html>
