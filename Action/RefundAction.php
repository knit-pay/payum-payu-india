<?php

namespace KnitPay\PayuIndia\Action;

use KnitPay\PayuIndia\Api;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Request\Refund;
use ArrayAccess;

class RefundAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Refund $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getFirstModel();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = 10 ** $currency->exp;

        return $this->api->cancel_refund_transaction($payment->getNumber(), uniqid( 'refund_' ), $payment->getTotalAmount() / $divisor);
    }

    public function supports($request)
    {
        return $request instanceof Refund &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
