<?php namespace \cjmaio;

use GuzzleHttp\Client;

class HoneywellWifiAPI
{
	const SYSTEM_HEAT_ONLY = 1;
	const SYSTEM_COOL_ONLY = 3;
	const SYSTEM_OFF = 2;

	const USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36';
	const USERNAME = '';
	const PASSWORD = '';
	const DEVICE_ID = 0;
	const PROTOCOL = 'https';
	const URL = 'mytotalconnectcomfort.com';

	private $client = null;

	/**
	 * Constructor
	 * Initializes a Guzzle client that we will use
	 * throughout the life of this object
	 */
	public function __construct()
	{
		date_default_timezone_set('America/Chicago');

		$this->client = new Client();
	}

	/**
	 * Login to the Honeywell Total Connect Comfort
	 * website. Guzzle will automatically save the cookie
	 * generated and we can use that cookie to send other commands.
	 *
	 * @throws Exception
	 * @return Boolean
	 */
	public function login()
	{
		$request = $this->client->post(self::PROTOCOL . '://' . self::URL . '/portal/', [
			'headers' => [
				'Content-Type' 		=> 'application/x-www-form-urlencoded',
				'Accept' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
				'Accept-Encoding' 	=> 'sdch',
				'Host' 				=> self::URL,
				'DNT' 				=> '1',
				'Origin' 			=> self::PROTOCOL . '://' . self::URL . '/portal/',
				'User-Agent' 		=> self::USER_AGENT,
			],
			'body' => [
				'timeOffset' => '360',
				'UserName' => self::USERNAME,
				'Password' => self::PASSWORD,
				'RememberMe' => 'false',
			],
			'cookies' => true,
		]);

		// TODO: Rethink how this works perhaps? Some cases you get a 302 when you login, other cases
		// you get a 200. So I check for something outside of that, or if the URL is still the login page.
		if ($request->getStatusCode() != 200 && $request != 302) throw new Exception('Returned an error code that was not 200 or 302. Error with login.');
		if ($request->getEffectiveUrl() == 'https://mytotalconnectcomfort.com/portal/') throw new Exception('Login failed. Are your credentials correct?');

		return true;
	}

	/**
	 * Check the current status of your device.
	 * This assumes that you have already successfully logged
	 * in using the login() function.
	 *
	 * @throws Exception
	 * @return JSON Array
	 */
	public function checkStatus()
	{
		$request = $this->client->get(self::PROTOCOL . '://' . self::URL . '/portal/Device/CheckDataSession/' . self::DEVICE_ID . '?_=' . time(), [
			'headers' => [
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
			],
			'cookies' => true,
		]);

		// TODO: The only responses I have gotten are either 401 (not authorized) or 200
		// (authorized, command went through, etc). Feel free to expand.
		if ($request->getStatusCode() === 401) throw new Exception('Not authorized to perform this action');
		if ($request->getStatusCode() !== 200) throw new Exception('API returned status code ' . $request->getStatusCode());

		return $request->json();
	}

	/**
	 * Set the current state of the thermostat
	 *
	 * @param $mode integer
	 * @return Boolean
	 */
	public function setThermostat($mode)
	{
		$body = ['SystemSwitch' => $mode];

		try {
			$this->postToThermostat($body);
		} catch(Exception $e) {
			return false;
		}

		// TODO: Need error checking
		return true;
	}

	/**
	 * Set the current Heat temperature for the thermostat
	 *
	 * @param $temperature float
	 * @return Boolean
	 */
	public function setHeatTemperature($temperature)
	{
		$body = ['HeatSetpoint' => $temperature];

		try {
			$this->postToThermostat($body);
		} catch(Exception $e) {
			return false;
		}
		
		// TODO: Need error checking
		return true;
	}

	/**
	 * Provides a private helper function to construct
	 * the HTTP POST request to change parameters of the
	 * thermostat.
	 *
	 * @param $body array of values to set
	 * @return JSON Array
	 * @throws Exception
	 */
	private function postToThermostat($body)
	{
		// 	'CoolNextPeriod' => null,
		// 	'CoolSetpoint' => null,
		// 	'DeviceID' => self::DEVICE_ID,
		// 	'FanMode' => null,
		// 	'HeatNextPeriod' => null,
		// 	'HeatSetpoint' => 72,
		// 	'StatusCool' => null, // 1 for hold, 0 for regular
		// 	'StatusHeat' => null, // 1 for hold, 0 for regular
		// 	'SystemSwitch' => null, // 2 is off, 1 is heat, 3 for AC

		$request = $this->client->post(self::PROTOCOL . '://' . self::URL . '/portal/Device/SubmitControlScreenChanges', [
			'headers' => [
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
			],
			'cookies' => true,
			'json' => array_merge(['DeviceID' => self::DEVICE_ID], $body),
		]);

		if ($request->getStatusCode() === 401) throw new Exception('Not authorized to perform this action');
		if ($request->getStatusCode() !== 200) throw new Exception('API returned status code ' . $request->getStatusCode());

		return $request->json();
	}
}