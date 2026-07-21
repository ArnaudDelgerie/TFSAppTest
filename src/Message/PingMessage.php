<?php

namespace App\Message;

/**
 * Demo message for the async-worker/Mercure validation (plan 028): dispatched
 * by TFSAppController::ping(), handled by PingMessageHandler. Carries a
 * correlation id so the browser can tell its own dispatch apart from another
 * one on the same shared Mercure topic.
 */
final class PingMessage
{
    public const TOPIC = 'https://tfsapp-test.local/ping';

    public function __construct(
        public readonly string $correlationId,
        public readonly string $text,
    ) {
    }
}
