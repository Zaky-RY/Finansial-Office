<?php

namespace Tests\Unit;

use App\Models\Approval;
use App\Models\Reimbursement;
use App\Models\ReimbursementItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReimbursementCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reimbursement_can_have_multiple_items_and_calculate_total_accurately(): void
    {
        $user = User::factory()->create([
            'role' => 'employee',
        ]);

        $reimbursement = Reimbursement::create([
            'user_id' => $user->id,
            'purpose' => 'Kunjungan Klien Surabaya',
            'department' => 'Sales',
            'status' => 'draft',
        ]);

        ReimbursementItem::create([
            'reimbursement_id' => $reimbursement->id,
            'expense_date' => '2026-10-10',
            'category' => 'Transport',
            'description' => 'Tiket Kereta Api PP',
            'amount' => '750000.50',
            'vendor' => 'PT KAI',
            'receipt_number' => 'INV-001',
        ]);

        ReimbursementItem::create([
            'reimbursement_id' => $reimbursement->id,
            'expense_date' => '2026-10-11',
            'category' => 'Akomodasi',
            'description' => 'Hotel 1 Malam',
            'amount' => '650000.25',
            'vendor' => 'Hotel Santika',
            'receipt_number' => 'INV-002',
        ]);

        $reimbursement->load('items');
        $total = $reimbursement->calculateTotal();
        $reimbursement->save();

        $this->assertEquals('1400000.75', $total);
        $this->assertEquals('1400000.75', (string) $reimbursement->fresh()->total_amount);
        $this->assertCount(2, $reimbursement->items);
    }

    public function test_reimbursement_can_have_approvals_history(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $manager = User::factory()->create(['role' => 'manager']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'purpose' => 'Pembelian ATK',
            'status' => 'waiting_manager_approval',
        ]);

        $approval = Approval::create([
            'approvable_type' => Reimbursement::class,
            'approvable_id' => $reimbursement->id,
            'approver_id' => $manager->id,
            'approval_level' => 'manager',
            'status' => 'approved',
            'comment' => 'Disetujui untuk kebutuhan operasional',
            'decided_at' => now(),
        ]);

        $this->assertCount(1, $reimbursement->approvals);
        $this->assertEquals('manager', $reimbursement->approvals->first()->approval_level);
        $this->assertEquals($manager->id, $reimbursement->approvals->first()->approver_id);
    }
}
