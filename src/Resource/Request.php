<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;
	use Inkwell\HTTP\CookieCollection;
	use Dotink\Flourish\Collection;

	class Request extends Resource\Request
	{
		use HTTP\Message;

		/**
		 *
		 */
		public $headers = NULL;


		/**
		 *
		 */
		public $params  = NULL;


		/**
		 *
		 */
		public $cookies = NULL;


		/**
		 *
		 */
		public function __construct(Collection $headers = NULL, Collection $params = NULL, CookieCollection $cookies = NULL)
		{
			$this->headers = $headers ?: new Collection();
			$this->params  = $params  ?: new Collection();
			$this->cookies = $cookies ?: new CookieCollection();
		}


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
		public function getUrl()
		{
			return $this->getTarget();
		}


		/**
		 *
		 */
		public function  setMethod($method)
		{
			$this->method = strtoupper($method);

			return $this;
		}


		/**
		 *
		 */
		public function setUrl($url)
		{
			return $this->setTarget($url);
		}
	}
}
