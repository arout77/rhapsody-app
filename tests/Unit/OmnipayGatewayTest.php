<?php
namespace Tests\Unit;

use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Helpers\OmnipayGateway;

/**
 * A minimal fake of Omnipay's GatewayInterface.
 *
 * Omnipay gateways expose purchase()/authorize()/etc via __call() magic
 * (they're not part of GatewayInterface itself — see vendor/omnipay/common's
 * GatewayInterface, which only declares getName/getShortName/getParameters/
 * initialize/getDefaultParameters). That means PHPUnit's mock builder can't
 * stub purchase() directly on a GatewayInterface mock, so a small fake
 * implementing the real interface is the simplest reliable way to test
 * OmnipayGateway's use of purchase() without hitting a real payment API.
 */
class FakeOmnipayGateway implements GatewayInterface
{
    /** @var array<int, array> */
    public array $purchaseCalls = [];
    public $purchaseReturn;

    public function getName()
    {
        return 'Fake';
    }

    public function getShortName()
    {
        return 'fake';
    }

    public function getDefaultParameters()
    {
        return [];
    }

    public function initialize(array $parameters = [])
    {
        return $this;
    }

    public function getParameters()
    {
        return [];
    }

    public function purchase(array $options = [])
    {
        $this->purchaseCalls[] = $options;
        return $this->purchaseReturn;
    }
}

class OmnipayGatewayTest extends TestCase
{
    protected FakeOmnipayGateway $gateway;
    protected OmnipayGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway        = new FakeOmnipayGateway();
        $this->paymentGateway = new OmnipayGateway($this->gateway);
    }

    private function fakeRequest(ResponseInterface $response): object
    {
        return new class ($response) {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function send(): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    public function test_it_successfully_charges_a_payment(): void
    {
        $amount = 1000;
        $token  = 'tok_12345';

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('isSuccessful')->willReturn(true);
        $responseMock->method('getTransactionReference')->willReturn('tr_abc123');

        $this->gateway->purchaseReturn = $this->fakeRequest($responseMock);

        $result = $this->paymentGateway->charge($amount, $token);

        $this->assertSame([[
            'amount'   => $amount,
            'currency' => 'USD',
            'token'    => $token,
        ]], $this->gateway->purchaseCalls);

        $this->assertTrue($result['success']);
        $this->assertSame('tr_abc123', $result['transaction_id']);
        $this->assertSame('Payment approved.', $result['message']);
        $this->assertSame('USD', $result['currency']);
    }

    public function test_it_handles_failed_charges(): void
    {
        $amount = 1000;
        $token  = 'tok_invalid';

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('isSuccessful')->willReturn(false);
        $responseMock->method('getMessage')->willReturn('Card declined');

        $this->gateway->purchaseReturn = $this->fakeRequest($responseMock);

        $result = $this->paymentGateway->charge($amount, $token);

        $this->assertFalse($result['success']);
        $this->assertSame('Card declined', $result['message']);
        $this->assertArrayNotHasKey('currency', $result);
    }

    public function test_it_uses_the_configured_default_currency(): void
    {
        $paymentGateway = new OmnipayGateway($this->gateway, 'EUR');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('isSuccessful')->willReturn(true);
        $responseMock->method('getTransactionReference')->willReturn('tr_eur1');

        $this->gateway->purchaseReturn = $this->fakeRequest($responseMock);

        $result = $paymentGateway->charge(1000, 'tok_12345');

        $this->assertSame('EUR', $this->gateway->purchaseCalls[0]['currency']);
        $this->assertSame('EUR', $result['currency']);
    }

    public function test_an_explicit_currency_option_overrides_the_default(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('isSuccessful')->willReturn(true);
        $responseMock->method('getTransactionReference')->willReturn('tr_gbp1');

        $this->gateway->purchaseReturn = $this->fakeRequest($responseMock);

        $result = $this->paymentGateway->charge(1000, 'tok_12345', ['currency' => 'GBP']);

        $this->assertSame('GBP', $this->gateway->purchaseCalls[0]['currency']);
        $this->assertSame('GBP', $result['currency']);
    }
}
