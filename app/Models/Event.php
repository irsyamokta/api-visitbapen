<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $date
 * @property string $time
 * @property string $place
 * @property int $price
 * @property string $public_id
 * @property string $thumbnail
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Event extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'date',
        'time',
        'place',
        'price',
        'public_id',
        'thumbnail',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
