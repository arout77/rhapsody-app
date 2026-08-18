<?php
namespace App\Controllers;

use App\Events\AiModelUnavailableEvent;
use Rhapsody\Core\Ai\Exceptions\AiModelUnavailableException;
use Rhapsody\Core\Contracts\AiClientInterface;
use Rhapsody\Core\Controllers\BaseAiController;
use Rhapsody\Core\Events\EventDispatcher;

class AiController extends BaseAiController
{
    public function __construct(
        protected AiClientInterface $ai,
        protected EventDispatcher $dispatcher
    ) {
        parent::__construct($ai);
    }

    protected function onModelUnavailable(AiModelUnavailableException $e): void
    {
        $this->dispatcher->dispatch(new AiModelUnavailableEvent($e->model, $e->getMessage()));
    }
}
