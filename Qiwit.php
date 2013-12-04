<?php

/**
* Qiwit class
* 
* @author zhzhussupovkz@gmail.com
* 
* The MIT License (MIT)
*
* Copyright (c) 2013 Zhussupov Zhassulan zhzhussupovkz@gmail.com
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of
* this software and associated documentation files (the "Software"), to deal in
* the Software without restriction, including without limitation the rights to
* use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
* the Software, and to permit persons to whom the Software is furnished to do so,
* subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
* FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
* COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
* IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
* CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class Qiwit {

	//api url
	private $api_url = 'https://w.qiwi.com/api/v2/';

	//api key
	private $api_key;

	//provider id
	private $prv_id;

	//token
	private $auth_token;

	//constructor
	public function __construct($api_key = null, $prv_id = null) {
		if (!$api_key)
			throw new Exception("API key is required");
		if (!$prv_id)
			throw new Exception("Provider id is required");
		$this->api_key = $api_key;
		$this->prv_id = $prv_id;
		$this->auth_token = base64_encode($this->prv_id.':'.$this->api_key);
	}

	//authorization get request
	private function get_request($query, $params = array()) {
		$header = array(
			'GET '.$this->api_url.'/'.$query.' HTTP/1.1',
			'Host: w.qiwi.com',
			'Accept: text/json',
			'Authorization: Basic '.$this->auth_token,
			'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
		);

		if (!empty($params))
			$params = '?'.http_build_query($params);
		else
			$params = '';

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->api_url.'/'.$query . $params,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
			);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		if ($response == false)
			throw new Exception('Error: '.curl_error($ch));
		curl_close($ch);
		$result = json_decode($response);
		if (!$result)
			throw new Exception('Server response invalid data type');
		return $result;
	}

	//authorization post request
	private function post_request($query, $params = array()) {
		$header = array(
				'POST '.$this->api_url.'/'.$query.' HTTP/1.1',
				'Host: w.qiwi.com',
				'Accept: text/json',
				'Authorization: Basic '.$this->auth_token,
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
			);

		if (!empty($params))
			$fields = http_build_query($params);

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->api_url.'/'.$query,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POSTFIELDS => $fields,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
			);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		if ($response == false)
			throw new Exception('Error: '.curl_error($ch));
		curl_close($ch);
		$result = json_decode($response);
		if (!$result)
			throw new Exception('Server response invalid data type');
		return $result;
	}

	//authorization custom request
	private function custom_request($request_type, $query, $params = array()) {
		$header = array(
				$request_type.' '.$this->api_url.'/'.$query.' HTTP/1.1',
				'Host: w.qiwi.com',
				'Accept: text/json',
				'Authorization: Basic '.$this->auth_token,
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
			);

		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$params[$key] = urlencode($value);
			}
		}

		$params = http_build_query($params);

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->api_url.'/'.$query . $params,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_CUSTOMREQUEST => $request_type,
			);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		if ($response == false)
			throw new Exception('Error: '.curl_error($ch));
		curl_close($ch);
		$result = json_decode($response);
		return $result;
	}

	/*
	bill_create - Выставление счета пользователю
	*/
	public function bill_create($bill_id = null, $params = array()) {
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		if (!preg_match('/^tel:\+\d{1,15}$/', $params['user']))
			throw new Exception("Неверный формат кошелька пользователя");
		if (!preg_match('/^\d+(.\d{0,3})?$/', $params['amount']))
			throw new Exception("Неверный формат счета");
		if (!preg_match('/^[a-zA-Z]{3}$/', $params['ccy']))
			throw new Exception("Неверный формат идентификатора валюты");
		if (!preg_match('/^\.{0,255}$/', $params['comment']))
			throw new Exception("Комментарий должен быть текстом");
		if (!preg_match('^\d{4}-\d{2}0\d{4}T\d{2}:\d{2}:\d{2}$', $params['lifetime']))
			throw new Exception("Неверный формат даты");
		if (!preg_match('/^\.{1,100}$/', $params['prv_name']))
			throw new Exception("Длина названия провайдера должна быть не более 100 символов");
		return $this->custom_request('PUT', 'prv/'.$this->prv_id.'/bills/'.$bill_id, $params);
	}

	/*
	bill_status - Запрос статуса счета
	*/
	public function bill_status($bill_id = null, $params = array()) {
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		return $this->get_request('prv/'.$this->prv_id.'/bills/'.$bill_id, $params);
	}

	/*
	bill_redirect - Переадресация для оплаты счета
	*/
	public function bill_redirect($bill_id = null, $success_url = null, $fail_url = null, $iframe = true) {
		if ((!$success_url) || (!$fail_url))
			throw new Exception("Параметры не заданы!");
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		$first = array('shop' => $this->prv_id, 'transaction' => $bill_id);
		$second = array('successUrl' => $success_url, 'failUrl' => $fail_url);
		$params = array_merge($first, $second);
		$url = 'https://w.qiwi.com/order/external/main.action';

		if ($iframe == true)
			$params.= '&iframe=true';

		$location = $url.'?'.http_build_query($params);
		header("Location: ".$location);
	}

	/*
	bill_rollback - Отмена неоплаченного выставленного счета
	*/
	public function bill_rollback($bill_id = null, $status = null) {
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		if (!preg_match('/^rejected$/', $status))
			throw new Exception("Неверный формат статуса");
		$params = array('status' => $status);
		return $this->custom_request('PATCH', 'prv/'.$this->prv_id.'/bills/'.$bill_id, $params);
	}

	/*
	bill_refund - Возврат средств по оплаченному счету
	*/
	public function bill_refund($bill_id = null, $refund_id = null, $amount = null) {
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		if (!$refund_id)
			throw new Exception("Не задан ID отмены счета");
		if (!$amount)
			throw new Exception("Не задана сумма возврата");
		if(!preg_match('^\d+(\.\d{0,3})?$', $amount))
			throw new Exception("Неверный формат суммы возврата");
		$params = array('amount' => $amount);
		return $this->custom_request('PUT', 'prv/'.$this->prv_id.'/bills/'.$bill_id.'/refund/'.$refund_id, $params);
	}

	/*
	bill_check_status - Проверка статуса возврата
	*/
	public function bill_check_status($bill_id = null, $refund_id = null) {
		if (!$bill_id)
			throw new Exception("Не задан ID счета");
		if (!$refund_id)
			throw new Exception("Не задан ID отмены счета");
		return $this->get_request('prv/'.$this->prv_id.'/bills/'.$bill_id.'/refund/'.$refund_id, array());
	}

}
