<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $ticket_id
 * @property string $channel
 * @property string $payment_method
 * @property string $status
 * @property int $quantity
 * @property int $total_price
 * @property \Carbon\Carbon $order_date
 * @property string $qr_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'ticket_id',
        'channel',
        'payment_method',
        'status',
        'quantity',
        'total_price',
        'order_date',
        'qr_code',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'channel' => 'string',
        'payment_method' => 'string',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'order_id');
    }
}
