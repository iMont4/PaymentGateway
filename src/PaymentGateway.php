<?php

namespace Mont4\PaymentGateway;

use Mont4\PaymentGateway\Gateways\IrPay;

/**
 * Class PaymentGateway
 *
 * @package Mont4\PaymentGateway
 *
 * @method request(int $amount, string $mobile = NULL, string $factorNumber = NULL, string $description = NULL)
 * @method verify($token)
 */
class PaymentGateway
{
	const IR_PAY = 'ir_pay';

	const GATEWAYS = [
		self::IR_PAY,
	];

	const GATEWAY_CLASSES = [
		self::IR_PAY => IrPay::class,
	];

	private $gateway = NULL;
	private $sender;

	/**
	 * SmsService constructor.
	 */
	private function __construct($gateway)
	{
		$this->gateway = $gateway;
	}

	static public function gateway($gateway)
	{
		return new self($gateway);
	}

	public function __call($name, $arguments)
	{
		if (!in_array($this->gateway, self::GATEWAYS)) {
			throw new \Exception('Gateway is not exists.');
		}

		// class from gateway name
		$gateway = new \ReflectionClass(self::GATEWAY_CLASSES[$this->gateway]);

		// construct class of gateway
		$gateway = $gateway->newInstanceArgs();

		// check called method exist
		if (!method_exists($gateway, $name)) {
			throw new \Exception('Method is not exists.');
		}

		// call method of gateway
		return $gateway->{$name}(...$arguments);
	}
}