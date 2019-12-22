<?php
/**
 * Created by PhpStorm.
 * User: iMohammad
 * Date: 6/20/17
 * Time: 8:40 PM
 */

namespace Mont4\PaymentGateway\Gateways;

use Mont4\PaymentGateway\PaymentGateway;

class IrSep implements GatewayInterface
{
    const VERIFY_STATUS = [
        -1 => "ارسال api الزامی می باشد,",
        -2 => "ارسال transId الزامی می باشد,",
        -3 => "درگاه پرداختی با api ارسالی یافت نشد و یا غیر فعال می باشد,",
        -4 => "فروشنده غیر فعال می باشد,",
        -5 => "تراکنش با خطا مواجه شده است,",
    ];

    private $apiKey;
    private $requestUrl;
    private $gatewayUrl;
    private $verifyUrl;
    private $redirect;

    public function __construct()
    {
        $this->apiKey     = config('payment_gateway.gateways.ir_sep.api_key');
        $this->requestUrl = config('payment_gateway.gateways.ir_sep.request_url');
        $this->gatewayUrl = config('payment_gateway.gateways.ir_sep.gateway_url');
        $this->verifyUrl  = config('payment_gateway.gateways.ir_sep.verify_url');
        $this->redirect   = config('payment_gateway.gateways.ir_sep.redirect');
    }

    class ir_sep implements GatewayInterface
{
    private $apiKey;
    private $sendUrl;
    private $gatewayUrl;
    private $verifyUrl;
    private $password;

    function __construct()
    {
        $this->apiKey     = env('SEP_IR_api_key');
        $this->sendUrl    = env('SEP_IR_send_url');
        $this->gatewayUrl = env('SEP_IR_gateway_url');
        $this->verifyUrl  = env('SEP_IR_verify_url');
        $this->password   = env('SEP_IR_password');
        $this->redirect   = env('SEP_IR_redirect');
    }

    public function setApiToken($token)
    {
        $this->apiKey = $token;

        return $this;
    }

    public function request(int $amount, string $mobile = NULL, string $factorNumber = NULL, string $description = NULL)
    {
        if ($amount < 1000)
            throw new \Exception('amount is lower than 1000');

        $token = "sep_" . Str::random();

        return [
            'method'         => 'post',
            'amount'         => $amount,
            'mid'            => $this->apiKey,
            'gateway_url'    => $this->gatewayUrl,
            'transaction_id' => $token,
            'redirect_url'   => $this->redirect,
        ];
    }

    public function verify($transactionId)
    {
        $data = [
            "merchantID" => $this->apiKey,
            "RefNum"     => $transactionId,
            "password"   => $this->password,
        ];

        $soapClient = new \SoapClient("https://sep.shaparak.ir/payments/referencepayment.asmx?wsdl");
        $value      = $soapClient->__call("verifyTransaction", $data);

        if ($value < 0) {
            return false;
        }

        return true;
    }
}
