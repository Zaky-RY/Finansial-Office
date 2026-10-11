<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Transaction::class);

        $type = $request->query('type', 'all');
        $status = $request->query('status');
        $department = $request->query('department');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('q');

        $query = Transaction::query()->with(['creator', 'reimbursement'])->latest('transaction_date')->latest('id');

        if ($type === 'income') {
            $query->income();
        } elseif ($type === 'expense') {
            $query->expense();
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($department) {
            $query->where('department', $department);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('source_reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Ringkasan saldo dan mutasi kas
        $summary = $this->transactionService->calculateBalance($department, $startDate, $endDate);
        $departments = Department::where('status', 'active')->get();

        return view('transactions.index', compact(
            'transactions',
            'summary',
            'departments',
            'type',
            'status',
            'department',
            'startDate',
            'endDate',
            'search'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Transaction::class);

        $categories = Category::active()->get();
        $departments = Department::where('status', 'active')->get();
        $budgets = Budget::active()->get();

        return view('transactions.create', compact('categories', 'departments', 'budgets'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $transaction = $this->transactionService->createTransaction(
            $request->validated(),
            $request->user(),
            $request->file('attachment')
        );

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi kas #{$transaction->transaction_number} berhasil dicatat.");
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['creator', 'canceller', 'reimbursement.user', 'budget']);

        return view('transactions.show', compact('transaction'));
    }

    public function cancel(CancelTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('cancel', $transaction);

        try {
            $this->transactionService->cancelTransaction(
                $transaction,
                $request->user(),
                $request->input('reason')
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi #{$transaction->transaction_number} telah dibatalkan.");
    }

    public function attachment(Transaction $transaction): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $transaction);

        if (! $transaction->attachment_path || ! Storage::disk('local')->exists($transaction->attachment_path)) {
            abort(404, 'Berkas lampiran bukti transaksi tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $transaction->attachment_path,
            basename($transaction->attachment_path)
        );
    }
}
