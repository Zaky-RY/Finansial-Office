<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionBalanceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    private User $financeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionService;
        $this->financeUser = User::factory()->create(['role' => 'finance']);
    }

    public function test_saldo_is_calculated_as_active_income_minus_active_expense(): void
    {
        // Pemasukan Aktif 1: Rp 15.000.000,50
        Transaction::create([
            'type' => 'income',
            'amount' => '15000000.50',
            'transaction_date' => '2026-10-01',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Pendapatan Invoice Proyek A',
        ]);

        // Pemasukan Aktif 2: Rp 5.000.000,25
        Transaction::create([
            'type' => 'income',
            'amount' => '5000000.25',
            'transaction_date' => '2026-10-05',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Penerimaan Kas Operasional',
        ]);

        // Pengeluaran Aktif: Rp 8.000.000,35
        Transaction::create([
            'type' => 'expense',
            'amount' => '8000000.35',
            'transaction_date' => '2026-10-08',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Pembayaran Sewa Kantor',
        ]);

        // Transaksi Dibatalkan (cancelled): Rp 2.000.000,00 -> TIDAK BOLEH DIHITUNG
        Transaction::create([
            'type' => 'expense',
            'amount' => '2000000.00',
            'transaction_date' => '2026-10-09',
            'payment_status' => 'cancelled',
            'cancellation_reason' => 'Salah input nominal',
            'created_by' => $this->financeUser->id,
            'description' => 'Pengeluaran Keliru',
        ]);

        $result = $this->service->calculateBalance();

        // Total Pemasukan = 15.000.000,50 + 5.000.000,25 = 20000000.75
        // Total Pengeluaran = 8.000.000,35 (transaksi cancelled diabaikan)
        // Saldo = 20000000.75 - 8000000.35 = 12000000.40
        $this->assertEquals('20000000.75', $result['total_income']);
        $this->assertEquals('8000000.35', $result['total_expense']);
        $this->assertEquals('12000000.40', $result['balance']);
        $this->assertEquals('Rp 12.000.000,40', $result['formatted_balance']);
    }

    public function test_cancelling_a_transaction_updates_status_and_requires_reason(): void
    {
        $transaction = Transaction::create([
            'type' => 'expense',
            'amount' => '1000000.00',
            'transaction_date' => '2026-10-10',
            'payment_status' => 'paid',
            'created_by' => $this->financeUser->id,
            'description' => 'Beli Meja Rapat',
        ]);

        $this->assertEquals('paid', $transaction->payment_status);

        $cancelled = $this->service->cancelTransaction($transaction, $this->financeUser, 'Pembelian dibatalkan oleh vendor');

        $this->assertEquals('cancelled', $cancelled->payment_status);
        $this->assertEquals($this->financeUser->id, $cancelled->cancelled_by);
        $this->assertEquals('Pembelian dibatalkan oleh vendor', $cancelled->cancellation_reason);
        $this->assertNotNull($cancelled->cancelled_at);

        // Pastikan transaksi yang telah dibatalkan tidak masuk dalam perhitungan saldo
        $result = $this->service->calculateBalance();
        $this->assertEquals('0.00', $result['total_expense']);
        $this->assertEquals('0.00', $result['balance']);
    }
}
