<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

/**
 * La nota general de un mes (RF-118): «Operación rescate, Comité de Jóvenes» o un encuentro del
 * distrito con la fecha pendiente. Es de toda la sede, como el cronograma general: la registra el
 * pastor y la ven todos. La marca de color es la de las actividades (RF-116).
 */
class MonthNote extends Model
{
    use BelongsToSite;

    protected $fillable = ['site_id', 'year', 'month', 'text', 'highlight', 'recorded_by_user_id'];

    protected function casts(): array
    {
        return ['year' => 'integer', 'month' => 'integer'];
    }
}
