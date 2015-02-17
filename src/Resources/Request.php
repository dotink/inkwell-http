<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;
	use Inkwell\HTTP\CookieCollection;
	use Inkwell\HTTP\FileCollection;

	use Dotink\Flourish\URL;
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
		public $files = NULL;


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
		private $url = NULL;


		/**
		 *
		 */
		public function __construct(URL $url = NULL, Collection $headers = NULL, Collection $params = NULL, CookieCollection $cookies = NULL, FileCollection $files = NULL)
		{
			$this->url     = $url     ?: new Url();
			$this->headers = $headers ?: new Collection();
			$this->params  = $params  ?: new Collection();
			$this->cookies = $cookies ?: new CookieCollection();
			$this->files   = $files   ?: new FileCollection();
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
		public function getTarget()
		{
			return $this->getUrl()->getPath();
		}


		/**
		 *
		 */
		public function getUrl()
		{
			return $this->url;
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
			$this->url = $this->url->modify($url);

			return $this;
		}


		/**
		 *
		 */
		public function setTarget($target)
		{
			$this->url = $this->getUrl()->modify(['path' => $target]);

			return $this;
		}
	}
}
