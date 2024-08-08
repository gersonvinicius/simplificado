<?php

namespace App\Services;

use App\Repositories\TransferRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransferService
{
    protected $transferRepository;
    protected $walletRepository;

    public function __construct(TransferRepository $transferRepository, WalletRepository $walletRepository)
    {
        $this->transferRepository = $transferRepository;
        $this->walletRepository = $walletRepository;
    }

    public function createTransfer($payerId, $payeeId, $value)
    {
        // Verificar se o pagador tem saldo suficiente
        $payerWallet = $this->walletRepository->findByUserId($payerId);
        if (!$payerWallet || $payerWallet[0]->balance < $value) {
            throw new \Exception('Saldo insuficiente');
        }

        // Consultar serviço autorizador externo
        $authorizationResponse = Http::get('https://util.devi.tools/api/v2/authorize');
        if ($authorizationResponse->json('data.authorization') !== true) {
            throw new \Exception('Transferência não autorizada');
        }

        // Iniciar transação
        DB::beginTransaction();

        try {
            // Atualizar saldos
            $this->walletRepository->updateBalance($payerId, -$value);
            $this->walletRepository->updateBalance($payeeId, $value);

            // Criar transferência
            $transfer = $this->transferRepository->create([
                'payer_id' => $payerId,
                'payee_id' => $payeeId,
                'value' => $value,
                'status' => 'completed',
            ]);

            // Enviar notificação (usando POST)
            $notificationResponse = Http::post('https://util.devi.tools/api/v1/notify', [
                'message' => 'Você recebeu um pagamento de R$' . $value,
                'to' => $payeeId,
            ]);

            if ($notificationResponse->json('status') !== 'success') {
                throw new \Exception('Falha ao enviar notificação');
            }

            DB::commit();
            return $transfer;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
