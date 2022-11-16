<?php

namespace KnitPay\PayuIndia\Action;

use KnitPay\PayuIndia\Api;
use League\Uri\Http as HttpUri;
use League\Uri\UriModifier;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use ArrayAccess;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->query['return'])) {
            return;
        }

        $post_param = $model->toUnsafeArray();
        $post_param = $this->api->addGlobalParams($post_param);

        $return_url = $request->getToken()->getAfterUrl();
        $return_url = HttpUri::createFromString($return_url);
        $return_url = UriModifier::mergeQuery($return_url, 'return=1');
        $post_param['surl'] = $return_url;
        $post_param['furl'] = $return_url;

        throw new HttpPostRedirect($this->api->getOffsiteUrl(), $post_param);
    }

    public function supports($request)
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
