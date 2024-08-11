<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\TransferServices;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $transferService;

    public function setUp(): void
    {
        parent::setUp();
        $this->transferService = app(TransferServices::class);
    }

    /** @test */
    public function it_can_transfer_money_between_users_when_authorized()
    {
        // Fake a response where the transfer is authorized
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response(['status' => 'success', 'data' => ['authorization' => true]], 200),
            'https://util.devi.tools/api/v1/notify' => Http::response(['status' => 'success'], 200),
        ]);

        // Setup users and wallets with sufficient balance
        $payer = User::factory()->create();
        $payee = User::factory()->create();
        
        $payer->wallet()->create(['balance' => 1000]);
        $payee->wallet()->create(['balance' => 0]);

        // Execute transfer
        $this->transferService->createTransfer($payer->id, $payee->id, 500);

        // Assert that the transfer occurred and notification was sent
        $this->assertDatabaseHas('wallets', ['user_id' => $payer->id, 'balance' => 500]);
        $this->assertDatabaseHas('wallets', ['user_id' => $payee->id, 'balance' => 500]);
    }

    /** @test */
    public function it_throws_exception_if_transfer_is_not_authorized()
    {
        // Fake a response where the transfer is not authorized
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response(['status' => 'success', 'data' => ['authorization' => false]], 200),
        ]);

        // Setup users and wallets with sufficient balance
        $payer = User::factory()->create();
        $payee = User::factory()->create();

        $payer->wallet()->create(['balance' => 1000]);
        $payee->wallet()->create(['balance' => 0]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transferência não autorizada');

        // Attempt the transfer
        $this->transferService->createTransfer($payer->id, $payee->id, 500);
    }
}
