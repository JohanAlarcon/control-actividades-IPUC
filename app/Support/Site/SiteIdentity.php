<?php

namespace App\Support\Site;

use App\Models\Site;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * El nombre de la sede y el lema del año en curso (RF-081, RF-082, D-43), que viajan en todas las
 * pantallas y en la entrada. Salen de la memoria de la aplicación, guardada por sede y por año: así
 * no suman consultas, y el primero de enero aparece solo el lema nuevo. Se borra al guardar un lema.
 */
class SiteIdentity
{
    private const ZONE = 'America/Bogota';

    /** @return array{name: string, city: ?string, motto: ?array{year: int, text: string, citation: ?string}}|null */
    public static function for(?User $persona): ?array
    {
        $anio = now(self::ZONE)->year;
        $llave = $persona ? "sede.{$persona->site_id}.{$anio}" : "sede.principal.{$anio}";

        return self::memoria()->rememberForever($llave, fn () => self::leer($persona?->site_id, $anio));
    }

    public static function forget(int $sede, int $anio): void
    {
        self::memoria()->forget("sede.{$sede}.{$anio}");
        self::memoria()->forget("sede.principal.{$anio}");
    }

    /** Una sola consulta: la sede y, si lo hay, el lema de ese año. Sin sesion, la sede principal. */
    private static function leer(?int $sede, int $anio): ?array
    {
        $fila = Site::query()
            ->leftJoin('site_mottos', fn ($lema) => $lema->on('site_mottos.site_id', '=', 'sites.id')->where('site_mottos.year', $anio))
            ->when($sede, fn ($consulta) => $consulta->where('sites.id', $sede), fn ($consulta) => $consulta->where('sites.active', true)->orderBy('sites.id'))
            ->first(['sites.name', 'sites.city', 'site_mottos.text', 'site_mottos.citation']);

        if ($fila === null) {
            return null;
        }

        return [
            'name' => $fila->name,
            'city' => $fila->city,
            'motto' => $fila->text === null ? null : ['year' => $anio, 'text' => $fila->text, 'citation' => $fila->citation],
        ];
    }

    /**
     * Si la memoria de la aplicación vive en la base de datos, leerla costaría una consulta por
     * pantalla: entonces se guarda en archivos. En las pruebas se usa la memoria de siempre.
     */
    private static function memoria(): Repository
    {
        return Cache::store(config('cache.default') === 'database' ? 'file' : null);
    }
}
