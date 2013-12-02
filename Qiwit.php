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

	//token
	private $auth_token;

	//constructor
	public function __construct() {
		$this->auth_token = $this->get_token();
	}

	//authorization get request
	private function get_request($query, $params = array()) {
		$header = array(
			'GET '.$this->url.'/'.$query.' HTTP/1.1',
			'Host: googleapis.com',
			'Authorization: Basic '.$this->auth_token,
		);

		if (!empty($params))
			$params = '?'.http_build_query($params);
		else
			$params = '';

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->url.'/'.$query . $params,
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
				'POST '.$this->url.'/'.$query.' HTTP/1.1',
				'Host: googleapis.com',
				'Authorization: Basic '.$this->auth_token,
			);

		if (!empty($params))
			$fields = http_build_query($params);

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->url.'/'.$query,
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
				$request_type.' '.$this->url.'/'.$query.' HTTP/1.1',
				'Host: googleapis.com',
				'Authorization: Basic '.$this->auth_token,
			);

		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$params[$key] = urlencode($value);
			}
		}

		$params = http_build_query($params);

		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->url.'/'.$query . $params,
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
}