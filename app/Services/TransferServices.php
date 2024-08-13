<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Repositories\TransferRepository;
use App\Repositories\WalletRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class TransferServices
{
    protected $transferRepository;
    protected $walletRepository;
    protected $userRepository;

    public function __construct(TransferRepository $transferRepository, WalletRepository $walletRepository, UserRepository $userRepository)
    {
        $this->transferRepository = $transferRepository;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
    }

    public function createTransfer($payerId, $payeeId, $value)
    {
        // Validar IDs dos usuários e tipo de usuário
        $payer = $this->userRepository->findById($payerId);
        $payee = $this->userRepository->findById($payeeId);

        // Como o DB::select retorna um array, acessamos o primeiro elemento do array para pegar o objeto
        if (!$payer || count($payer) === 0 || !$payee || count($payee) === 0) {
            throw new \Exception('Usuário pagador ou recebedor não encontrado.');
        }

        $payer = $payer[0];
        $payee = $payee[0];

        // Lojistas não podem fazer transferências
        if ($payer->type !== 'user') {
            throw new \Exception('Lojistas não podem fazer transferências.');
        }

        if ($payee->id === $payer->id) {
            throw new \Exception('O pagador e o recebedor não podem ser o mesmo usuário.');
        }

        // Validar valor da transação
        if (!is_numeric($value) || $value <= 0) {
            throw new \Exception('Valor da transação inválido.');
        }

        // Verificar se o pagador tem saldo suficiente
        $payerWallet = $this->walletRepository->findByUserId($payerId);
        if (!$payerWallet || count($payerWallet) === 0 || $payerWallet[0]->balance < $value) {
            throw new \Exception('Saldo insuficiente.');
        }

        // Consultar serviço autorizador externo
        $authorizationResponse = Http::get('https://util.devi.tools/api/v2/authorize');
        if ($authorizationResponse->json('data.authorization') !== true) {
            throw new \Exception('Transferência não autorizada.');
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

            // Enfileirar a notificação
            $message = 'Você recebeu um pagamento de R$' . $value;
            $recipient = $payee->email; // ou o e-mail do destinatário, dependendo da API de notificação
            SendNotificationJob::dispatch($message, $recipient);

            DB::commit();
            return $transfer;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
