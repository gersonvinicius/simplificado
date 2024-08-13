<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransferServices;
use App\Repositories\WalletRepository;
use App\Repositories\TransferRepository;
use App\Repositories\UserRepository;
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
    public function test_it_can_transfer_money_between_users_when_authorized()
    {
        // Simular autorização bem-sucedida e tentativa de notificação
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response(['status' => 'success', 'data' => ['authorization' => true]], 200),
            'https://util.devi.tools/api/v1/notify' => Http::sequence()
                ->pushStatus(504)
                ->pushStatus(204), // Sucesso na segunda tentativa
        ]);

        // Configurar dados para o teste
        $payer = User::factory()->create();
        $payee = User::factory()->create();
        
        // Criar as wallets (carteiras) associadas aos usuários
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 500]);
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 0]);

        $value = 100;

        // Executar a transferência
        $transferService = new TransferServices(
            new TransferRepository(),
            new WalletRepository(),
            new UserRepository()
        );

        $transfer = $transferService->createTransfer($payer->id, $payee->id, $value);

        // Verifique se a transferência foi concluída
        $this->assertEquals('completed', $transfer->status);

        // Verifique se o saldo foi atualizado corretamente
        $this->assertEquals(400, $payerWallet->fresh()->balance);
        $this->assertEquals(100, $payeeWallet->fresh()->balance);
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

    public function test_it_throws_exception_if_payer_is_merchant()
    {
        // Crie um usuário do tipo lojista
        $merchant = User::factory()->create([
            'type' => 'merchant', // Ou qualquer valor que você use para identificar um lojista
        ]);

        // Crie um usuário comum que será o recebedor
        $user = User::factory()->create();

        // Defina o valor da transferência
        $value = 100;

        // Inicialize o serviço de transferência
        $transferService = new TransferServices(
            new TransferRepository(),
            new WalletRepository(),
            new UserRepository()
        );

        // Execute o teste, esperando que uma exceção seja lançada
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lojistas não podem fazer transferências.');

        // Tente fazer a transferência com o lojista como pagador
        $transferService->createTransfer($merchant->id, $user->id, $value);
    }

}
