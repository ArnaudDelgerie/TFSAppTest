<?php

namespace App\MessageHandler;

use App\Message\PingMessage;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PingMessageHandler
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    public function __invoke(PingMessage $message): void
    {
        // Simulated work, so the demo actually exercises async delivery
        // (dispatch returns immediately, completion arrives a few seconds
        // later via Mercure) instead of completing too fast to tell apart
        // from the sync:// inline path.
        sleep(3);

        $this->hub->publish(new Update(
            PingMessage::TOPIC,
            json_encode([
                'correlationId' => $message->correlationId,
                'text' => $message->text,
                'completedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ], JSON_THROW_ON_ERROR),
        ));
    }
}
