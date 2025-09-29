<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $title
 * @property string $about
 * @property string $location
 * @property string $operational
 * @property string $start
 * @property string $end
 * @property string $facility
 * @property string $maps
 * @property int $price
 * @property string $public_id
 * @property string $thumbnail
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Tour extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tours';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'about',
        'location',
        'operational',
        'start',
        'end',
        'facility',
        'maps',
        'price',
        'public_id',
        'thumbnail',
    ];
}
