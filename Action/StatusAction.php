<?php

namespace KnitPay\PayuIndia\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use KnitPay\PayuIndia\Api;
use ArrayAccess;
use LogicException;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($model['txnid'])){
            $transaction = $this->api->verify_payment($model['txnid']);
            if ( $transaction->txnid !== $model['txnid'] ) {
                throw new LogicException('Something went wrong: ' . print_r( $transaction, true ));
            }

            $model->replace((array)$transaction);
        }

        switch ( $model['status'] ) {
            case 'success':
                $request->markCaptured();

                break;

            case 'failure':
                $request->markFailed();

                break;

            case 'pending':
                $request->markPending();
                break;

            default:
                $request->markNew();
                break;
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
