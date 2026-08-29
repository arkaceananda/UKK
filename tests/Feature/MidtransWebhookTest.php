<?php

namespace Tests\Feature;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Events\StatsUpdated;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $serverKey = 'test-server-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.midtrans.server_key' => $this->serverKey]);
    }

    private function createTransaksi(string $orderId): Transaksi
    {
        $pesanan = Pesanan::factory()->create([
            'status' => StatusPesanan::Diterima,
        ]);

        return Transaksi::create([
            'pesanan_id' => $pesanan->id,
            'metode_bayar' => MetodeBayar::Qris->value,
            'total_bayar' => 25000,
            'status_bayar' => StatusBayar::Pending->value,
            'midtrans_order_id' => $orderId,
        ]);
    }

    private function signature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
    }

    public function test_rejects_invalid_signature(): void
    {
        $response = $this->postJson(route('midtrans.webhook'), [
            'order_id' => 'ORDER-1',
            'status_code' => '200',
            'gross_amount' => '25000',
            'signature_key' => 'wrong-signature',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403)->assertJson(['message' => 'Invalid signature']);
    }

    public function test_rejects_unknown_order(): void
    {
        $orderId = 'ORDER-UNKNOWN';
        $response = $this->postJson(route('midtrans.webhook'), [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '25000',
            'signature_key' => $this->signature($orderId, '200', '25000'),
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(404)->assertJson(['message' => 'Transaction not found']);
    }

    public function test_valid_settlement_marks_transaksi_lunas_and_dispatches_stats(): void
    {
        Event::fake([StatsUpdated::class]);

        $transaksi = $this->createTransaksi('ORDER-LUNAS');
        $orderId = $transaksi->midtrans_order_id;

        $response = $this->postJson(route('midtrans.webhook'), [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '25000',
            'signature_key' => $this->signature($orderId, '200', '25000'),
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'OK']);
        $this->assertSame(StatusBayar::Lunas, $transaksi->fresh()->status_bayar);

        Event::assertDispatched(StatsUpdated::class);
    }

    public function test_repeated_success_webhook_is_idempotent(): void
    {
        Event::fake([StatsUpdated::class]);

        $transaksi = $this->createTransaksi('ORDER-REPEAT');
        $transaksi->update(['status_bayar' => StatusBayar::Lunas->value]);
        $orderId = $transaksi->midtrans_order_id;

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '25000',
            'signature_key' => $this->signature($orderId, '200', '25000'),
            'transaction_status' => 'settlement',
        ];

        $this->postJson(route('midtrans.webhook'), $payload)->assertStatus(200);
        $this->postJson(route('midtrans.webhook'), $payload)->assertStatus(200);

        // Stats event should be dispatched at most once despite multiple notifications.
        Event::assertDispatchedTimes(StatsUpdated::class, 0);
    }

    public function test_failure_status_returns_to_pending(): void
    {
        Event::fake([StatsUpdated::class]);

        $transaksi = $this->createTransaksi('ORDER-DENY');
        $orderId = $transaksi->midtrans_order_id;

        $this->postJson(route('midtrans.webhook'), [
            'order_id' => $orderId,
            'status_code' => '202',
            'gross_amount' => '25000',
            'signature_key' => $this->signature($orderId, '202', '25000'),
            'transaction_status' => 'deny',
        ])->assertStatus(200);

        $this->assertSame(StatusBayar::Pending, $transaksi->fresh()->status_bayar);
        Event::assertNotDispatched(StatsUpdated::class);
    }
}
