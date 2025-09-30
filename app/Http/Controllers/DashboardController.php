<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
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

            $transactions = $query->orderBy('transaction_date', 'desc')->get();

            $totalIncome = $transactions->where('type', 'income')->sum('amount');
            $totalExpense = $transactions->where('type', 'expense')->sum('amount');

            $balance = $totalIncome - $totalExpense;

            $totalTransactions = $transactions->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => [
                        'saldo' => $balance,
                        'total_income' => $totalIncome,
                        'total_expense' => $totalExpense,
                        'total_transactions' => $totalTransactions,
                    ],
                    'recent_transactions' => $transactions->take(5),
                    'overview' => $transactions,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}
