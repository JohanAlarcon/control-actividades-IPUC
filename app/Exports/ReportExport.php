<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** El archivo de los reportes (RF-038): una hoja con las cifras y otra con los miembros que cumplen los filtros. */
class ReportExport implements Export, WithMultipleSheets
{
    public function __construct(
        private readonly array $encabezado,
        private readonly array $cifras,
        private readonly Collection $miembros,
    ) {}

    public function sheets(): array
    {
        return [new StatsReportExport($this->encabezado, $this->cifras), new MemberReportExport($this->miembros)];
    }
}
