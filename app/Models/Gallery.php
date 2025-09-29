<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $title
 * @property string $caption
 * @property string $public_id
 * @property string $image
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Gallery extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'galleries';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'caption',
        'public_id',
        'image',
    ];
}
