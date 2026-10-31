<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private const CATEGORIES = [
        'operasional' => 'Operasional',
        'belanja_stok' => 'Belanja Stok',
        'tagihan' => 'Tagihan',
        'lainnya' => 'Lainnya',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:operasional,belanja_stok,tagihan,lainnya'],
            'per_page' => ['nullable', 'in:10,20,50,all,semua'],
        ]);
        $search = trim($filters['search'] ?? '');
        $perPageInput = $filters['per_page'] ?? '10';
        $perPage = ($perPageInput === 'all' || $perPageInput === 'semua') ? 1000 : (int) $perPageInput;
        if (!in_array($perPage, [10, 20, 50, 1000], true)) {
            $perPage = 10;
        }

        $expenses = Expense::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->latest('expense_date')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalExpenses = Expense::sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'search' => $search,
            'filters' => $filters,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'amount' => preg_replace('/\D/', '', (string) $request->input('amount', '')),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:operasional,belanja_stok,tagihan,lainnya'],
            'amount' => ['required', 'integer', 'min:1', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'expense_date' => ['required', 'date'],
        ]);

        $request->user()->expenses()->create($validated);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }
}
