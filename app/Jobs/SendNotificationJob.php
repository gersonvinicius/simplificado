<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $recipient;
    protected $maxRetries = 10; // Número máximo de tentativas
    protected $retryDelay = 10; // Tempo de espera entre tentativas em segundos

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message, $recipient)
    {
        $this->message = $message;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            $response = Http::post('https://util.devi.tools/api/v1/notify', [
                'message' => $this->message,
                'to' => $this->recipient,
            ]);

            // Se o status for 204, a notificação foi enviada com sucesso
            if ($response->status() === 204) {
                Log::info('Notificação enviada com sucesso para o destinatário: ' . $this->recipient);
                return;
            }

            // Caso contrário, verifique o status e o conteúdo da resposta
            if ($response->status() === 504 || $response->json('status') === 'error') {
                Log::warning('Tentativa de notificação falhou. Tentativa ' . ($attempts + 1) . ' de ' . $this->maxRetries);
            }

            $attempts++;
            sleep($this->retryDelay); // Aguarda um tempo antes de tentar novamente
        }

        // Se esgotar todas as tentativas, lança uma exceção
        throw new \Exception('Falha ao enviar notificação após ' . $this->maxRetries . ' tentativas.');
    }
}
