<?php

namespace Ajtarragona\MailRelay\Mail;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;

class MailRelayTransport extends EsmtpTransport
{
    public function __construct(array $config)
    {
        // Pasamos los datos a la clase base de Symfony
        parent::__construct(
            $config['host'],
            $config['port'],
            $config['encryption'] ?? null
        );

        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        if ($message instanceof Email) {
            // Inyectamos el header para que Mailrelay sepa qué trackear
            $message->getHeaders()->addTextHeader('X-Metadata', json_encode([
                'package' => 'ajtarragona/mailrelay-client',
                'source' => config('app.url')
            ]));

            // Si quieres que Mailrelay fuerce el tracking de clics y aperturas
            // puedes añadir este si tu cuenta lo requiere por header:
            $message->getHeaders()->addTextHeader('X-Track-Opens', '1');
            $message->getHeaders()->addTextHeader('X-Track-Clicks', '1');
        }

        return parent::send($message, $envelope);
    }
}
