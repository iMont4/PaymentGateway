<?php

namespace Mont4\PaymentGateway\Gateways;

use Illuminate\Support\Str;
use SoapClient;

class IrSep implements GatewayInterface
{
    const VERIFY_STATUS = [
        -111 => "ساختار صحیح نمی‌باشد.",
        -18  => "IP شما برای این ترمینال ثبت نشده است.",
        -6   => "زمان تایید درخواست به پایان رسیده است.",
        -1   => "کدفروشنده یا RefNum صحیح نمی‌باشد.",
        -20  => "مبلغ دریافتی از بانک با سند تراکنش تطابق ندارد. پول به حساب شما برمی‌گردد.",
    ];

    private $apiKey;
    private $gatewayUrl;
    private $verifyUrl;
    private $redirect;
    private $password;

    public function __construct()
    {
        $this->apiKey     = config('payment_gateway.gateways.ir_sep.api_key');
        $this->password   = config('payment_gateway.gateways.ir_sep.password');
        $this->gatewayUrl = config('payment_gateway.gateways.ir_sep.gateway_url');
        $this->verifyUrl  = config('payment_gateway.gateways.ir_sep.verify_url');
        $this->redirect   = config('payment_gateway.gateways.ir_sep.redirect');
    }

    public function request(int $amount, string $mobile = NULL, string $factorNumber = NULL, string $description = NULL)
    {
        if ($amount < 1000)
            throw new \Exception('amount is lower than 1000');

        if (!$factorNumber)
            $factorNumber = "sep_" . Str::random(40);

        return [
            'success'     => true,
            'method'      => 'post',
            'gateway_url' => $this->gatewayUrl,
            'token'       => $factorNumber,
            'data'        => [
                'amount'         => $amount,
                'mobile'         => $mobile,
                'mid'            => $this->apiKey,
                'transaction_id' => $factorNumber,
                'redirect_url'   => $this->redirect,
            ],
        ];
    }

    public function verify($RefNum, $amount)
    {
        try {
            $soapClient = new SoapClient($this->verifyUrl);
            $response   = $soapClient->verifyTransaction($RefNum, $this->apiKey);

            if ($response < 0) {
                return [
                    'success' => false,
                    'message' => self::VERIFY_STATUS[$response] ?? NULL,
                ];
            }

            if ($response != $amount) {
                // Reverse Money
                $this->reverse($RefNum);

                return [
                    'success' => false,
                    'message' => self::VERIFY_STATUS[-20] ?? NULL,
                ];
            }

            return [
                'success' => true,
            ];
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return [
            'success' => false,
        ];
    }

    public function reverse($RefNum)
    {
        try {
            $soapClient = new SoapClient($this->verifyUrl);
            $response   = $soapClient->reverseTransaction($RefNum, $this->apiKey, $this->apiKey, $this->password);

            if ($response < 0) {
                return [
                    'success' => false,
                    'message' => self::VERIFY_STATUS[$response] ?? NULL,
                ];
            }

            return [
                'success' => true,
            ];
        } catch (\Exception $ex) {
            \Log::error($ex);
        }

        return [
            'success' => false,
        ];
    }
}
