<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Master Departments
        $deptTech = Department::create(['name' => 'Technology', 'code' => 'TECH', 'status' => 'active']);
        $deptFinance = Department::create(['name' => 'Finance & Accounting', 'code' => 'FIN', 'status' => 'active']);
        $deptMarketing = Department::create(['name' => 'Marketing', 'code' => 'MKT', 'status' => 'active']);
        $deptHR = Department::create(['name' => 'Human Resources', 'code' => 'HRD', 'status' => 'active']);
        $deptGA = Department::create(['name' => 'General Affairs', 'code' => 'GA', 'status' => 'active']);

        // 2. Seed Master Categories
        $categories = [
            ['name' => 'Transport & Perjalanan', 'code' => 'TRP', 'type' => 'expense', 'description' => 'Biaya taksi, bensin, tiket pesawat/kereta'],
            ['name' => 'Akomodasi & Hotel', 'code' => 'AKM', 'type' => 'expense', 'description' => 'Penginapan dinas luar kota'],
            ['name' => 'Konsumsi & Jamuan Klien', 'code' => 'KNS', 'type' => 'expense', 'description' => 'Makan siang rapat & jamuan bisnis'],
            ['name' => 'ATK & Operasional Kantor', 'code' => 'ATK', 'type' => 'expense', 'description' => 'Kebutuhan perlengkapan harian'],
            ['name' => 'Software & Lisensi', 'code' => 'SFT', 'type' => 'expense', 'description' => 'Langganan SaaS & cloud server'],
            ['name' => 'Pendapatan Invoice Jasa', 'code' => 'INV', 'type' => 'income', 'description' => 'Penerimaan pembayaran dari klien'],
            ['name' => 'Penerimaan Kas Operasional', 'code' => 'KAS', 'type' => 'income', 'description' => 'Top up kas kecil & transfer operasional'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 3. Seed Users across 5 Roles
        $admin = User::create([
            'name' => 'Sistem Administrator',
            'email' => 'admin@office.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $director = User::create([
            'name' => 'Budi Direktur',
            'email' => 'director@office.test',
            'password' => Hash::make('password123'),
            'role' => 'director',
            'status' => 'active',
        ]);

        $finance = User::create([
            'name' => 'Siti Finance',
            'email' => 'finance@office.test',
            'password' => Hash::make('password123'),
            'role' => 'finance',
            'department_id' => $deptFinance->id,
            'department' => $deptFinance->name,
            'status' => 'active',
        ]);

        $manager = User::create([
            'name' => 'Ahmad Manager IT',
            'email' => 'manager@office.test',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'department_id' => $deptTech->id,
            'department' => $deptTech->name,
            'manager_id' => $director->id,
            'status' => 'active',
        ]);

        $deptTech->update(['manager_id' => $manager->id]);

        $employee = User::create([
            'name' => 'Rian Karyawan',
            'email' => 'employee@office.test',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'department_id' => $deptTech->id,
            'department' => $deptTech->name,
            'manager_id' => $manager->id,
            'status' => 'active',
        ]);

        // Akun nonaktif untuk uji coba keamanan
        User::create([
            'name' => 'User Nonaktif',
            'email' => 'inactive@office.test',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'department_id' => $deptTech->id,
            'department' => $deptTech->name,
            'status' => 'inactive',
        ]);
    }
}
