<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BudgetManagementTest extends TestCase
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

        $this->financeUser = User::factory()->create(['role' => 'finance', 'department' => 'Finance', 'status' => 'active']);
        $this->employeeUser = User::factory()->create(['role' => 'employee', 'department' => 'Technology', 'status' => 'active']);
        $this->managerUser = User::factory()->create(['role' => 'manager', 'department' => 'Technology', 'status' => 'active']);
        $this->directorUser = User::factory()->create(['role' => 'director', 'department' => 'Management', 'status' => 'active']);
        $this->adminUser = User::factory()->create(['role' => 'admin', 'department' => 'IT', 'status' => 'active']);

        Department::create(['name' => 'Technology', 'code' => 'TECH', 'status' => 'active']);
        Department::create(['name' => 'Finance', 'code' => 'FIN', 'status' => 'active']);
        Category::create(['name' => 'Infrastruktur Server', 'code' => 'INFRA', 'type' => 'expense', 'status' => 'active']);
    }

    public function test_finance_and_admin_can_view_budgets_index_with_overall_metrics(): void
    {
        Budget::create([
            'name' => 'Budget Operasional Q4 IT',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '100000000.00',
            'used_amount' => '25000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get('/budgets');

        $response->assertOk();
        $response->assertSee('Daftar Anggaran (Budget)');
        $response->assertSee('Total Pagu Alokasi');
        $response->assertSee('Rp 100.000.000,00');
        $response->assertSee('Rp 25.000.000,00');
        $response->assertSee('Rp 75.000.000,00');
        $response->assertSee('Budget Operasional Q4 IT');
    }

    public function test_manager_can_view_budgets_index_for_own_department(): void
    {
        Budget::create([
            'name' => 'Budget Riset IT',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '50000000.00',
            'used_amount' => '0.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)->get('/budgets');
        $response->assertOk();
        $response->assertSee('Budget Riset IT');
    }

    public function test_employee_is_forbidden_from_viewing_budgets_index(): void
    {
        $response = $this->actingAs($this->employeeUser)->get('/budgets');
        $response->assertForbidden();
    }

    public function test_finance_can_create_new_budget_and_audit_log_is_recorded(): void
    {
        $payload = [
            'name' => 'Anggaran Pelatihan Tim Q4',
            'department' => 'Technology',
            'category' => 'Infrastruktur Server',
            'project' => 'Cloud Certification 2026',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '30000000.00',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->financeUser)->post('/budgets', $payload);

        $response->assertSessionHasNoErrors();
        $budget = Budget::where('name', 'Anggaran Pelatihan Tim Q4')->first();
        $this->assertNotNull($budget);
        $this->assertEquals('30000000.00', $budget->allocated_amount);
        $this->assertEquals('0.00', (string) $budget->used_amount);
        $this->assertEquals('30000000.00', $budget->remaining_amount);
        $response->assertRedirect("/budgets/{$budget->id}");

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->financeUser->id,
            'action' => 'created',
            'module' => 'budget',
        ]);
    }

    public function test_creating_budget_validation_rejects_negative_or_zero_amount(): void
    {
        $response = $this->actingAs($this->financeUser)->post('/budgets', [
            'name' => 'Invalid Budget',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '-1000.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['allocated_amount']);
    }

    public function test_creating_budget_validation_requires_period_end_after_or_equal_period_start(): void
    {
        $response = $this->actingAs($this->financeUser)->post('/budgets', [
            'name' => 'Invalid Dates',
            'department' => 'Technology',
            'period_start' => '2026-12-31',
            'period_end' => '2026-10-01', // Lebih awal dari period_start
            'allocated_amount' => '10000000.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['period_end']);
    }

    public function test_employee_cannot_create_budget(): void
    {
        $response = $this->actingAs($this->employeeUser)->post('/budgets', [
            'name' => 'Employee Budget Try',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
        ]);

        $response->assertForbidden();
    }

    public function test_finance_can_view_budget_detail_and_usage_summary(): void
    {
        $budget = Budget::create([
            'name' => 'Anggaran Server Baremetal',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '80000000.00',
            'used_amount' => '20000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get("/budgets/{$budget->id}");

        $response->assertOk();
        $response->assertSee('Anggaran Server Baremetal');
        $response->assertSee('Rp 80.000.000,00');
        $response->assertSee('Rp 20.000.000,00');
        $response->assertSee('Rp 60.000.000,00');
        $response->assertSee('25%'); // 20 / 80 = 25%
    }

    public function test_finance_can_update_budget_allocation_and_details(): void
    {
        $budget = Budget::create([
            'name' => 'Anggaran Lisensi Software',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '40000000.00',
            'used_amount' => '10000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->put("/budgets/{$budget->id}", [
            'name' => 'Anggaran Lisensi Software (Revisi Pagu)',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '60000000.00',
            'status' => 'active',
        ]);

        $response->assertRedirect("/budgets/{$budget->id}");
        $budget->refresh();
        $this->assertEquals('Anggaran Lisensi Software (Revisi Pagu)', $budget->name);
        $this->assertEquals('60000000.00', $budget->allocated_amount);
        $this->assertEquals('50000000.00', $budget->remaining_amount);
    }

    public function test_updating_budget_allocation_cannot_be_reduced_below_current_used_amount(): void
    {
        $budget = Budget::create([
            'name' => 'Anggaran Keamanan Siber',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '50000000.00',
            'used_amount' => '30000000.00', // Sudah terpakai 30 jt
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        // Coba turunkan alokasi ke 20 jt (lebih rendah dari dana terpakai 30 jt)
        $response = $this->actingAs($this->financeUser)->put("/budgets/{$budget->id}", [
            'name' => 'Anggaran Keamanan Siber',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '20000000.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['allocated_amount']);
        $budget->refresh();
        $this->assertEquals('50000000.00', $budget->allocated_amount);
    }

    public function test_finance_or_director_can_close_active_budget(): void
    {
        $budget = Budget::create([
            'name' => 'Anggaran Q3 Selesai',
            'department' => 'Technology',
            'period_start' => '2026-07-01',
            'period_end' => '2026-09-30',
            'allocated_amount' => '10000000.00',
            'used_amount' => '9500000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->post("/budgets/{$budget->id}/close");

        $response->assertRedirect("/budgets/{$budget->id}");
        $budget->refresh();
        $this->assertEquals('closed', $budget->status);

        // Verifikasi audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->financeUser->id,
            'action' => 'closed',
            'module' => 'budget',
        ]);
    }

    public function test_deleting_budget_with_used_amount_greater_than_zero_is_strictly_forbidden(): void
    {
        $budget = Budget::create([
            'name' => 'Budget Terpakai',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '500000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $this->assertFalse(Gate::forUser($this->financeUser)->allows('delete', $budget));
        $this->assertFalse(Gate::forUser($this->adminUser)->allows('delete', $budget));
    }

    public function test_creating_expense_transaction_with_budget_id_atomically_records_budget_usage(): void
    {
        $budget = Budget::create([
            'name' => 'Pagu Hardware Q4',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '20000000.00',
            'used_amount' => '0.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->post('/transactions', [
            'type' => 'expense',
            'transaction_date' => '2026-10-11',
            'amount' => '6500000.00',
            'category' => 'Infrastruktur Server',
            'department' => 'Technology',
            'vendor' => 'PT Mega Data Server',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'paid',
            'description' => 'Beli Switch 24 Port',
            'budget_id' => $budget->id,
        ]);

        $response->assertSessionHasNoErrors();
        $budget->refresh();

        // Pemakaian bertambah menjadi 6.500.000,00 dan sisa menjadi 13.500.000,00
        $this->assertEquals('6500000.00', (string) $budget->used_amount);
        $this->assertEquals('13500000.00', $budget->remaining_amount);
        $this->assertEquals(32.5, $budget->usage_percentage);

        // Verifikasi transaksi terhubung
        $transaction = Transaction::where('vendor', 'PT Mega Data Server')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals($budget->id, $transaction->budget_id);
        $this->assertEquals($transaction->budget->id, $budget->id);
    }

    public function test_cancelling_expense_transaction_atomically_releases_budget_usage(): void
    {
        $budget = Budget::create([
            'name' => 'Pagu Cloud Q4',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '4000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $transaction = Transaction::create([
            'type' => 'expense',
            'transaction_date' => '2026-10-11',
            'amount' => '4000000.00',
            'category' => 'Infrastruktur Server',
            'department' => 'Technology',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'paid',
            'description' => 'Invoice Cloud Hosting AWS',
            'budget_id' => $budget->id,
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->post("/transactions/{$transaction->id}/cancel", [
            'reason' => 'Salah input pos anggaran, tagihan dibatalkan',
        ]);

        $response->assertRedirect("/transactions/{$transaction->id}");
        $budget->refresh();

        // Pemakaian kembali berkurang menjadi 0.00
        $this->assertEquals('0.00', (string) $budget->used_amount);
        $this->assertEquals('10000000.00', $budget->remaining_amount);
    }

    public function test_budget_alert_thresholds_render_correct_badges(): void
    {
        // 75% -> Warning
        $bWarning = Budget::create([
            'name' => 'Budget 75%',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '7500000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals('Peringatan (75%)', $bWarning->alert_label);
        $this->assertEquals('badge-warning', $bWarning->alert_badge_class);

        // 90% -> High Warning
        $bHigh = Budget::create([
            'name' => 'Budget 90%',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '9000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals('Peringatan Tinggi (90%)', $bHigh->alert_label);
        $this->assertEquals('badge-amber', $bHigh->alert_badge_class);

        // 100% -> Exhausted
        $bExhausted = Budget::create([
            'name' => 'Budget 100%',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '10000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals('Batas Terlampaui (100%)', $bExhausted->alert_label);
        $this->assertEquals('badge-danger', $bExhausted->alert_badge_class);
    }

    public function test_dashboard_displays_budget_summary_widget_when_active_budgets_exist(): void
    {
        Budget::create([
            'name' => 'Anggaran Dashboard Test',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '50000000.00',
            'used_amount' => '10000000.00',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $response = $this->actingAs($this->financeUser)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Serapan Pagu Anggaran Operasional');
        $response->assertSee('Kelola Anggaran');
        $response->assertSee('Rp 50.000.000,00');
        $response->assertSee('Rp 10.000.000,00');
        $response->assertSee('Rp 40.000.000,00');
    }
}
