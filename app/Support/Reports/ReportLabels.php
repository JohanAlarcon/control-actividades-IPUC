<?php

namespace App\Support\Reports;

/**
 * Cómo se dicen las cifras y los filtros de los reportes. Están en un solo lugar para que el gráfico,
 * la tabla, la hoja impresa y el archivo digan lo mismo.
 */
class ReportLabels
{
    public const MARITAL = ['single' => 'Solteros', 'married' => 'Casados', 'common_law' => 'En unión libre', 'separated' => 'Separados', 'divorced' => 'Divorciados', 'widowed' => 'Viudos'];

    public const GENDER = ['f' => 'Mujeres', 'm' => 'Hombres'];

    public const AGES = ['children' => 'Niños (0 a 12)', 'teens' => 'Adolescentes (13 a 17)', 'young' => 'Jóvenes (18 a 29)', 'adults' => 'Adultos (30 a 59)', 'seniors' => 'Adultos mayores (60 o más)'];

    public const BAPTIZED = ['yes' => 'Bautizados', 'no' => 'Sin bautizar'];

    private const MARITAL_OF_ONE = ['single' => ['Soltera', 'Soltero'], 'married' => ['Casada', 'Casado'], 'common_law' => ['En unión libre', 'En unión libre'], 'separated' => ['Separada', 'Separado'], 'divorced' => ['Divorciada', 'Divorciado'], 'widowed' => ['Viuda', 'Viudo']];

    /** El estado civil de una persona: «Casada», «Casado». */
    public static function maritalOf(string $estado, string $genero): string
    {
        return self::MARITAL_OF_ONE[$estado][$genero === 'f' ? 0 : 1];
    }

    /** «Comité de Alabanza · Mujeres · Bautizados», o «Sin filtros». El comité se nombra aparte porque su nombre sale de la base. */
    public static function describe(array $filtros, ?string $comite): string
    {
        $partes = array_filter([
            $filtros['committee'] ? ($comite ?? 'El comité escogido') : null,
            $filtros['gender'] ? self::GENDER[$filtros['gender']] : null,
            $filtros['marital'] ? self::MARITAL[$filtros['marital']] : null,
            $filtros['baptized'] ? self::BAPTIZED[$filtros['baptized']] : null,
            $filtros['age'] ? self::AGES[$filtros['age']] : null,
        ]);

        return $partes ? implode(' · ', $partes) : 'Sin filtros';
    }
}
