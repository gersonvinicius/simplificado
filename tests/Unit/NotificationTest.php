<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class NotificationTest extends TestCase
{
    public function test_it_sends_notification_successfully()
    {
            // Simular a resposta do serviço de notificação com sucesso
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response(null, 204),
        ]);

        // Configurar o mock para o log
        Log::shouldReceive('info')
            ->once()
            ->with('Notificação enviada com sucesso para o destinatário: recipient@example.com');

        // Executar o job
        $job = new SendNotificationJob('Você recebeu um pagamento', 'recipient@example.com');
        $job->handle();
    }

    public function test_it_fails_to_send_notification_if_service_is_unavailable()
    {
        // Simular a resposta do serviço de notificação com falha (status 504)
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response(null, 504),
        ]);

        // Simular o envio da notificação
        $message = 'Você recebeu um pagamento';
        $recipient = 'recipient@example.com';

        // Executar o job e capturar a exceção
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Falha ao enviar notificação após 10 tentativas.');

        $job = new SendNotificationJob($message, $recipient);
        $job->handle();
    }
}
