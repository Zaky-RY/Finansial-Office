<?php

namespace App\Http\Controllers;

use App\Enums\ReimbursementStatus;
use App\Http\Requests\StoreReimbursementRequest;
use App\Http\Requests\SubmitReimbursementRequest;
use App\Models\Category;
use App\Models\Reimbursement;
use App\Services\ApprovalService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReimbursementController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService,
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'all');
        $search = $request->query('q');

        $query = $user->reimbursements()->with(['items', 'approvals'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($tab === 'draft') {
            $query->where('status', ReimbursementStatus::DRAFT->value);
        } elseif ($tab === 'pending') {
            $query->whereIn('status', [
                ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
                ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value,
                ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value,
                'submitted',
            ]);
        } elseif ($tab === 'approved') {
            $query->where('status', ReimbursementStatus::APPROVED->value);
        } elseif ($tab === 'paid') {
            $query->whereIn('status', [ReimbursementStatus::PAID->value, ReimbursementStatus::COMPLETED->value]);
        } elseif ($tab === 'rejected') {
            $query->where('status', ReimbursementStatus::REJECTED->value);
        } elseif ($tab === 'returned') {
            $query->whereIn('status', [ReimbursementStatus::RETURNED_FOR_REVISION->value, 'returned']);
        }

        $reimbursements = $query->paginate(10)->withQueryString();

        // Metrics untuk KPI summary card
        $allUserReimbursements = $user->reimbursements();
        $totalClaims = $allUserReimbursements->clone()->where('status', '!=', ReimbursementStatus::REJECTED->value)->sum('total_amount');
        $pendingCount = $allUserReimbursements->clone()->whereIn('status', [
            ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
            ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value,
            ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value,
            'submitted',
        ])->count();
        $approvedCount = $allUserReimbursements->clone()->whereIn('status', [
            ReimbursementStatus::APPROVED->value,
            ReimbursementStatus::PAID->value,
            ReimbursementStatus::COMPLETED->value,
        ])->count();
        $rejectedCount = $allUserReimbursements->clone()->where('status', ReimbursementStatus::REJECTED->value)->count();

        return view('reimbursements.index', compact(
            'reimbursements',
            'tab',
            'search',
            'totalClaims',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Reimbursement::class);

        $categories = Category::active()->expense()->get();

        return view('reimbursements.create', [
            'reimbursement' => new Reimbursement([
                'department' => auth()->user()->department,
            ]),
            'categories' => $categories,
        ]);
    }

    public function store(StoreReimbursementRequest $request): RedirectResponse
    {
        $this->authorize('create', Reimbursement::class);

        $reimbursement = new Reimbursement($request->safe()->except(['attachment', 'items']));
        $reimbursement->user()->associate($request->user());

        $this->persist($reimbursement, $request->file('attachment'), $request->input('items', []));

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Draft reimbursement berhasil disimpan.');
    }

    public function show(Reimbursement $reimbursement): View
    {
        $this->authorize('view', $reimbursement);

        $reimbursement->load(['items', 'approvals.approver', 'user', 'paidByUser']);

        return view('reimbursements.show', compact('reimbursement'));
    }

    public function edit(Reimbursement $reimbursement): View
    {
        $this->authorize('update', $reimbursement);

        $reimbursement->load('items');
        $categories = Category::active()->expense()->get();

        return view('reimbursements.create', compact('reimbursement', 'categories'));
    }

    public function update(StoreReimbursementRequest $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('update', $reimbursement);

        $reimbursement->fill($request->safe()->except(['attachment', 'items']));
        $this->persist($reimbursement, $request->file('attachment'), $request->input('items', []));

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Draft pengajuan reimbursement berhasil diperbarui.');
    }

    public function submit(SubmitReimbursementRequest $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('submit', $reimbursement);

        $reimbursement->fill($request->safe()->except(['attachment', 'items']));
        $this->persist($reimbursement, $request->file('attachment'), $request->input('items', []));

        try {
            $this->approvalService->submit($reimbursement, $request->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', 'Pengajuan reimbursement berhasil dikirim dan masuk ke antrean approval.');
    }

    public function attachment(Reimbursement $reimbursement): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $reimbursement);

        if (! $reimbursement->attachment_path || ! Storage::disk('local')->exists($reimbursement->attachment_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $reimbursement->attachment_path,
            $reimbursement->attachment_original_name ?? basename($reimbursement->attachment_path)
        );
    }

    private function persist(Reimbursement $reimbursement, ?UploadedFile $attachment, array $itemsData): void
    {
        DB::transaction(function () use ($reimbursement, $attachment, $itemsData) {
            if ($attachment) {
                $storedName = Str::uuid().'.'.$attachment->getClientOriginalExtension();
                $path = $attachment->storeAs('reimbursements', $storedName, 'local');
                $reimbursement->attachment_path = $path;
                $reimbursement->attachment_original_name = $attachment->getClientOriginalName();
                $reimbursement->attachment_mime_type = $attachment->getMimeType();
                $reimbursement->attachment_size = $attachment->getSize();
            }

            $reimbursement->save();

            // Simpan / update item-item detail jika disediakan
            if (! empty($itemsData)) {
                $reimbursement->items()->delete();
                foreach ($itemsData as $item) {
                    if (! empty($item['amount']) && (float) $item['amount'] > 0) {
                        $reimbursement->items()->create([
                            'expense_date' => $item['expense_date'] ?? $reimbursement->transaction_date ?? now()->toDateString(),
                            'category' => $item['category'] ?? $reimbursement->category ?? 'General',
                            'description' => $item['description'] ?? 'Detail Pengeluaran',
                            'amount' => $item['amount'],
                            'vendor' => $item['vendor'] ?? null,
                            'receipt_number' => $item['receipt_number'] ?? null,
                        ]);
                    }
                }
            }

            // Hitung ulang total amount dengan presisi bcmath
            $reimbursement->calculateTotal();
            $reimbursement->save();

            $this->auditLogService->log(
                auth()->id(),
                $reimbursement->wasRecentlyCreated ? 'created' : 'updated',
                'reimbursement',
                $reimbursement,
                null,
                ['total_amount' => $reimbursement->total_amount, 'status' => $reimbursement->status]
            );
        });
    }
}
