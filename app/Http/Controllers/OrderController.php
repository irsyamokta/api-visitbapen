<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Ticket;
use App\Helpers\ValidationHelper;
use App\Services\MidtransService;
use Carbon\Carbon;
use Midtrans\Config;
use Ramsey\Uuid\Uuid;

class OrderController extends Controller
{
    private $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $user = auth()->user();

        $query = Order::query();

        if ($user->role == 'visitor') {
            $query->where('user_id', $user->id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validator = ValidationHelper::validateOrder($request->all());
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->user()->id != $request->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        DB::beginTransaction();

        try {
            $data = $validator->validated();

            $ticket = Ticket::select('price')->find($data['ticket_id']);
            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                ], 404);
            }

            $order = Order::create([
                'user_id' => auth()->user()->id,
                'ticket_id' => $data['ticket_id'],
                'name' => $data['name'],
                'channel' => 'online',
                'status' => 'pending',
                'quantity' => $data['quantity'],
                'total_price' => $data['total_price'],
                'order_date' => Carbon::parse(now())->setTimezone(config('app.timezone')),
                'qr_code' => Uuid::uuid4()->toString(),
            ]);

            Transaction::create([
                'user_id' => auth()->user()->id,
                'title' => 'Penjualan Tiket',
                'type' => 'income',
                'category' => 'Penjualan',
                'amount' => $data['total_price'],
                'finance_role' => 'finance_tourism',
                'transaction_date' => Carbon::parse(now())->setTimezone(config('app.timezone')),
                'order_id' => $order->id,
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $ticket->price * $order->quantity,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
                'item_details' => [
                    [
                        'id' => $order->ticket_id,
                        'price' => $ticket->price,
                        'quantity' => $order->quantity,
                        'name' => 'Tiket Wisata',
                    ],
                ],
            ];

            $response = $this->midtrans->createTransaction($params);

            if (!isset($response->token) || empty($response->token)) {
                throw new \Exception('Snap token not received from Midtrans');
            }

            $snapToken = $response->token;

            $order->update([
                'snap_token' => $snapToken,
                'snap_token_expired_at' => Carbon::now()->addMinutes(15),
                'raw_response' => json_encode($response),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan',
            ], 404);
        }
        DB::beginTransaction();

        try {
            if ($order->snap_token) {
                try {
                    $statusResponse = (object) $this->midtrans->status($order->id);

                    if (in_array($statusResponse->transaction_status, ['cancel', 'expire', 'deny'])) {
                        $order->update([
                            'status' => $statusResponse->transaction_status,
                            'snap_token' => null,
                            'snap_token_expired_at' => null,
                        ]);

                        DB::commit();

                        return response()->json([
                            'message' => 'Order berhasil dibatalkan',
                        ]);
                    }

                    $this->midtrans->cancelTransaction($order->id);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $order->update([
                'status' => 'canceled',
                'snap_token' => null,
                'snap_token_expired_at' => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order berhasil dibatalkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membatalkan order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);

        try {
            $notification = new \Midtrans\Notification();

            $serverKey = Config::$serverKey;
            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $grossAmount = (string) $notification->gross_amount;
            $expectedSignature = hash('SHA512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($notification->signature_key !== $expectedSignature) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $orderId = $notification->order_id;
            $status = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? 'accept';
            $paymentType = $notification->payment_type ?? null;

            $order = Order::where('id', $orderId)->first();
            if (!$order) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            DB::beginTransaction();

            $updateData = ['status' => $status];
            if (in_array($status, ['capture', 'settlement']) && $fraudStatus === 'accept') {
                $updateData['status'] = 'paid';
                $updateData['payment_method'] = $paymentType;
            } elseif ($status === 'capture' && $fraudStatus !== 'accept') {
                $updateData['status'] = 'canceled';
            } elseif (in_array($status, ['cancel', 'deny'])) {
                $updateData['status'] = 'canceled';
            } elseif ($status === 'expire') {
                $updateData['status'] = 'expired';
            }

            $updateData['raw_notification'] = json_encode($notification->getResponse());

            $order->update($updateData);

            DB::commit();

            return response()->json(['message' => 'Notifikasi berhasil diterima']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menerima notifikasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function history()
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->select('id', 'ticket_id', 'status', 'quantity', 'total_price', 'order_date', 'used_at', 'qr_code', 'snap_token', 'snap_token_expired_at')
            ->with(['ticket:id,title,price'])
            ->orderBy('order_date', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function visitor(Request $request)
    {
        $limit = $request->query('limit', 10);
        $date = $request->query('date');

        $query = Order::whereNotNull('used_at')
            ->with(['ticket:id,title', 'user:id,name'])
            ->orderBy('used_at', 'desc');

        if ($date) {
            $query->whereDate('used_at', $date);
        }

        $visitors = $query->paginate($limit);

        $totalVisitors = $query->sum('quantity');

        return response()->json([
            'data' => $visitors->items(),
            'total' => $totalVisitors,
            'current_page' => $visitors->currentPage(),
            'last_page' => $visitors->lastPage(),
            'per_page' => $visitors->perPage(),
            'total_orders' => $visitors->total(),
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $order = Order::where('qr_code', $request->qr_code)->first();

            if (!$order) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                ], 404);
            }

            if ($order->used_at) {
                return response()->json([
                    'message' => 'Tiket sudah digunakan',
                ], 400);
            }

            $order->update([
                'used_at' => Carbon::now('Asia/Jakarta'),
            ]);

            return response()->json([
                'message' => 'Tiket berhasil digunakan',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menggunakan tiket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
