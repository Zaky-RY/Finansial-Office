<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_relationship_with_manager_and_users(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'status' => 'active',
        ]);

        $dept = Department::create([
            'name' => 'Technology',
            'code' => 'TECH',
            'manager_id' => $manager->id,
            'status' => 'active',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'department_id' => $dept->id,
            'manager_id' => $manager->id,
            'status' => 'active',
        ]);

        $this->assertEquals($manager->id, $dept->manager->id);
        $this->assertTrue($dept->users->contains($employee));
        $this->assertEquals($dept->id, $employee->departmentRel->id);
        $this->assertEquals($manager->id, $employee->manager->id);
        $this->assertTrue($manager->subordinates->contains($employee));
    }

    public function test_category_scopes_income_and_expense_accurately(): void
    {
        Category::create(['name' => 'Transport', 'code' => 'TRP', 'type' => 'expense', 'status' => 'active']);
        Category::create(['name' => 'Invoice', 'code' => 'INV', 'type' => 'income', 'status' => 'active']);
        Category::create(['name' => 'Adjustment', 'code' => 'ADJ', 'type' => 'both', 'status' => 'active']);
        Category::create(['name' => 'Nonaktif', 'code' => 'NA', 'type' => 'expense', 'status' => 'inactive']);

        $this->assertCount(3, Category::active()->get());
        $this->assertCount(2, Category::active()->income()->get());
        $this->assertCount(2, Category::active()->expense()->get());
    }
}
