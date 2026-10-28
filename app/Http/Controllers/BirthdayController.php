<?php

namespace App\Http\Controllers;

use App\Support\Activities\ScheduleCards;
use App\Support\People\Birthdays;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Los cumpleaños de un mes, para planear la celebración (HU-29 escenarios 3 a 5): los miembros y
 * los simpatizantes del ámbito, con su fecha y su código, y cuántas fichas no tienen fecha.
 */
class BirthdayController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = now(ScheduleCards::ZONE);
        $mes = in_array($request->integer('month'), range(1, 12), true) ? $request->integer('month') : $hoy->month;
        // Un mes que ya pasó este año se muestra como viene: el del año siguiente, con la edad que cumplirán.
        $anio = $mes < $hoy->month ? $hoy->year + 1 : $hoy->year;
        $personas = Birthdays::people();

        return Inertia::render('Birthdays/Index', [
            'month' => $mes,
            'year' => $anio,
            'people' => Birthdays::ofMonth($personas, $mes, $anio),
            'withoutDate' => $personas->whereNull('birthDate')->count(),
        ]);
    }
}
