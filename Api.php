<?php

namespace KnitPay\PayuIndia;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\Http\HttpException;
use LogicException;

class Api
{
    const LIVE_URL = 'https://secure.payu.in/_payment';

    const TEST_URL = 'https://test.payu.in/_payment';

    const API_LIVE_URL = 'https://info.payu.in';

    const API_TEST_URL = 'https://test.payu.in';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    private $merchant_key;

    private $merchant_salt;

    /**
     * @throws InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;

        $this->merchant_key  = $this->options['merchant_key'];
        $this->merchant_salt = $this->options['merchant_salt'];
    }

    /**
     * @return array
     */
    protected function doRequest($method, $uri, array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint() . $uri, $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = json_decode($response->getBody()->getContents());
        if (null === $result) {
            throw new LogicException("Response content is not valid json: \n\n{$response->getBody()->getContents()}");
        }

        return $result;
    }

    /**
     * @return array
     */
    public function addGlobalParams(array $params)
    {
        $merchant_key  = $this->options['merchant_key'];
        $merchant_salt = $this->options['merchant_salt'];

        $txnid  = $params['txnid'];
        $amount  = $params['amount'];
        $product_info  = $params['productinfo'];
        $first_name = $params['firstname'];
        $email = $params['email'];

        $udf1 = PHP_VERSION;
        $udf2 = '0.0.1'; // TODO: Fetch from variable
        $udf3 = 'payum';
        $udf4 = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:'';
        $udf5 = 'Knit Pay';

        $str = "{$merchant_key}|{$txnid}|{$amount}|{$product_info}|{$first_name}|{$email}|{$udf1}|{$udf2}|{$udf3}|{$udf4}|{$udf5}||||||{$merchant_salt}";

        $params = array_merge($params,[
            'hash' => strtolower( hash( 'sha512', $str ) ),
            'key'  => $merchant_key,
            'udf1' => $udf1,
            'udf2' => $udf2,
            'udf3' => $udf3,
            'udf4' => $udf4,
            'udf5' => $udf5,
        ]);
        return $params;
    }

    public function verify_payment( $transaction_id ) {
        $command = 'verify_payment';

        $hash_str = "{$this->merchant_key}|{$command}|{$transaction_id}|{$this->merchant_salt}";
        $hash     = strtolower( hash( 'sha512', $hash_str ) ); // generate hash for verify payment request

        $data = [
            'key'     => $this->merchant_key,
            'command' => $command,
            'var1'    => $transaction_id,
            'hash'    => $hash,
        ];

        $result = $this->doRequest('POST', '/merchant/postservice?form=2', $data);

        if ( isset( $result->status ) && 1 === $result->status ) {
            return $result->transaction_details->$transaction_id;
        }

        if ( isset( $result->msg ) ) {
            throw new LogicException( trim( $result->msg ) );
        }
        throw new LogicException( 'Something went wrong. Please try again later.' );
    }

    public function cancel_refund_transaction( $transaction_id, $token_id, $amount ) {
        $command = 'cancel_refund_transaction';

        $hash_str = "{$this->merchant_key}|{$command}|{$transaction_id}|{$this->merchant_salt}";
        $hash     = strtolower( hash( 'sha512', $hash_str ) ); // generate hash for verify payment request

        $data = [
            'key'     => $this->merchant_key,
            'command' => $command,
            'var1'    => $transaction_id,
            'var2'    => $token_id,
            'var3'    => $amount,
            'hash'    => $hash,
        ];

        $result = $this->doRequest('POST', '/merchant/postservice?form=2', $data);

        if ( isset( $result->status ) && 1 === $result->status ) {
            return $result->request_id;
        }

        if ( isset( $result->msg ) ) {
            throw new LogicException( trim( $result->msg ) );
        }
        throw new LogicException( 'Something went wrong. Please try again later.' );
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? self::API_TEST_URL : self::API_LIVE_URL;
    }

    /**
     * @return string
     */
    public function getOffsiteUrl()
    {
        return $this->options['sandbox'] ? self::TEST_URL : self::LIVE_URL;
    }
}
