<?php
namespace App\Listeners;

use App\Events\PaymentSucceededEvent;
use Doctrine\ORM\EntityManager;

class UpdateOrderStatus
{
    public function __construct(
        protected EntityManager $em
    ) {}

    public function handle(PaymentSucceededEvent $event): void
    {
        // Assuming the metadata contains 'order_id'
        $orderId = $event->metadata['order_id'] ?? null;

        if ($orderId) {
            // Fetch the order entity and update its status
            // $order = $this->em->getRepository(Order::class)->find($orderId);
            // if ($order) {
            //     $order->setStatus('paid');
            //     $order->setTransactionId($event->transactionId);
            //     $this->em->flush();
            // }

            // Placeholder: log the update
            error_log(sprintf(
                '[Payment] Order %s marked as paid (txn: %s)',
                $orderId,
                $event->transactionId
            ));
        }
    }
}
