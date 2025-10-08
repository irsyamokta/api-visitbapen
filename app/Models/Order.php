<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $user_id
 * @property string $ticket_id
 * @property string $name
 * @property string $channel
 * @property string $payment_method
 * @property string $status
 * @property int $quantity
 * @property int $total_price
 * @property \Carbon\Carbon $order_date
 * @property \Carbon\Carbon $used_at
 * @property string $qr_code
 * @property string $snap_token
 * @property \Carbon\Carbon $snap_token_expired_at
 * @property string $raw_response
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
        'name',
        'channel',
        'payment_method',
        'status',
        'quantity',
        'total_price',
        'order_date',
        'used_at',
        'qr_code',
        'snap_token',
        'snap_token_expired_at',
        'raw_response',
    ];

    protected $casts = [
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
