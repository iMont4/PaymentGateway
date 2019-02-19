<?php

return [
	'gateways' => [
		'ir_pay' => [
			'api_key'     => env('payment_gateway__ir_pay__api_key', 'test'),
			'request_url' => env('payment_gateway__ir_pay__request_url', 'https://pay.ir/pg/send'),
			'gateway_url' => env('payment_gateway__ir_pay__gateway_url', 'https://pay.ir/pg'),
			'verify_url'  => env('payment_gateway__ir_pay__verify_url', 'https://pay.ir/pg/verify'),
			'redirect'    => env('APP_URL') . '/payment/verify',
		],
	],
];