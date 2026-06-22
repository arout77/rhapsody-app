<?php
namespace Tests\Unit;

use Mockery;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Rhapsody\Core\Helpers\OmnipayGateway;

class OmnipayGatewayTest extends TestCase
{
    protected $gatewayMock;
    protected $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Omnipay GatewayInterface
        $this->gatewayMock = Mockery::mock(GatewayInterface::class);

        // Instantiate our helper with the mock
        $this->paymentGateway = new OmnipayGateway($this->gatewayMock);
    }

    public function test_it_successfully_charges_a_payment(): void
    {
        $amount = 1000;
        $token  = 'tok_12345';

        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('isSuccessful')->once()->andReturn(true);
        $responseMock->shouldReceive('getTransactionReference')->once()->andReturn('tr_abc123');

        $requestMock = Mockery::mock();
        $requestMock->shouldReceive('send')->once()->andReturn($responseMock);

        // Expect the purchase method to be called on the gateway with the correct payload
        $this->gatewayMock->shouldReceive('purchase')
            ->once()
            ->with([
                'amount'   => $amount,
                'currency' => 'USD',
                'token'    => $token,
            ])
            ->andReturn($requestMock);

        $result = $this->paymentGateway->charge($amount, $token);

        $this->assertTrue($result['success']);
        $this->assertEquals('tr_abc123', $result['transaction_id']);
        $this->assertEquals('Payment approved.', $result['message']);
    }

    public function test_it_handles_failed_charges(): void
    {
        $amount = 1000;
        $token  = 'tok_invalid';

        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('isSuccessful')->once()->andReturn(false);
        $responseMock->shouldReceive('getMessage')->once()->andReturn('Card declined');

        $requestMock = Mockery::mock();
        $requestMock->shouldReceive('send')->once()->andReturn($responseMock);

        $this->gatewayMock->shouldReceive('purchase')
            ->once()
            ->andReturn($requestMock);

        $result = $this->paymentGateway->charge($amount, $token);

        $this->assertFalse($result['success']);
        $this->assertEquals('Card declined', $result['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
