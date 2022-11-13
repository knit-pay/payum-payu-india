<?php

namespace KnitPay\PayuIndia;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use KnitPay\PayuIndia\Action\AuthorizeAction;
use KnitPay\PayuIndia\Action\CancelAction;
use KnitPay\PayuIndia\Action\CaptureAction;
use KnitPay\PayuIndia\Action\ConvertPaymentAction;
use KnitPay\PayuIndia\Action\NotifyAction;
use KnitPay\PayuIndia\Action\RefundAction;
use KnitPay\PayuIndia\Action\StatusAction;

class PayuIndiaGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'payu_india',
            'payum.factory_title' => 'PayU India',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'merchant_key' => '',
                'merchant_salt' => '',
                'sandbox' => true,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchant_key', 'merchant_salt'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
