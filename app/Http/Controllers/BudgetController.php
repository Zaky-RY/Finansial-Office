<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        protected BudgetService $budgetService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Budget::class);

        $user = $request->user();
        $department = $request->query('department');
        $status = $request->query('status');
        $search = $request->query('q');

        // Jika user adalah manager divisi, default batasi ke divisinya jika tidak ada filter
        if ($user->isManager() && ! $user->isFinance() && ! $user->isDirector() && ! $user->isAdmin()) {
            $department = $user->department;
        }

        $query = Budget::query()->with('creator')->latest('period_start')->latest('id');

        if ($department) {
            $query->where('department', $department);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $budgets = $query->paginate(15)->withQueryString();

        $metrics = $this->budgetService->getOverallBudgetMetrics($department);
        $departments = Department::where('status', 'active')->get();

        return view('budgets.index', compact(
            'budgets',
            'metrics',
            'departments',
            'department',
            'status',
            'search'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Budget::class);

        $departments = Department::where('status', 'active')->get();
        $categories = Category::active()->expense()->get();

        return view('budgets.create', compact('departments', 'categories'));
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->authorize('create', Budget::class);

        $budget = $this->budgetService->createBudget(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('budgets.show', $budget)
            ->with('success', "Alokasi anggaran '{$budget->name}' berhasil dibuat.");
    }

    public function show(Budget $budget): View
    {
        $this->authorize('view', $budget);

        $budget->load(['creator', 'transactions' => function ($q) {
            $q->with('creator')->latest('transaction_date');
        }]);

        $summary = $this->budgetService->getBudgetSummary($budget);

        return view('budgets.show', compact('budget', 'summary'));
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);

        $departments = Department::where('status', 'active')->get();
        $categories = Category::active()->expense()->get();

        return view('budgets.edit', compact('budget', 'departments', 'categories'));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        try {
            $this->budgetService->updateBudget(
                $budget,
                $request->validated(),
                $request->user()
            );
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['allocated_amount' => $e->getMessage()]);
        }

        return redirect()->route('budgets.show', $budget)
            ->with('success', "Anggaran '{$budget->name}' berhasil diperbarui.");
    }

    public function close(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorize('close', $budget);

        $this->budgetService->closeBudget($budget, $request->user());

        return redirect()->route('budgets.show', $budget)
            ->with('success', "Periode anggaran '{$budget->name}' telah resmi ditutup.");
    }
}
