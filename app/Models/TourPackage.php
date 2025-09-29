<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $title
 * @property int $price
 * @property string $benefit
 * @property string $public_id
 * @property string $thumbnail
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TourPackage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tour_packages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'price',
        'benefit',
        'public_id',
        'thumbnail',
    ];
}
