<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string $writer
 * @property string $editor_id
 * @property string $public_id
 * @property string $thumbnail
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Article extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'articles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'content',
        'writer',
        'editor_id',
        'public_id',
        'thumbnail',
    ];

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
