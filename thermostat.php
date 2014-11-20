#!/usr/bin/php
<?php

use GuzzleHttp\Client;



require 'vendor/autoload.php';





class HoneywellWifiAPI
{
	const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36';
	const USERNAME = '***';
	const PASSWORD = '***';
	const DEVICE_ID = 000;
	const PROTOCOL = 'https';
	const URL = 'mytotalconnectcomfort.com';

	public function __construct()
	{
		date_default_timezone_set('America/Chicago');
	}

	public function parseClientCookies($cookies, $container)
	{
		$ignore = array('path', 'Path', 'HttpOnly');
		$return = [];
		foreach( $cookies as $cookie )
		{
			$data = explode(';', $cookie);
			foreach ($data as $inside)
			{
				$split = explode('=', trim($inside));
				if (!in_array($split[0], $ignore))
				{
					if ($split[0] == 'expires')
					{
						$return['expires'] = explode(',', $split[1])[0];
					}
					else
					{
						$return[$split[0]] = $split[1];
					}
				}
			}
		}

		return $return;
	}

	public function fillCookieJar($cookieJar)
	{
		$string = '';
		foreach ($cookieJar as $cookieKey => $cookieVal)
		{
			$string .= $cookieKey . '=' . $cookieVal . ';';
		}
		return $string;
	}

	public function getLogin()
	{
		$client = new Client();

		$query = [
			'timeOffset' => '360',
			'UserName' => self::USERNAME,
			'Password' => self::PASSWORD,
			'RememberMe' => 'false',
		];

		$headers2 = [
			'Content-Type' 		=> 'application/x-www-form-urlencoded',
			'Accept' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding' 	=> 'sdch',
			'Host' 				=> self::URL,
			'DNT' 				=> '1',
			'Origin' 			=> self::PROTOCOL . '://' . self::URL . '/portal/',
			'User-Agent' 		=> self::USER_AGENT,
		];

		$response2 = $client->post(self::PROTOCOL . '://' . self::URL . '/portal/', [
			'headers' => $headers2,
			'body' => $query,
			'cookies' => true,
		]);
		
		$headers3 = [
			'Accept' => '*/*',
			'DNT' => '1',
			'Accept-Encoding' => 'plain',
			'Cache-Control' => 'max-age=0',
			'Accept-Language' => 'en-US,en,q=0.8',
			'Connection' => 'keep-alive',
			'Host' => self::URL,
			'Referer' => self::PROTOCOL . '://' . self::URL . '/portal/',
			'X-Requested-With' => 'XMLHttpRequest',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36',
		];

		$response3 = $client->get(self::PROTOCOL . '://' . self::URL . '/portal/Device/CheckDataSession/' . self::DEVICE_ID . '?_=' . time(), [
			'headers' => $headers3,
			'cookies' => true,
		]);

		$data = $response3->json();

		$headers4 = [
			'Accept' => 'application/json, text/javascript, */*; q=0.01',
			'DNT' => '1',
			'Accept-Encoding' => 'gzip,deflate',
			'Content-Type' => 'application/json; charset=UTF-8',
			'Cache-Control' => 'max-age=0',
			'Accept-Language' => 'en-US,en;q=0.8',
			'Connection' => 'keep-alive',
			'Origin' => self::PROTOCOL . '//' . self::URL,
			'Host' => self::URL,
			'Referer' => self::PROTOCOL . '://' . self::URL . '/portal/Device/Control/' . self::DEVICE_ID,
			'X-Requested-With' => 'XMLHttpRequest',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36',
		];

		$body = [
			'CoolNextPeriod' => null,
			'CoolSetpoint' => null,
			'DeviceID' => self::DEVICE_ID,
			'FanMode' => null,
			'HeatNextPeriod' => null,
			'HeatSetpoint' => 72,
			'StatusCool' => null, // 1 for hold, 0 for regular
			'StatusHeat' => null, // 1 for hold, 0 for regular
			'SystemSwitch' => null, // 2 is off, 1 is heat, 3 for AC
		];

		$response4 = $client->post(self::PROTOCOL . '://' . self::URL . '/portal/Device/SubmitControlScreenChanges', [
			'headers' => $headers4,
			'cookies' => true,
			'json' => $body,
			'debug' => true,
		]);

		echo $response3->getBody();
		die();
	}
}

$honeywell = new HoneywellWifiApi();
$honeywell->getLogin();

?>
