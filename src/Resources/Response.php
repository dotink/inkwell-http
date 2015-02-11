<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;
	use Inkwell\HTTP\CookieCollection;
	use Dotink\Flourish\Collection;
	use Dotink\Flourish;


	class Response extends Resource\Response
	{
		use HTTP\Message;


		/**
		 *
		 */
		public $cookies = NULL;


		/**
		 *
		 */
		public $headers = NULL;


		/**
		 *
		 */
		static protected $defaultStatus = NULL;


		/**
		 *
		 */
		static protected $messages = [

		];


		/**
		 *
		 */
		static protected $states = [
			'Ok'                    => 200,
			'Created'               => 201,
			'Accepted'              => 202,
			'No Content'            => 204,
			'Permanent Redirect'    => 301,
			'Found'                 => 302,
			'See Other'             => 303,
			'Not Modified'          => 304,
			'Temporary Redirect'    => 307,
			'Bad Request'           => 400,
			'Not Authorized'        => 401,
			'Forbidden'             => 403,
			'Not Found'             => 404,
			'Not Allowed'           => 405,
			'Not Acceptable'        => 406,
			'Internal Server Error' => 500,
			'Unavailable'           => 503
		];


		/**
		 *
		 */
		protected $status = NULL;


		/**
		 *
		 */
		protected $statusCode = NULL;


		/**
		 *
		 */
		static public function setDefaultStatus($status)
		{
			static::$defaultStatus = $status;
		}


		/**
		 *
		 */
		static public function addCode($state, $code)
		{
			static::$states[$state] = $code;
		}


		/**
		 *
		 */
		static public function addMessage($state, $message)
		{
			static::$messages[$state] = $message;
		}


		/**
		 *
		 */
		public function __construct($status = NULL, Collection $headers = NULL, CookieCollection $cookies = NULL)
		{
			if ($status === NULL) {
				$this->setStatus(static::$defaultStatus ?: 'Not Found');
			}

			$this->headers = $headers ?: new Collection();
			$this->cookies = $cookies ?: new CookieCollection();
		}


		/**
		 *
		 */
		public function checkStatus($status)
		{
			if (is_array($status)) {
				return in_array($this->status, $status);
			}

			return $this->status == $status;
		}


		/**
		 *
		 */
		public function checkStatusCode($code)
		{
			if (is_array($code)) {
				return in_array($this->statusCode, $code);
			}

			return $this->statusCode == $code;
		}


		/**
		 *
		 */
		public function get()
		{
			if ($this->data === NULL && isset(static::$messages[$this->status])) {
				return static::$messages[$this->status];
			}

			return parent::get();
		}


		/**
		 *
		 */
		public function getStatus()
		{
			return $this->status;
		}


		/**
		 *
		 */
		public function getStatusCode()
		{
			return $this->statusCode;
		}


		/**
		 *
		 */
		public function setStatus($status)
		{
			$this->status     = ucwords($status);
			$this->statusCode = isset(static::$states[$status])
				? static::$states[$status]
				: FALSE;

			if ($this->statusCode === FALSE) {
				throw new Flourish\ProgrammerException(
					'Invalid status %s, no matching code',
					$status
				);
			}
		}


		/**
		 *
		 */
		public function setStatusCode($code)
		{
			$this->statusCode = $code;
			$this->status     = array_search($code, static::$states);

			if ($this->status === FALSE) {
				throw new Flourish\ProgrammerException(
					'Invalid status code %s, no matching status',
					$code
				);
			}
		}
	}
}
