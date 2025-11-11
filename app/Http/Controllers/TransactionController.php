<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Order;
use App\Helpers\ValidationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik'
                ? 'finance_batik'
                : ($userRole === 'admin_tourism' || $userRole === 'finance_tourism'
                    ? 'finance_tourism'
                    : null);

            $perPage = $request->get('per_page', 10);

            $transactions = Transaction::query()
                ->when($financeRole, function ($query) use ($financeRole) {
                    return $query->where('finance_role', $financeRole);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%');
                })
                ->when($request->category, function ($query, $category) {
                    return $query->where('category', $category);
                })
                ->when($request->type, function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                    $start = Carbon::parse($request->start_date)
                        ->setTimezone(config('app.timezone'))
                        ->startOfDay();
                    $end = Carbon::parse($request->end_date)
                        ->setTimezone(config('app.timezone'))
                        ->endOfDay();

                    return $query->whereBetween('transaction_date', [$start, $end]);
                })
                ->orderBy('transaction_date', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function analytics()
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole =
                ($userRole === 'admin_batik' || $userRole === 'finance_batik')
                ? 'finance_batik'
                : (($userRole === 'admin_tourism' || $userRole === 'finance_tourism' || $userRole === 'admin')
                    ? 'finance_tourism'
                    : null);

            $query = Transaction::query();

            if ($financeRole) {
                $query->where('finance_role', $financeRole);
            }

            $transactions = $query->get();

            $expense_total = $transactions->where('type', 'expense')->sum('amount');
            $expense_breakdown = $transactions
                ->where('type', 'expense')
                ->groupBy('category')
                ->map(function ($group, $category) use ($expense_total) {
                    $sum = $group->sum('amount');
                    return [
                        'category' => $category,
                        'amount' => $sum,
                        'percentage' => $expense_total > 0 ? round(($sum / $expense_total) * 100, 1) : 0,
                    ];
                })->values();

            $income_total = $transactions->where('type', 'income')->sum('amount');
            $income_sources = $transactions
                ->where('type', 'income')
                ->groupBy('category')
                ->map(function ($group, $category) use ($income_total) {
                    $sum = $group->sum('amount');
                    return [
                        'category' => $category,
                        'amount' => $sum,
                        'percentage' => $income_total > 0 ? round(($sum / $income_total) * 100, 1) : 0,
                    ];
                })->values();

            $monthly_trends = $transactions
                ->groupBy(function ($t) {
                    return Carbon::parse($t->transaction_date)->format('M Y');
                })
                ->map(function ($group, $month) {
                    $income = $group->where('type', 'income')->sum('amount');
                    $expenses = $group->where('type', 'expense')->sum('amount');
                    return [
                        'month' => $month,
                        'income' => $income,
                        'expenses' => $expenses,
                        'net' => $income - $expenses,
                    ];
                })->values();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'expense_breakdown' => $expense_breakdown,
                    'income_sources' => $income_sources,
                    'monthly_trends' => $monthly_trends,
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik' ? 'finance_batik' : null;

            $transaction = Transaction::where('id', $id)
                ->when($financeRole, function ($query) use ($financeRole) {
                    return $query->where('finance_role', $financeRole);
                })->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik' ? 'finance_batik' : 'finance_tourism';

            $validator = ValidationHelper::validateTransaction($request->all(), true);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            $orderId = null;

            if (
                $data['category'] === 'Penjualan Tiket' &&
                $request->has('ticket_id') &&
                $data['type'] === 'income' &&
                in_array($userRole, ['finance_tourism', 'admin_tourism'])
            ) {
                $order = Order::create([
                    'user_id' => $data['user_id'] ?? null,
                    'ticket_id' => $data['ticket_id'],
                    'name' => $data['name'],
                    'channel' => 'offline',
                    'payment_method' => 'cash',
                    'status' => 'paid',
                    'quantity' => $data['quantity'] ?? 1,
                    'total_price' => $data['amount'],
                    'order_date' => Carbon::parse($data['transaction_date'])->setTimezone(config('app.timezone')),
                    'qr_code' => null,
                ]);

                $orderId = $order->id;
            }

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'title' => $data['title'],
                'type' => $data['type'],
                'category' => $data['category'],
                'amount' => $data['amount'],
                'finance_role' => $financeRole,
                'transaction_date' => Carbon::parse($data['transaction_date'])->setTimezone(config('app.timezone')),
                'order_id' => $orderId,
                'financier' => $data['financier'] ?? null
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil dibuat',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik' ? 'finance_batik' : 'finance_tourism';

            $transaction = Transaction::where('id', $id)
                ->when($financeRole, function ($query) use ($financeRole) {
                    return $query->where('finance_role', $financeRole);
                })->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            $validator = ValidationHelper::validateTransaction($request->all(), false);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 400);
            }

            $data = $validator->validated();
            $orderId = $transaction->order_id;

            if (
                $data['category'] === 'Penjualan Tiket' &&
                $request->has('ticket_id') &&
                $data['type'] === 'income' &&
                in_array($userRole, ['finance_tourism', 'admin_tourism'])
            ) {
                if ($orderId) {
                    $order = Order::find($orderId);
                    if ($order) {
                        $order->update([
                            'ticket_id' => $data['ticket_id'],
                            'quantity' => $data['quantity'] ?? 1,
                            'total_price' => $data['amount'],
                            'order_date' => Carbon::parse($data['transaction_date'])->setTimezone(config('app.timezone')),
                        ]);
                    }
                } else {
                    $order = Order::create([
                        'user_id' => $data['user_id'] ?? null,
                        'ticket_id' => $data['ticket_id'],
                        'name' => $data['name'],
                        'channel' => 'offline',
                        'payment_method' => 'cash',
                        'status' => 'paid',
                        'quantity' => $data['quantity'] ?? 1,
                        'total_price' => $data['amount'],
                        'order_date' => Carbon::parse($data['transaction_date'])->setTimezone(config('app.timezone')),
                        'qr_code' => null,
                    ]);
                    $orderId = $order->id;
                }
            } else {
                $orderId = null;
            }

            $transaction->update([
                'user_id' => $data['user_id'] ?? $transaction->user_id,
                'title' => $data['title'] ?? $transaction->title,
                'type' => $data['type'] ?? $transaction->type,
                'category' => $data['category'] ?? $transaction->category,
                'amount' => $data['amount'] ?? $transaction->amount,
                'finance_role' => $financeRole ?? $transaction->finance_role,
                'transaction_date' => $data['transaction_date'] ? Carbon::parse($data['transaction_date']) : $transaction->transaction_date,
                'order_id' => $orderId,
                'financier' => $data['financier'] ?? $transaction->financier,
                'name' => $data['name'] ?? $transaction->name,
            ]);

            return response()->json([
                'message' => 'Transaksi berhasil diperbarui',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik' ? 'finance_batik' : null;

            $transaction = Transaction::where('id', $id)
                ->when($financeRole, function ($query) use ($financeRole) {
                    return $query->where('finance_role', $financeRole);
                })->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            $transaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified resource by transaction date.
     */
    public function exportPdf(Request $request)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik'
                ? 'finance_batik'
                : ($userRole === 'admin_tourism' || $userRole === 'finance_tourism'
                    ? 'finance_tourism'
                    : null);

            $reportTitle = match ($financeRole) {
                'finance_batik' => 'Laporan Keuangan Batik Caraka',
                'finance_tourism' => 'Laporan Keuangan Wisata Bukit Pengaritan',
                default => 'Laporan Keuangan',
            };

            $query = Transaction::query();

            if ($financeRole) {
                $query->where('finance_role', $financeRole);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date, config('app.timezone'))->startOfDay();
                $end   = Carbon::parse($request->end_date, config('app.timezone'))->endOfDay();
                $query->whereBetween('transaction_date', [$start, $end]);
            }

            $transactions = $query->orderBy('transaction_date', 'asc')->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data transaksi untuk diekspor.'
                ], 404);
            }

            $income = $transactions->where('type', 'income')->sum('amount');
            $expense = $transactions->where('type', 'expense')->sum('amount');
            $balance = $income - $expense;

            $summary = [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance
            ];

            $pdf = Pdf::loadView('pdf.transactions', [
                'transactions' => $transactions,
                'summary' => $summary,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'report_title' => $reportTitle,
            ])->setPaper('a4', 'portrait');

            $filename = 'transaksi_' . Carbon::now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengunduh transaksi PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
