<?php
namespace App\Events;

class AiModelUnavailableEvent
{
    public function __construct(
        public readonly ?string $model,
        public readonly string $message
    ) {
    }
}
