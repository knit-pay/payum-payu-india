


# Payum PayU India and PayUBiz Extension

The Payum extension. It provides [PayU India](http://go.thearrangers.xyz/payu?utm_source=knit-pay&utm_medium=ecommerce-module&utm_campaign=github&utm_content=help-signup) and PayUBiz payment integration.

Before proceeding, kindly create an account at **PayU** if you don't have one already.
<br>
[Sign Up on PayU Live](http://go.thearrangers.xyz/payu?utm_source=knit-pay&utm_medium=ecommerce-module&utm_campaign=github&utm_content=help-signup)

For Testing, kindly create an account at **PayU UAT Dashboard** if you don't have one already.
<br>
[Sign Up on PayU Test/UAT](https://test.payumoney.com/url/QIJLMsgaurL3)

## Support
Feel free to contact us for any kind of support required.
https://www.knitpay.org/contact-us/

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require knit-pay/payum-payu-india php-http/guzzle6-adapter
```
## Register the `payu_india` Payum factory using `PayumBuilder` (config.php):

```php
use Payum\Core\GatewayFactoryInterface;
use KnitPay\PayuIndia\PayuIndiaGatewayFactory;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('payu_india', function(array $config, GatewayFactoryInterface $gatewayFactory) {
        return new PayuIndiaGatewayFactory($config, $gatewayFactory);
    })
        
    ->addGateway('payu_india', [
        'factory' => 'payu_india',
        'merchant_key' => 'Key', // Change this.
        'merchant_salt' => 'Salt', // Change this.
        'sandbox' => true,
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `payu_india`. The rest remain almost the same as described in basic [get it started](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md) documentation.
Optional fields can be added as shown in the code below.

```php
$gatewayName = 'payu_india';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($paymentClass);

$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('INR');
$payment->setTotalAmount(123); // 1.23 INR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

$payment->setDetails(array(
  // put here any fields in a gateway format.
  // for example if you use PayU India you can define optional fields like this.
  // Kindly refer to this link for more details. https://devguide.payu.in/docs/payu-hosted-checkout/payu-hosted-checkout-integration/https://devguide.payu.in/docs/payu-hosted-checkout/payu-hosted-checkout-integration/
  // Uncomment the optional field below that you want to pass to the payment gateway.
    //'firstname'          => 'First Name',
    //'lastname'           => 'Last Name',
    //'address1'           => 'Address Line 1',
    //'address2'           => 'Address Line 2',
    //'city'               => 'City',
    //'state'              => 'State',
    //'country'            => 'Country',
    //'zipcode'            => 'Zip Code',
    //'phone'              => 'Phone Number',
    //'pg'                 => 'CC',
    //'enforce_paymethod'  => 'creditcard',
    //'display_lang'       => 'Hindi'
));
```
## capture.php
capture.php remains almost the same as described in basic [get it started](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md) documentation.
Although there is a minor modification. We need need to handle the HttpPostRedirect response also.

```php
/** @var \Payum\Core\GatewayInterface $gateway */
if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    } elseif ($reply instanceof HttpPostRedirect) {
        echo $reply->getContent();
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}
```
