<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    /**
     * Tampilkan antrean persetujuan biaya yang membutuhkan tindakan approver.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Mengambil pengajuan yang menunggu tindakan dari user bersangkutan
        $pendingApprovals = Reimbursement::pendingForUser($user)
            ->with(['user', 'items'])
            ->latest()
            ->paginate(15);

        return view('approvals.index', compact('pendingApprovals'));
    }

    /**
     * Setujui pengajuan reimbursement.
     */
    public function approve(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('approve', $reimbursement);

        $comment = $request->input('comment');

        try {
            $this->approvalService->approve($reimbursement, $request->user(), $comment);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Pengajuan {$reimbursement->number} berhasil disetujui.");
    }

    /**
     * Tolak pengajuan reimbursement (alasan wajib).
     */
    public function reject(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('reject', $reimbursement);

        $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penolakan wajib dicantumkan.',
        ]);

        try {
            $this->approvalService->reject($reimbursement, $request->user(), $request->input('reason'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Pengajuan {$reimbursement->number} telah ditolak.");
    }

    /**
     * Kembalikan pengajuan ke pemohon untuk revisi dokumen (catatan wajib).
     */
    public function returnForRevision(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('returnForRevision', $reimbursement);

        $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'comment.required' => 'Catatan instruksi perbaikan wajib diisi.',
        ]);

        try {
            $this->approvalService->returnForRevision($reimbursement, $request->user(), $request->input('comment'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Pengajuan {$reimbursement->number} dikembalikan ke pemohon untuk perbaikan.");
    }

    /**
     * Finance memproses pencairan dan pembayaran reimbursement.
     */
    public function pay(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $this->authorize('pay', $reimbursement);

        $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_account' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->approvalService->markAsPaid(
                $reimbursement,
                $request->user(),
                $request->input('payment_method'),
                $request->input('payment_account')
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reimbursements.show', $reimbursement)
            ->with('success', "Pengajuan {$reimbursement->number} telah dibayarkan dan dicatat pada transaksi kas kantor.");
    }
}
