<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CalificacionesExport implements FromCollection, WithStyles, WithTitle, WithHeadings
{
    protected $resultados;
    protected $repeticiones;

    public function __construct($resultados, $repeticiones)
    {
        $this->resultados = collect($resultados);
        $this->repeticiones = $repeticiones;
    }

    public function collection()
    {
        // Convertir a un array para trabajar con él y calcular el promedio
        $dataArray = $this->resultados->toArray();

        // Calcular el promedio de las columnas numéricas
        $promedios = $this->calcularPromedios($dataArray);

        // Agregar la fila de promedios al final, dejando una columna vacía
        $dataArray[] = array_merge(['Promedio'], $promedios, ['']); // Agregar columna vacía

        return collect($dataArray);
    }

    // Método para calcular los promedios de las columnas numéricas
    private function calcularPromedios($data)
    {
        $suma = [];
        $count = [];

        // Recorrer los datos para sumar y contar
        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                // Verificar si el valor es numérico y si no está vacío
                if (is_numeric($value) && $value !== '') {
                    if (!isset($suma[$key])) {
                        $suma[$key] = 0;
                        $count[$key] = 0;
                    }
                    $suma[$key] += $value;
                    $count[$key]++;
                }
            }
        }

        // Calcular el promedio
        $promedios = [];
        foreach ($suma as $key => $total) {
            // Solo calcular el promedio si hay al menos un número
            $promedios[$key] = $count[$key] > 0 ? number_format($total / $count[$key], 2) : ''; // Formato a 2 decimales
        }

        return $promedios;
    }

    // Método para definir los encabezados de las columnas
    public function headings(): array
    {
        $headings = ['Estudiante'];

        // Repetir la secuencia según el número de repeticiones
        for ($i = 0; $i < $this->repeticiones; $i++) {
            $headings[] = '';    // Encabezado de la segunda columna
            $headings[] = 'P1';  // Encabezado de la tercera columna
            $headings[] = 'P2';  // Encabezado de la cuarta columna
            $headings[] = 'P3';  // Encabezado de la quinta columna
            $headings[] = 'PR';  // Encabezado del promedio
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la primera fila (encabezados)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => Color::COLOR_BLUE,
                ],
            ],
        ];

        // Determinar el número total de columnas
        $columnCount = count($this->headings());
        $rowCount = count($this->resultados) + 2; // +2 por los encabezados y la fila de promedios

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow($columnCount, 1)->getCoordinate())->applyFromArray($headerStyle);

        // Estilo para la fila de promedios
        $sheet->getStyle('A' . $rowCount . ':' . $sheet->getCellByColumnAndRow($columnCount, $rowCount)->getCoordinate())->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Aplicar bordes a todas las celdas de la colección
        $this->applyBorders($sheet, $rowCount, $columnCount);

        return [];
    }

    private function applyBorders(Worksheet $sheet, int $rowCount, int $columnCount)
    {
        // Aplicar bordes a todas las celdas
        for ($row = 1; $row <= $rowCount; $row++) {
            for ($column = 1; $column <= $columnCount; $column++) {
                $cell = $sheet->getCellByColumnAndRow($column, $row);
                $sheet->getStyle($cell->getCoordinate())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => Color::COLOR_BLACK],
                        ],
                    ],
                ]);
            }
        }
    }

    public function title(): string
    {
        return 'Calificaciones'; // Título de la hoja
    }

    // Método para ajustar automáticamente el ancho de las columnas
    public function setColumnWidths(Worksheet $sheet)
    {
        // Obtener el número total de columnas
        $columnCount = count($this->headings());

        // Ajustar el ancho de cada columna
        for ($column = 1; $column <= $columnCount; $column++) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }
}
