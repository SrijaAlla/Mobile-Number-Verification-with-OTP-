<?php

/**
 * Textlocal API2 Wrapper Class
 *
 * This class is used to interface with the Textlocal API2 to send messages, manage contacts, retrieve messages from
 * inboxes, track message delivery statuses, access history reports
 *
 * @package    Textlocal
 * @subpackage API
 * @author     Andy Dixon <andy.dixon@tetxlocal.com>
 * @version    1.4-IN
 * @const      REQUEST_URL       URL to make the request to
 * @const      REQUEST_TIMEOUT   Timeout in seconds for the HTTP request
 * @const      REQUEST_HANDLER   Handler to use when making the HTTP request (for future use)
 */
class Textlocal
{
	const REQUEST_URL = 'https://api.textlocal.in/';
	const REQUEST_TIMEOUT = 60;
	const REQUEST_HANDLER = 'curl';

	private $username;
	private $hash;
	private $apiKey;

	private $errorReporting = false;

	public $errors = array();
	public $warnings = array();

	public $lastRequest = array();

	/**
	 * Instantiate the object
	 * @param $username
	 * @param $hash
	 */
	function __construct($username, $hash, $apiKey = false)
	{
		$this->username = $username;
		$this->hash = $hash;
		if ($apiKey) {
			$this->apiKey = $apiKey;
		}

	}

	/**
	 * Private function to construct and send the request and handle the response
	 * @param       $command
	 * @param array $params
	 * @return array|mixed
	 * @throws Exception
	 * @todo Add additional request handlers - eg fopen, file_get_contacts
	 */
	private function _sendRequest($command, $params = array())
	{
		if ($this->apiKey && !empty($this->apiKey)) {
			$params['apiKey'] = $this->apiKey;

		} else {
			$params['hash'] = $this->hash;
		}
		// Create request string
		$params['username'] = $this->username;

		$this->lastRequest = $params;

		if (self::REQUEST_HANDLER == 'curl')
			$rawResponse = $this->_sendRequestCurl($command, $params);
		else throw new Exception('Invalid request handler.');

		$result = json_decode($rawResponse);
		if (isset($result->errors)) {
			if (count($result->errors) > 0) {
				foreach ($result->errors as $error) {
					switch ($error->code) {
						default:
							throw new Exception($error->message);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Curl request handler
	 * @param $command
	 * @param $params
	 * @return mixed
	 * @throws Exception
	 */
	private function _sendRequestCurl($command, $params)
	{

		$url = self::REQUEST_URL . $command . '/';

		// Initialize handle
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $params,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT        => self::REQUEST_TIMEOUT
		));

		$rawResponse = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		if ($rawResponse === false) {
			throw new Exception('Failed to connect to the Textlocal service: ' . $error);
		} elseif ($httpCode != 200) {
			throw new Exception('Bad response from the Textlocal service: HTTP code ' . $httpCode);
		}

		return $rawResponse;
	}

	/**
	 * fopen() request handler
	 * @param $command
	 * @param $params
	 * @throws Exception
	 */
	private function _sendRequestFopen($command, $params)
	{
		throw new Exception('Unsupported transfer method');
	}

	/**
	 * Get last request's parameters
	 * @return array
	 */
	public function getLastRequest()
	{
		return $this->lastRequest;
	}

	/**
	 * Send an SMS to one or more comma separated numbers
	 * @param       $numbers
	 * @param       $message
	 * @param       $sender
	 * @param null  $sched
	 * @param false $test
	 * @param null  $receiptURL
	 * @param numm  $custom
	 * @param false $optouts
	 * @param false $simpleReplyService
	 * @return array|mixed
	 * @throws Exception
	 */

	public function sendSms($numbers, $message, $sender, $sched = null, $test = false, $receiptURL = null, $custom = null, $optouts = false, $simpleReplyService = false)
	{

		if (!is_array($numbers))
			throw new Exception('Invalid $numbers format. Must be an array');
		if (empty($message))
			throw new Exception('Empty message');
		if (empty($sender))
			throw new Exception('Empty sender name');
		if (!is_null($sched) && !is_numeric($sched))
			throw new Exception('Invalid date format. Use numeric epoch format');

		$params = array(
			'message'       => rawurlencode($message),
			'numbers'       => implode(',', $numbers),
			'sender'        => rawurlencode($sender),
			'schedule_time' => $sched,
			'test'          => $test,
			'receipt_url'   => $receiptURL,
			'custom'        => $custom,
			'optouts'       => $optouts,
			'simple_reply'  => $simpleReplyService
		);

		return $this->_sendRequest('send', $params);
	}


	/**
	 * Send an SMS to a Group of contacts - group IDs can be retrieved from getGroups()
	 * @param       $groupId
	 * @param       $message
	 * @param null  $sender
	 * @param false $test
	 * @param null  $receiptURL
	 * @param numm  $custom
	 * @param false $optouts
	 * @param false $simpleReplyService
	 * @return array|mixed
	 * @throws Exception
	 */
	public function sendSmsGroup($groupId, $message, $sender = null, $sched = null, $test = false, $receiptURL = null, $custom = null, $optouts = false, $simpleReplyService = false)
	{

		if (!is_numeric($groupId))
			throw new Exception('Invalid $groupId format. Must be a numeric group ID');
		if (empty($message))
			throw new Exception('Empty message');
		if (empty($sender))
			throw new Exception('Empty sender name');
		if (!is_null($sched) && !is_numeric($sched))
			throw new Exception('Invalid date format. Use numeric epoch format');

		$params = array(
			'message'       => rawurlencode($message),
			'group_id'      => $groupId,
			'sender'        => rawurlencode($sender),
			'schedule_time' => $sched,
			'test'          => $test,
			'receipt_url'   => $receiptURL,
			'custom'        => $custom,
			'optouts'       => $optouts,
			'simple_reply'  => $simpleReplyService
		);

		return $this->_sendRequest('send', $params);
	}


}

;

class Contact
{
	var $number;
	var $first_name;
	var $last_name;
	var $custom1;
	var $custom2;
	var $custom3;

	var $groupID;

	/**
	 * Structure of a contact object
	 * @param        $number
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $custom1
	 * @param string $custom2
	 * @param string $custom3
	 */
	function __construct($number, $firstname = '', $lastname = '', $custom1 = '', $custom2 = '', $custom3 = '')
	{
		$this->number = $number;
		$this->first_name = $firstname;
		$this->last_name = $lastname;
		$this->custom1 = $custom1;
		$this->custom2 = $custom2;
		$this->custom3 = $custom3;
	}
}

;

/**
 * If the json_encode function does not exist, then create it..
 */

if (!function_exists('json_encode')) {
	function json_encode($a = false)
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a)) {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			} else
				return $a;
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) $result[] = json_encode($v);
			return '[' . join(',', $result) . ']';
		} else {
			foreach ($a as $k => $v) $result[] = json_encode($k) . ':' . json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}


