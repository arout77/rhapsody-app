<?php
namespace App\Controllers;

use Rhapsody\Core\Controllers\BaseBillingController;
use Rhapsody\Core\Events\EventDispatcher;
use Rhapsody\Core\Events\PaymentFailedEvent;
use Rhapsody\Core\Events\PaymentSucceededEvent;
use Rhapsody\Core\Http\Request;
use Rhapsody\Core\Response;

class BillingController extends BaseBillingController
{
    public function __construct(
        protected PaymentGatewayInterface $gateway,
        protected EventDispatcher $dispatcher
    ) {
        parent::__construct($gateway);
    }

    public function charge(Request $request): Response
    {
        // Custom pre‑charge logic (e.g., validate order)

        // Execute the charge using the parent logic
        $response = parent::charge($request);

        // Decode the JSON response to know if it succeeded
        $data = json_decode($response->getContent(), true);

        if ($data['success'] ?? false) {
            // Dispatch a success event
            $this->dispatcher->dispatch(
                new PaymentSucceededEvent(
                    transactionId: $data['transaction_id'] ?? 'unknown',
                    amount: (float) $request->input('amount'),
                    metadata: ['order_id' => $request->input('order_id')]
                )
            );
        } else {
            // Dispatch a failure event
            $this->dispatcher->dispatch(
                new PaymentFailedEvent(
                    message: $data['message'] ?? 'Unknown error',
                    context: ['request' => $request->all()]
                )
            );
        }

        return $response;
    }
}
