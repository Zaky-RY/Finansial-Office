<?php

namespace Tests\Feature;

use App\Enums\ReimbursementStatus;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReimbursementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_employee_can_create_draft_reimbursement_with_multiple_items(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);

        $response = $this->actingAs($employee)->post('/reimbursements', [
            'department' => 'Technology',
            'category' => 'Transport & Perjalanan',
            'purpose' => 'Supervisi Deployment Server Surabaya',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 1234567890',
            'items' => [
                [
                    'expense_date' => '2026-10-10',
                    'description' => 'Tiket Kereta PP',
                    'category' => 'Transport',
                    'amount' => '1100000.00',
                    'receipt_number' => 'KAI-001',
                ],
                [
                    'expense_date' => '2026-10-11',
                    'description' => 'Grab Taxi',
                    'category' => 'Transport',
                    'amount' => '400000.00',
                    'receipt_number' => 'GRAB-002',
                ],
            ],
            'attachment' => UploadedFile::fake()->create('nota.pdf', 500, 'application/pdf'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursements', [
            'user_id' => $employee->id,
            'status' => ReimbursementStatus::DRAFT->value,
            'total_amount' => '1500000.00',
        ]);
        $this->assertDatabaseCount('reimbursement_items', 2);
    }

    public function test_submission_requires_attachment_and_positive_amount(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Uji Coba',
            'total_amount' => '0.00',
            'amount' => '0.00',
            'status' => ReimbursementStatus::DRAFT->value,
        ]);

        $response = $this->actingAs($employee)->post("/reimbursements/{$reimbursement->id}/submit", [
            'department' => 'Technology',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 12345',
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(ReimbursementStatus::DRAFT->value, $reimbursement->fresh()->status);
    }

    public function test_submitting_reimbursement_transitions_to_waiting_manager_approval(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Beli Peralatan',
            'total_amount' => '750000.00',
            'amount' => '750000.00',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 12345',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::DRAFT->value,
        ]);

        $response = $this->actingAs($employee)->post("/reimbursements/{$reimbursement->id}/submit", [
            'department' => 'Technology',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 12345',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::WAITING_MANAGER_APPROVAL->value, $reimbursement->fresh()->status);
        $this->assertNotNull($reimbursement->fresh()->submitted_at);
    }

    public function test_manager_can_approve_advancing_to_finance_verification(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Perjalanan Dinas',
            'total_amount' => '2500000.00',
            'amount' => '2500000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        $response = $this->actingAs($manager)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Disetujui untuk operasional IT',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value, $reimbursement->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_id' => $reimbursement->id,
            'approver_id' => $manager->id,
            'approval_level' => 'manager',
            'status' => 'approved',
        ]);
    }

    public function test_finance_approval_below_or_equal_to_threshold_marks_as_approved(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);

        // Nominal <= Rp 5.000.000 (tidak perlu direktur)
        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Biaya Server Bulanan',
            'total_amount' => '4500000.00',
            'amount' => '4500000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value,
        ]);

        $response = $this->actingAs($finance)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Nota diverifikasi lengkap dan valid',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::APPROVED->value, $reimbursement->fresh()->status);
    }

    public function test_finance_approval_above_threshold_advances_to_director_approval(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);

        // Nominal > Rp 5.000.000 (Rp 7.500.000 -> wajib direktur)
        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Upgrade Switch Data Center',
            'total_amount' => '7500000.00',
            'amount' => '7500000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value,
        ]);

        $response = $this->actingAs($finance)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Verifikasi Finance selesai, diteruskan ke Direktur',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value, $reimbursement->fresh()->status);
    }

    public function test_director_can_approve_high_value_reimbursement(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $director = User::factory()->create(['role' => 'director', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Upgrade Switch Data Center',
            'total_amount' => '7500000.00',
            'amount' => '7500000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_DIRECTOR_APPROVAL->value,
        ]);

        $response = $this->actingAs($director)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Disetujui Direksi',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::APPROVED->value, $reimbursement->fresh()->status);
    }

    public function test_approver_can_reject_reimbursement_with_mandatory_reason(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Klaim Tidak Jelas',
            'total_amount' => '1000000.00',
            'amount' => '1000000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        $response = $this->actingAs($manager)->post("/approvals/{$reimbursement->id}/reject", [
            'reason' => 'Nota tidak sesuai dengan surat tugas perjalanan dinas.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::REJECTED->value, $reimbursement->fresh()->status);
        $this->assertEquals('Nota tidak sesuai dengan surat tugas perjalanan dinas.', $reimbursement->fresh()->rejection_reason);
    }

    public function test_approver_can_return_reimbursement_for_revision(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Beli Bensin',
            'total_amount' => '300000.00',
            'amount' => '300000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value,
        ]);

        $response = $this->actingAs($finance)->post("/approvals/{$reimbursement->id}/return", [
            'comment' => 'Harap lampirkan foto struk SPBU yang lebih jelas dan tidak buram.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::RETURNED_FOR_REVISION->value, $reimbursement->fresh()->status);
        $this->assertTrue($reimbursement->fresh()->isEditable());
    }

    public function test_anti_self_approval_blocks_manager_from_approving_own_submission(): void
    {
        // Manager mengajukan reimbursement pribadi
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $manager->id,
            'department' => 'Technology',
            'purpose' => 'Reimbursement Manager Pribadi',
            'total_amount' => '1500000.00',
            'amount' => '1500000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        // Manager mencoba menyetujui pengajuannya sendiri
        $response = $this->actingAs($manager)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Saya setujui klaim saya sendiri',
        ]);

        // Wajib ditolak dengan status 403 Forbidden
        $response->assertStatus(403);
        $this->assertEquals(ReimbursementStatus::WAITING_MANAGER_APPROVAL->value, $reimbursement->fresh()->status);
    }

    public function test_manager_reimbursement_escalates_to_director_and_can_be_approved_by_director(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);
        $director = User::factory()->create(['role' => 'director', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $manager->id,
            'department' => 'Technology',
            'purpose' => 'Klaim Perjalanan Dinas Manager',
            'total_amount' => '2000000.00',
            'amount' => '2000000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::DRAFT->value,
        ]);

        $item = $reimbursement->items()->create([
            'expense_date' => '2026-10-11',
            'category' => 'Transport',
            'description' => 'Tiket Pesawat Dinas',
            'amount' => '2000000.00',
        ]);

        // Manager submit reimbursement
        $submitResponse = $this->actingAs($manager)->post("/reimbursements/{$reimbursement->id}/submit", [
            'department' => 'Technology',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 98765',
        ]);

        $submitResponse->assertSessionHasNoErrors();
        $reimbursement->refresh();
        $this->assertEquals(ReimbursementStatus::WAITING_MANAGER_APPROVAL->value, $reimbursement->status);
        $this->assertTrue($reimbursement->isManagerClaim());

        // Direktur menerima notifikasi eskalasi approval
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $director->id,
            'title' => 'Eskalasi Approval: Klaim Manager',
        ]);

        // Direktur menyetujui pengajuan eskalasi milik Manager
        $response = $this->actingAs($director)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Disetujui oleh Direktur untuk klaim pimpinan departemen',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::WAITING_FINANCE_VERIFICATION->value, $reimbursement->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_id' => $reimbursement->id,
            'approver_id' => $director->id,
            'status' => 'approved',
        ]);
    }

    public function test_finance_can_mark_approved_reimbursement_as_paid_and_creates_transaction(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'category' => 'Transport',
            'purpose' => 'Dinas Solo',
            'total_amount' => '1200000.00',
            'amount' => '1200000.00',
            'status' => ReimbursementStatus::APPROVED->value,
            'attachment_path' => 'reimbursements/dummy.pdf',
        ]);

        $response = $this->actingAs($finance)->post("/approvals/{$reimbursement->id}/pay", [
            'payment_method' => 'Bank Transfer BCA',
            'payment_account' => 'BCA 9988776655',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(ReimbursementStatus::PAID->value, $reimbursement->fresh()->status);
        $this->assertEquals($finance->id, $reimbursement->fresh()->paid_by);

        // Memastikan record Transaction pengeluaran kas terbentuk secara otomatis
        $this->assertDatabaseHas('transactions', [
            'type' => 'expense',
            'amount' => '1200000.00',
            'source_reference' => $reimbursement->number,
            'payment_status' => 'paid',
            'created_by' => $finance->id,
        ]);
    }

    public function test_idor_protection_prevents_unauthorized_employee_from_viewing_others_reimbursement(): void
    {
        $employeeA = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $employeeB = User::factory()->create(['role' => 'employee', 'department' => 'Marketing', 'status' => 'active']);

        $reimbursementA = Reimbursement::create([
            'user_id' => $employeeA->id,
            'department' => 'Technology',
            'purpose' => 'Klaim Rahasia Tim IT',
            'total_amount' => '500000.00',
            'status' => ReimbursementStatus::DRAFT->value,
        ]);

        // Employee B mencoba mengakses halaman detail milik Employee A
        $response = $this->actingAs($employeeB)->get("/reimbursements/{$reimbursementA->id}");

        $response->assertStatus(403);
    }

    public function test_approval_actions_record_audit_log_and_notifications(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'purpose' => 'Beli Toolkit Hardware',
            'total_amount' => '900000.00',
            'amount' => '900000.00',
            'attachment_path' => 'reimbursements/dummy.pdf',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        $this->actingAs($manager)->post("/approvals/{$reimbursement->id}/approve", [
            'comment' => 'Disetujui',
        ]);

        // Cek audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $manager->id,
            'action' => 'approved',
            'module' => 'reimbursement',
            'auditable_id' => $reimbursement->id,
        ]);

        // Cek in-app notification ke pemohon
        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $employee->id,
            'is_read' => false,
        ]);
    }

    public function test_attachment_access_authorization_and_idor_protection(): void
    {
        $owner = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $otherEmployee = User::factory()->create(['role' => 'employee', 'department' => 'Marketing', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        Storage::disk('local')->put('reimbursements/secret_receipt.pdf', 'dummy receipt binary');

        $reimbursement = Reimbursement::create([
            'user_id' => $owner->id,
            'department' => 'Technology',
            'purpose' => 'Nota Rahasia',
            'total_amount' => '350000.00',
            'amount' => '350000.00',
            'attachment_path' => 'reimbursements/secret_receipt.pdf',
            'attachment_original_name' => 'secret_receipt.pdf',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        // Pemilik berhak mengunduh lampiran
        $ownerResponse = $this->actingAs($owner)->get("/reimbursements/{$reimbursement->id}/attachment");
        $ownerResponse->assertOk();

        // Manager divisinya berhak mengunduh lampiran
        $managerResponse = $this->actingAs($manager)->get("/reimbursements/{$reimbursement->id}/attachment");
        $managerResponse->assertOk();

        // Karyawan divisi lain ditolak keras (IDOR prevention)
        $otherResponse = $this->actingAs($otherEmployee)->get("/reimbursements/{$reimbursement->id}/attachment");
        $otherResponse->assertForbidden();
    }

    public function test_reimbursement_show_page_renders_stitch_compliant_details_and_decision_panel(): void
    {
        $employee = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $manager = User::factory()->create(['name' => 'Hendra Wijaya', 'role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        $reimbursement = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'category' => 'Transportasi',
            'project' => 'Implementasi Core Banking',
            'purpose' => 'Kunjungan dinas supervisi onsite deployment server',
            'total_amount' => '1500000.00',
            'amount' => '1500000.00',
            'payment_method' => 'Bank Central Asia (BCA)',
            'payment_account' => '8820-192-441',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        $reimbursement->items()->create([
            'description' => 'Tiket Kereta Eksekutif Gambir - Pasar Turi PP',
            'category' => 'Transportasi',
            'amount' => '1100000.00',
            'vendor' => 'PT Kereta Api Indonesia',
            'receipt_number' => 'KAI-99482X',
            'expense_date' => now()->toDateString(),
        ]);

        $reimbursement->items()->create([
            'description' => 'Transportasi Lokal Grab Taxi',
            'category' => 'Transportasi',
            'amount' => '400000.00',
            'vendor' => 'Grab',
            'receipt_number' => 'GRAB-8821',
            'expense_date' => now()->toDateString(),
        ]);

        // Karyawan pemilik melihat detail
        $employeeView = $this->actingAs($employee)->get("/reimbursements/{$reimbursement->id}");
        $employeeView->assertOk();
        $employeeView->assertSee('Review Pengajuan: '.$reimbursement->number);
        $employeeView->assertSee('SOP-FIN-04');
        $employeeView->assertSee('Budi Santoso');
        $employeeView->assertSee('Tiket Kereta Eksekutif Gambir - Pasar Turi PP');
        $employeeView->assertSee('Rp 1.500.000,00');
        $employeeView->assertSee('Alur Verifikasi &amp; Audit Log', false);

        // Manager melihat detail dan panel keputusan persetujuan
        $managerView = $this->actingAs($manager)->get("/reimbursements/{$reimbursement->id}");
        $managerView->assertOk();
        $managerView->assertSee('Keputusan Approval');
        $managerView->assertSee('Setujui &amp; Lanjutkan', false);
        $managerView->assertSee('Kembalikan untuk Revisi');
        $managerView->assertSee('Tolak Pengajuan');
    }

    public function test_approval_queue_page_renders_stitch_compliant_queue_and_modals_for_approvers(): void
    {
        $employee = User::factory()->create(['name' => 'Budi Staff', 'role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $manager = User::factory()->create(['name' => 'Hendra Manager', 'role' => 'manager', 'department' => 'Technology', 'status' => 'active']);

        // Employee biasa dilarang mengakses antrean approval
        $this->actingAs($employee)->get('/approvals')->assertForbidden();

        // Buat pengajuan subordinat yang menunggu approval manager
        $subordinateClaim = Reimbursement::create([
            'user_id' => $employee->id,
            'department' => 'Technology',
            'category' => 'Transportasi',
            'purpose' => 'Kunjungan Server Surabaya',
            'total_amount' => '1500000.00',
            'amount' => '1500000.00',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        // Buat pengajuan manager sendiri (harus dicegah anti-self-approval dari antreannya)
        $ownClaim = Reimbursement::create([
            'user_id' => $manager->id,
            'department' => 'Technology',
            'category' => 'Operasional',
            'purpose' => 'Klaim Pribadi Manager',
            'total_amount' => '2000000.00',
            'amount' => '2000000.00',
            'status' => ReimbursementStatus::WAITING_MANAGER_APPROVAL->value,
        ]);

        // Manager mengakses antrean approval
        $response = $this->actingAs($manager)->get('/approvals');
        $response->assertOk();
        $response->assertSee('Antrean Persetujuan Biaya');
        $response->assertSee('SOP-FIN-04');
        $response->assertSee('Budi Staff');
        $response->assertSee('Kunjungan Server Surabaya');
        $response->assertSee('Rp 1.500.000,00');
        $response->assertSee('Menunggu Tindakan');
        $response->assertSee('Total Nilai Antrean');
        $response->assertSee('approveModal');
        $response->assertSee('returnModal');
        $response->assertSee('rejectModal');

        // Pastikan klaim milik manager sendiri TIDAK muncul di antreannya (Anti-Self-Approval)
        $response->assertDontSee('Klaim Pribadi Manager');
    }
}
