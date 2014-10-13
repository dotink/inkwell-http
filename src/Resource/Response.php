<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;

	class Response extends Resource\Response
	{
		use HTTP\Message;


		/**
		 *
		 */
		static protected $states = [
			200 => 'Ok',
			201 => 'Created',
			202 => 'Accepted',
			204 => 'No Content',
			301 => 'Permanent Redirect',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Not Authorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Not Allowed',
			406 => 'Not Acceptable',
			500 => 'Internal Server Error',
			503 => 'Unavailable'
		];


		/**
		 *
		 */
		public function checkStatus($status)
		{
			return $this->status == $status;
		}


		/**
		 *
		 */
		public function checkStatusCode($code)
		{
			return $this->statusCode == $code;
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
			$this->statusCode = array_search($status, static::$states);

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
			$this->status     = isset(static::$states[$this->statusCode])
				? static::$states[$this->statusCode]
				: FALSE;

			if ($this->status === FALSE) {
				throw new Flourish\ProgrammerException(
					'Invalid status code %s, no matching status',
					$code
				);
			}
		}
	}
}
