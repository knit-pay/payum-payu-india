<?php

namespace KnitPay\PayuIndia\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = 10 ** $currency->exp;

        $product_info = preg_replace( '/[^!-~\s]/', '', $payment->getDescription() );

        $params = [
            'txnid'              => $payment->getNumber(),
            'amount'             => $payment->getTotalAmount() / $divisor,
            'productinfo'        => $product_info,
            'firstname'          => '',
            'lastname'           => '',
            'phone'              => '',
            'email'              => $payment->getClientEmail(),
        ];

        $params = array_merge($params, $payment->getDetails());
        $details = ArrayObject::ensureArrayObject($params);

        $request->setResult((array) $details);
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' == $request->getTo()
        ;
    }
}
