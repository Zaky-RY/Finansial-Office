<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;

    private User $employeeUser;

    private User $managerUser;

    private User $directorUser;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->financeUser = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);
        $this->employeeUser = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $this->managerUser = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);
        $this->directorUser = User::factory()->create(['role' => 'director', 'department' => 'Management', 'status' => 'active']);
        $this->adminUser = User::factory()->create(['role' => 'admin', 'department' => 'IT', 'status' => 'active']);

        Department::create(['name' => 'Technology', 'code' => 'TECH', 'status' => 'active']);
        Department::create(['name' => 'Finance', 'code' => 'FIN', 'status' => 'active']);
        Category::create(['name' => 'Operasional Kantor', 'code' => 'OPS', 'type' => 'expense', 'status' => 'active']);
        Category::create(['name' => 'Penerimaan Jasa', 'code' => 'REV', 'type' => 'income', 'status' => 'active']);
    }

    public function test_finance_can_view_transactions_index_and_summary(): void
    {
        Transaction::create([
            'type' => 'income',
            'amount' => '10000000.00',
            'transaction_date' => '2026-10-10',
            'category' => 'Penerimaan Jasa',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Pendapatan Invoice 1',
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => '3000000.00',
            'transaction_date' => '2026-10-11',
            'category' => 'Operasional Kantor',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Biaya Internet & Server',
        ]);

        $response = $this->actingAs($this->financeUser)->get('/transactions');

        $response->assertOk();
        $response->assertSee('Daftar Transaksi Kas');
        $response->assertSee('Saldo Kas Berjalan');
        $response->assertSee('Rp 7.000.000,00'); // 10.000.000 - 3.000.000
        $response->assertSee('+Rp 10.000.000,00');
        $response->assertSee('Biaya Internet');
    }

    public function test_employee_is_forbidden_from_viewing_transactions_index(): void
    {
        $response = $this->actingAs($this->employeeUser)->get('/transactions');
        $response->assertForbidden();
    }

    public function test_manager_director_and_admin_can_view_transactions_index(): void
    {
        $this->actingAs($this->managerUser)->get('/transactions')->assertOk();
        $this->actingAs($this->directorUser)->get('/transactions')->assertOk();
        $this->actingAs($this->adminUser)->get('/transactions')->assertOk();
    }

    public function test_finance_can_create_income_transaction(): void
    {
        $payload = [
            'type' => 'income',
            'transaction_date' => '2026-10-11',
            'amount' => '25000000.00',
            'category' => 'Penerimaan Jasa',
            'department' => 'Technology',
            'vendor' => 'Klien PT Solusi Maju',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'paid',
            'description' => 'Pelunasan termin 1 pengembangan software',
            'source_reference' => 'INV-2026-001',
        ];

        $response = $this->actingAs($this->financeUser)->post('/transactions', $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('transactions', [
            'type' => 'income',
            'amount' => '25000000.00',
            'source_reference' => 'INV-2026-001',
            'created_by' => $this->financeUser->id,
            'payment_status' => 'paid',
        ]);

        // Verifikasi audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->financeUser->id,
            'action' => 'created',
            'module' => 'transaction',
        ]);
    }

    public function test_finance_can_create_expense_transaction_with_attachment(): void
    {
        $file = UploadedFile::fake()->create('bukti_transfer.pdf', 300, 'application/pdf');

        $payload = [
            'type' => 'expense',
            'transaction_date' => '2026-10-11',
            'amount' => '4500000.50',
            'category' => 'Operasional Kantor',
            'department' => 'Finance',
            'vendor' => 'Gedung Wisma Niaga',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'paid',
            'description' => 'Sewa ruang kantor bulan Oktober 2026',
            'attachment' => $file,
        ];

        $response = $this->actingAs($this->financeUser)->post('/transactions', $payload);

        $response->assertSessionHasNoErrors();
        $transaction = Transaction::where('vendor', 'Gedung Wisma Niaga')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('4500000.50', $transaction->amount);
        $this->assertNotNull($transaction->attachment_path);
        Storage::disk('local')->assertExists($transaction->attachment_path);
    }

    public function test_employee_cannot_create_transaction(): void
    {
        $payload = [
            'type' => 'expense',
            'transaction_date' => '2026-10-11',
            'amount' => '100000.00',
            'category' => 'Operasional Kantor',
            'payment_method' => 'Cash',
            'payment_status' => 'paid',
            'description' => 'Percobaan create oleh employee',
        ];

        $response = $this->actingAs($this->employeeUser)->post('/transactions', $payload);
        $response->assertForbidden();
    }

    public function test_transaction_creation_validation_rejects_negative_or_zero_amount(): void
    {
        $response = $this->actingAs($this->financeUser)->post('/transactions', [
            'type' => 'expense',
            'transaction_date' => '2026-10-11',
            'amount' => '-5000.00',
            'category' => 'Operasional',
            'payment_method' => 'Cash',
            'payment_status' => 'paid',
            'description' => 'Test negatif',
        ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_finance_can_view_transaction_detail(): void
    {
        $transaction = Transaction::create([
            'type' => 'expense',
            'amount' => '750000.00',
            'transaction_date' => '2026-10-11',
            'category' => 'Konsumsi & Rapat',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Snack meeting bulanan',
        ]);

        $response = $this->actingAs($this->financeUser)->get("/transactions/{$transaction->id}");

        $response->assertOk();
        $response->assertSee($transaction->transaction_number);
        $response->assertSee('Rp 750.000,00');
        $response->assertSee('Snack meeting bulanan');
    }

    public function test_finance_can_cancel_transaction_with_valid_reason_and_records_audit_log(): void
    {
        $transaction = Transaction::create([
            'type' => 'expense',
            'amount' => '1200000.00',
            'transaction_date' => '2026-10-11',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Pembelian printer kantor',
        ]);

        $response = $this->actingAs($this->financeUser)->post("/transactions/{$transaction->id}/cancel", [
            'reason' => 'Vendor kehabisan stok dan mengembalikan dana secara tunai',
        ]);

        $response->assertRedirect("/transactions/{$transaction->id}");
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertEquals('cancelled', $transaction->payment_status);
        $this->assertEquals($this->financeUser->id, $transaction->cancelled_by);
        $this->assertEquals('Vendor kehabisan stok dan mengembalikan dana secara tunai', $transaction->cancellation_reason);
        $this->assertNotNull($transaction->cancelled_at);

        // Pastikan audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->financeUser->id,
            'action' => 'cancelled',
            'module' => 'transaction',
        ]);
    }

    public function test_cancellation_requires_mandatory_reason(): void
    {
        $transaction = Transaction::create([
            'type' => 'income',
            'amount' => '500000.00',
            'transaction_date' => '2026-10-11',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Test cancel tanpa alasan',
        ]);

        $response = $this->actingAs($this->financeUser)->post("/transactions/{$transaction->id}/cancel", [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
        $transaction->refresh();
        $this->assertEquals('paid', $transaction->payment_status);
    }

    public function test_hard_delete_is_strictly_prohibited_by_policy(): void
    {
        $transaction = Transaction::create([
            'type' => 'expense',
            'amount' => '800000.00',
            'transaction_date' => '2026-10-11',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Test hard delete prohibited',
        ]);

        // Verifikasi bahwa Gate 'delete' mengembalikan false untuk semua role
        $this->assertFalse(Gate::forUser($this->financeUser)->allows('delete', $transaction));
        $this->assertFalse(Gate::forUser($this->adminUser)->allows('delete', $transaction));
        $this->assertFalse(Gate::forUser($this->directorUser)->allows('delete', $transaction));
    }

    public function test_reimbursement_paid_creates_transaction_linked_with_reimbursement_id(): void
    {
        $reimbursement = Reimbursement::create([
            'user_id' => $this->employeeUser->id,
            'department' => 'Technology',
            'category' => 'Transport',
            'purpose' => 'Reimbursement Dinas Bandung',
            'total_amount' => '1750000.00',
            'currency' => 'IDR',
            'status' => 'approved',
            'payment_method' => 'Bank Transfer',
            'payment_account' => 'BCA 9988776655',
        ]);

        $approvalService = app(ApprovalService::class);
        $approvalService->markAsPaid($reimbursement, $this->financeUser, 'TRF-BCA-2026-009');

        $reimbursement->refresh();
        $this->assertEquals('paid', is_object($reimbursement->status) ? $reimbursement->status->value : $reimbursement->status);

        // Verifikasi relasi transaction dibuat dan reimbursement_id tersimpan
        $transaction = Transaction::where('reimbursement_id', $reimbursement->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('expense', $transaction->type);
        $this->assertEquals('1750000.00', $transaction->amount);
        $this->assertEquals('paid', $transaction->payment_status);
        $this->assertEquals($reimbursement->reimbursement_number, $transaction->source_reference);

        // Verifikasi relasi Eloquent dua arah
        $this->assertEquals($transaction->id, $reimbursement->transaction->id);
        $this->assertEquals($reimbursement->id, $transaction->reimbursement->id);
    }

    public function test_dashboard_displays_actual_financial_data_for_authorized_users(): void
    {
        Transaction::create([
            'type' => 'income',
            'amount' => '50000000.00',
            'transaction_date' => '2026-10-10',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Pendapatan Kontrak PT Jaya',
        ]);

        Transaction::create([
            'type' => 'expense',
            'amount' => '20000000.00',
            'transaction_date' => '2026-10-11',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Gaji Vendor Eksternal',
        ]);

        $response = $this->actingAs($this->financeUser)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ringkasan Posisi Kas & Keuangan', false);
        $response->assertSee('Rp 30.000.000,00'); // Saldo berjalan aktual: 50.000.000 - 20.000.000
        $response->assertSee('+Rp 50.000.000,00');
        $response->assertSee('Pendapatan Kontrak PT Jaya');
    }

    public function test_transactions_can_be_filtered_by_type_status_and_search_query(): void
    {
        $trx1 = Transaction::create([
            'type' => 'income',
            'amount' => '1000000.00',
            'transaction_date' => '2026-10-01',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Penjualan Aset Lama',
        ]);

        $trx2 = Transaction::create([
            'type' => 'expense',
            'amount' => '2000000.00',
            'transaction_date' => '2026-10-02',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Beli Laptop Developer',
        ]);

        // Filter type=income
        $response = $this->actingAs($this->financeUser)->get('/transactions?type=income');
        $response->assertOk();
        $response->assertSee($trx1->transaction_number);
        $response->assertDontSee($trx2->transaction_number);

        // Filter search=Laptop
        $response = $this->actingAs($this->financeUser)->get('/transactions?q=Laptop');
        $response->assertOk();
        $response->assertDontSee($trx1->transaction_number);
        $response->assertSee($trx2->transaction_number);
    }

    public function test_transaction_attachment_access_authorization(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('transactions/receipt_123.pdf', 'dummy invoice content');

        $trx = Transaction::create([
            'transaction_number' => 'TRX-ATT-001',
            'type' => 'expense',
            'amount' => '1500000.00',
            'transaction_date' => '2026-10-10',
            'category' => 'Operasional',
            'department' => 'Technology',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'attachment_path' => 'transactions/receipt_123.pdf',
            'description' => 'Pembelian Lisensi',
        ]);

        // Finance berhak mengunduh bukti transaksi
        $responseFinance = $this->actingAs($this->financeUser)->get("/transactions/{$trx->id}/attachment");
        $responseFinance->assertOk();

        // Employee dilarang mengunduh bukti transaksi kas kantor
        $responseEmployee = $this->actingAs($this->employeeUser)->get("/transactions/{$trx->id}/attachment");
        $responseEmployee->assertForbidden();
    }
}
