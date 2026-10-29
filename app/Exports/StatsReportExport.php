<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** La hoja de las cifras: el encabezado con la sede, la fecha y los filtros; el resumen; y cada grupo con su tabla. */
class StatsReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private readonly array $encabezado, private readonly array $cifras) {}

    public function array(): array
    {
        $resumen = $this->cifras['summary'];
        $filas = [
            ...array_map(fn (string $linea) => [$linea], $this->encabezado),
            [],
            ['Resumen'],
            ['Miembros', $resumen['total']],
            ['Bautizados', $resumen['baptized']],
            ['Porcentaje de bautizados', "{$resumen['baptizedPercent']} %"],
            ['Casados', $resumen['married']],
            ['Solteros', $resumen['single']],
            ['Edad promedio', $resumen['averageAge'] ?? 'Sin datos'],
            ['Sin comité', $resumen['withoutCommittee']],
            ['Sin fecha de nacimiento ni edad anotada', $resumen['withoutAge']],
        ];

        foreach ($this->cifras['groups'] as $grupo) {
            $filas[] = [];
            $filas[] = [$grupo['title']];
            $filas = [...$filas, ...($grupo['rows'] ? array_map(fn (array $fila) => [$fila['label'], $fila['count']], $grupo['rows']) : [['Nadie', 0]])];
        }

        return $filas;
    }

    public function title(): string
    {
        return 'Cifras';
    }

    public function styles(Worksheet $hoja): array
    {
        return [1 => ['font' => ['bold' => true, 'size' => 14]]];
    }
}
