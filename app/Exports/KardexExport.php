<?php

namespace App\Exports;

use App\Models\CargaDocument;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    // Método para obtener los datos de la base de datos
    public function collection()
    {
        return CargaDocument::with('product', 'person') // Relacionar con productos y personas
            ->orderBy('movement_date', 'asc')
            ->get();
    }

    // Definir las cabeceras del archivo Excel
    public function headings(): array
    {
        return [
            'Fecha Movimiento',
            'Descripción Producto',
            'Cantidad',
            'Peso',
            'Saldo Anterior',
            'Entrada',
            'Salida',
            'Saldo Final',
            'Nombre Persona / Razón Social',
            'Tipo de Movimiento',
            'Comentario',
        ];
    }

    // Mapeo de cada fila para el archivo Excel
    public function map($document): array
    {
        static $saldo = 0; // Variable estática para calcular el saldo acumulado

        // Calculamos las entradas y salidas
        $entrada = $document->movement_type === 'INGRESO' ? $document->quantity : 0;
        $salida = $document->movement_type === 'EGRESO' ? $document->quantity : 0;

        // Calculamos el saldo acumulado
        $saldoAnterior = $saldo;
        $saldo += $entrada; // Aumenta por las entradas
        $saldo -= $salida; // Disminuye por las salidas

        // Nombre de la persona o razón social dependiendo del tipo de documento
        $persona = $document->person;
        if ($persona->typeofDocument === 'DNI') {
            $nombrePersona = "{$persona->names} {$persona->fatherSurname} {$persona->motherSurname}";
        } else {
            $nombrePersona = $persona->businessName;
        }

        return [
            $document->movement_date,
            $document->product->name, // Descripción Producto
            $document->quantity, // Cantidad
            $document->weight, // Peso
            $saldoAnterior, // Saldo anterior
            $entrada,  // Entrada
            $salida,   // Salida
            $saldo,    // Saldo final
            $nombrePersona, // Nombre de la Persona o Razón Social
            $document->movement_type,
            $document->comment,
        ];
    }

    // Título de la hoja Excel
    public function title(): string
    {
        return 'Kardex';
    }
}
