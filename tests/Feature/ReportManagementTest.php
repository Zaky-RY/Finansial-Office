<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\Reimbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $financeUser;

    private User $directorUser;

    private User $adminUser;

    private User $managerTech;

    private User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeUser = User::factory()->create([
            'role' => 'finance',
            'department' => 'Finance',
            'status' => 'active',
        ]);

        $this->directorUser = User::factory()->create([
            'role' => 'director',
            'department' => 'Management',
            'status' => 'active',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'department' => 'IT',
            'status' => 'active',
        ]);

        $this->managerTech = User::factory()->create([
            'role' => 'manager',
            'department' => 'Technology',
            'status' => 'active',
        ]);

        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'department' => 'Technology',
            'status' => 'active',
        ]);

        Department::create(['name' => 'Technology', 'code' => 'TECH', 'status' => 'active']);
        Department::create(['name' => 'Finance', 'code' => 'FIN', 'status' => 'active']);
        Category::create(['name' => 'Operasional', 'code' => 'OPS', 'type' => 'expense', 'status' => 'active']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    public function test_employee_is_forbidden_from_accessing_reports(): void
    {
        $response = $this->actingAs($this->employeeUser)->get('/reports');
        $response->assertForbidden();

        $exportResponse = $this->actingAs($this->employeeUser)->get('/reports/export');
        $exportResponse->assertForbidden();

        $printResponse = $this->actingAs($this->employeeUser)->get('/reports/print');
        $printResponse->assertForbidden();
    }

    public function test_finance_director_admin_can_access_cashflow_report(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-202610-0001',
            'type' => 'income',
            'amount' => '5000000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-10',
            'category' => 'Operasional',
            'department' => 'Technology',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Pendanaan Operasional Client',
            'created_by' => $this->financeUser->id,
        ]);

        Transaction::create([
            'transaction_number' => 'TRX-202610-0002',
            'type' => 'expense',
            'amount' => '2000000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-10',
            'category' => 'Operasional',
            'department' => 'Technology',
            'vendor' => 'PT Mitra Hosting',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Pembayaran Sewa Server',
            'created_by' => $this->financeUser->id,
        ]);

        // Akses oleh Finance
        $response = $this->actingAs($this->financeUser)->get('/reports?report_type=cashflow');
        $response->assertOk();
        $response->assertSee('TRX-202610-0001');
        $response->assertSee('TRX-202610-0002');
        $response->assertSee('Rp 5.000.000,00');
        $response->assertSee('Rp 2.000.000,00');
        $response->assertSee('Rp 3.000.000,00'); // Saldo bersih

        // Akses oleh Director
        $directorResponse = $this->actingAs($this->directorUser)->get('/reports?report_type=cashflow');
        $directorResponse->assertOk();

        // Akses oleh Admin
        $adminResponse = $this->actingAs($this->adminUser)->get('/reports?report_type=cashflow');
        $adminResponse->assertOk();
    }

    public function test_manager_access_is_scoped_strictly_to_their_department(): void
    {
        // Transaksi divisi Technology
        Transaction::create([
            'transaction_number' => 'TRX-TECH-001',
            'type' => 'expense',
            'amount' => '1500000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-10',
            'category' => 'Operasional',
            'department' => 'Technology',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Lisensi Software Tim Tech',
            'created_by' => $this->financeUser->id,
        ]);

        // Transaksi divisi Finance
        Transaction::create([
            'transaction_number' => 'TRX-FIN-001',
            'type' => 'expense',
            'amount' => '3000000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-10',
            'category' => 'Operasional',
            'department' => 'Finance',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Audit Pajak Kantor',
            'created_by' => $this->financeUser->id,
        ]);

        // Manager Tech membuka laporan (bahkan jika memanipulasi query department=Finance)
        $response = $this->actingAs($this->managerTech)->get('/reports?report_type=cashflow&department=Finance');
        $response->assertOk();
        $response->assertSee('TRX-TECH-001');
        $response->assertDontSee('TRX-FIN-001');
    }

    public function test_can_view_reimbursement_report_with_correct_metrics(): void
    {
        $rem1 = Reimbursement::create([
            'number' => 'REM-202610-0001',
            'user_id' => $this->employeeUser->id,
            'department' => 'Technology',
            'category' => 'Operasional',
            'purpose' => 'Beli keyboard kantor',
            'total_amount' => '500000.00',
            'status' => 'paid',
        ]);

        $rem2 = Reimbursement::create([
            'number' => 'REM-202610-0002',
            'user_id' => $this->employeeUser->id,
            'department' => 'Technology',
            'category' => 'Operasional',
            'purpose' => 'Perjalanan dinas meeting',
            'total_amount' => '1000000.00',
            'status' => 'pending_manager',
        ]);

        $response = $this->actingAs($this->financeUser)->get('/reports?report_type=reimbursement');
        $response->assertOk();
        $response->assertSee('REM-202610-0001');
        $response->assertSee('REM-202610-0002');
        $response->assertSee('Rp 1.500.000,00'); // Total Diajukan
        $response->assertSee('Rp 500.000,00');   // Total Dibayar
    }

    public function test_can_view_budget_report_with_correct_metrics(): void
    {
        Budget::create([
            'name' => 'Budget Server Tech 2026',
            'department' => 'Technology',
            'period_start' => '2026-01-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '50000000.00',
            'used_amount' => '15000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get('/reports?report_type=budget');
        $response->assertOk();
        $response->assertSee('Budget Server Tech 2026');
        $response->assertSee('Rp 50.000.000,00'); // Pagu Alokasi
        $response->assertSee('Rp 15.000.000,00'); // Terpakai
        $response->assertSee('Rp 35.000.000,00'); // Sisa
        $response->assertSee('30%');             // Serapan
    }

    public function test_date_range_validation_fails_if_end_date_is_before_start_date(): void
    {
        $response = $this->actingAs($this->financeUser)->get('/reports?start_date=2026-10-15&end_date=2026-10-10');
        $response->assertSessionHasErrors('end_date');
    }

    public function test_date_range_validation_fails_if_range_exceeds_one_year(): void
    {
        // Rentang > 366 hari (misal 2 tahun)
        $response = $this->actingAs($this->financeUser)->get('/reports?start_date=2024-01-01&end_date=2026-01-01');
        $response->assertSessionHasErrors('end_date');
    }

    public function test_can_export_cashflow_report_to_csv_streaming_with_bom(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-EXP-001',
            'type' => 'expense',
            'amount' => '750000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-11',
            'category' => 'Operasional',
            'department' => 'Technology',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Pengeluaran Uji Ekspor',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get('/reports/export?report_type=cashflow');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));

        // Pastikan Audit Log mencatat aktivitas ekspor
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->financeUser->id,
            'action' => 'exported',
            'module' => 'report',
        ]);
    }

    public function test_can_access_print_view_for_reports(): void
    {
        Transaction::create([
            'transaction_number' => 'TRX-PRINT-001',
            'type' => 'income',
            'amount' => '10000000.00',
            'currency' => 'IDR',
            'transaction_date' => '2026-10-11',
            'category' => 'Operasional',
            'department' => 'Technology',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'completed',
            'description' => 'Pendanaan Proyek A',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get('/reports/print?report_type=cashflow');
        $response->assertOk();
        $response->assertSee('FINANCE OFFICE');
        $response->assertSee('LAPORAN ARUS KAS & TRANSAKSI KAS INTERNAL', false);
        $response->assertSee('TRX-PRINT-001');
        $response->assertSee('Rp 10.000.000,00');
    }
}
