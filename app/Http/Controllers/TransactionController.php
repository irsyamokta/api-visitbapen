<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Helpers\ValidationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;
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
                'status' => 'error',
                'message' => 'Failed to retrieve transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analytics()
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik'
                ? 'finance_batik'
                : ($userRole === 'admin_tourism' || $userRole === 'finance_tourism'
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
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
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

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'type' => $request->type,
                'category' => $request->category,
                'amount' => $request->amount,
                'finance_role' => $financeRole,
                'transaction_date' => Carbon::parse($request->transaction_date)
                    ->setTimezone(config('app.timezone')),
                'order_id' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction: ' . $e->getMessage()
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
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transaction: ' . $e->getMessage()
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
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik' ? 'finance_batik' : null;

            $transaction = Transaction::where('id', $id)
                ->when($financeRole, function ($query) use ($financeRole) {
                    return $query->where('finance_role', $financeRole);
                })->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            $validator = ValidationHelper::validateTransaction($request->all(), false);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 400);
            }

            $transaction->update([
                'user_id' => $request->user_id ?? $transaction->user_id,
                'title' => $request->title ?? $transaction->title,
                'type' => $request->type ?? $transaction->type,
                'category' => $request->category ?? $transaction->category,
                'amount' => $request->amount ?? $transaction->amount,
                'finance_role' => $financeRole ?? $transaction->finance_role,
                'transaction_date' => $request->transaction_date ? Carbon::parse($request->transaction_date) : $transaction->transaction_date,
                'order_id' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction: ' . $e->getMessage()
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
                    'message' => 'Transaction not found'
                ], 404);
            }

            $transaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified resource by transaction date.
     */
    public function export(Request $request)
    {
        try {
            $userRole = auth()->user()->role ?? 'user';
            $financeRole = $userRole === 'admin_batik' || $userRole === 'finance_batik'
                ? 'finance_batik'
                : ($userRole === 'admin_tourism' || $userRole === 'finance_tourism'
                    ? 'finance_tourism'
                    : null);

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

            return Excel::download(new TransactionExport($transactions, $summary), 'transactions_' . Carbon::now()->format('Y-m-d') . '.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download transactions: ' . $e->getMessage()
            ], 500);
        }
    }
}
