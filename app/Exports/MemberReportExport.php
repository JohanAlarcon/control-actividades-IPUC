<?php

namespace App\Exports;

use App\Models\CommitteeMember;
use App\Models\Member;
use App\Support\Reports\ReportLabels;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * La hoja de los miembros que cumplen los filtros, con lo que hace falta para las cifras. Sin
 * teléfono ni barrio: el archivo sale del sistema y se comparte, y para un reporte no se necesitan.
 */
class MemberReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly Collection $miembros) {}

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Edad', 'Género', 'Estado civil', 'Bautizado', 'Año de bautismo', 'Comités'];
    }

    public function array(): array
    {
        return $this->miembros->map(fn (Member $miembro) => [
            $miembro->code,
            $miembro->name,
            $miembro->age() ?? '',
            $miembro->gender === 'f' ? 'Mujer' : 'Hombre',
            ReportLabels::maritalOf($miembro->marital_status, $miembro->gender),
            $miembro->baptized ? 'Sí' : 'No',
            $miembro->baptized_at?->year ?? '',
            $miembro->memberships->map(fn (CommitteeMember $lista) => $lista->committee_name)->implode(', '),
        ])->all();
    }

    public function title(): string
    {
        return 'Miembros';
    }

    public function styles(Worksheet $hoja): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
