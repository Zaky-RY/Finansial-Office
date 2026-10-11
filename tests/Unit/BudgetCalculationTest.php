<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCalculationTest extends TestCase
{
    use RefreshDatabase;

    private BudgetService $service;

    private User $financeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BudgetService;
        $this->financeUser = User::factory()->create(['role' => 'finance']);
    }

    public function test_sisa_anggaran_is_calculated_accurately(): void
    {
        // Alokasi: Rp 100.000.000,00 | Terpakai: Rp 45.500.250,50
        $budget = Budget::create([
            'name' => 'Anggaran Q4 IT',
            'department' => 'Technology',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '100000000.00',
            'used_amount' => '45500250.50',
            'status' => 'active',
            'created_by' => $this->financeUser->id,
        ]);

        $summary = $this->service->getBudgetSummary($budget);

        // Sisa = 100.000.000,00 - 45.500.250,50 = 54.499.749,50
        $this->assertEquals('54499749.50', $summary['remaining_amount']);
        $this->assertEquals(45.50, $summary['usage_percentage']);
        $this->assertEquals(Budget::ALERT_NORMAL, $summary['alert_status']);
    }

    public function test_budget_threshold_warnings_at_75_90_and_100_percent(): void
    {
        // 1. Uji 74.99% -> Normal
        $budgetNormal = Budget::create([
            'name' => 'Budget Marketing',
            'department' => 'Marketing',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '7490000.00', // 74.9%
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals(Budget::ALERT_NORMAL, $budgetNormal->alert_status);

        // 2. Uji 75.00% -> Warning (>= 75%)
        $budgetWarning = Budget::create([
            'name' => 'Budget Operasional',
            'department' => 'General Affairs',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '7500000.00', // 75.0%
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals(Budget::ALERT_WARNING, $budgetWarning->alert_status);

        // 3. Uji 90.00% -> High Warning (>= 90%)
        $budgetHighWarning = Budget::create([
            'name' => 'Budget HRD',
            'department' => 'Human Resources',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '9000000.00', // 90.0%
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals(Budget::ALERT_HIGH_WARNING, $budgetHighWarning->alert_status);

        // 4. Uji 100.00% -> Budget Exhausted (>= 100%)
        $budgetExhausted = Budget::create([
            'name' => 'Budget Legal',
            'department' => 'Legal',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '10000000.00', // 100.0%
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals(Budget::ALERT_EXHAUSTED, $budgetExhausted->alert_status);
        $this->assertTrue($budgetExhausted->isExhausted());

        // 5. Uji Lebih dari 100% (Over budget) -> Budget Exhausted
        $budgetOver = Budget::create([
            'name' => 'Budget Proyek Over',
            'department' => 'Sales',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '10000000.00',
            'used_amount' => '10500000.00', // 105.0%
            'created_by' => $this->financeUser->id,
        ]);
        $this->assertEquals(Budget::ALERT_EXHAUSTED, $budgetOver->alert_status);
        $this->assertEquals('-500000.00', $budgetOver->remaining_amount);
        $this->assertFalse($budgetOver->canAccommodate('1000.00'));
    }

    public function test_budget_usage_can_be_recorded_atomically(): void
    {
        $budget = Budget::create([
            'name' => 'Budget Q4 Desain',
            'department' => 'Product',
            'period_start' => '2026-10-01',
            'period_end' => '2026-12-31',
            'allocated_amount' => '20000000.00',
            'used_amount' => '10000000.00',
            'created_by' => $this->financeUser->id,
        ]);

        $this->assertTrue($budget->canAccommodate('5000000.00'));

        $updated = $this->service->recordUsage($budget, '5000000.00');

        $this->assertEquals('15000000.00', (string) $updated->used_amount);
        $this->assertEquals('5000000.00', $updated->remaining_amount);
        $this->assertEquals(75.0, $updated->usage_percentage);
        $this->assertEquals(Budget::ALERT_WARNING, $updated->alert_status);
    }
}
