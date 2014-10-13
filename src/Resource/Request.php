<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;

	class Request extends Resource\Request
	{
		use HTTP\Message;


		/**
		 *
		 */
		public function checkMethod($method)
		{
			return $this->method == strtoupper($method);
		}


		/**
		 *
		 */
		public function getMethod()
		{
			return $this->method;
		}


		/**
		 *
		 */
		public function  setMethod($method)
		{
			$this->method = strtoupper($method);

			return $this;
		}
	}
}
