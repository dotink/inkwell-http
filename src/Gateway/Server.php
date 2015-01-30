<?php namespace Inkwell\HTTP\Gateway
{
	use Inkwell\Transport;
	use Inkwell\HTTP\CookieCollection;
	use Inkwell\HTTP\CookieWrapperInterface;
	use Dotink\Flourish\Collection;
	use Dotink\Flourish\URL;

	class Server implements Transport\GatewayInterface
	{
		/**
		 *
		 */
		private $cookieWrapper = NULL;


		/**
		 *
		 */
		public function __construct(CookieWrapperInterface $cookie_wrapper = NULL)
		{
			$this->cookieWrapper = $cookie_wrapper;
		}


		/**
		 *
		 */
		public function populate($request)
		{
			$request->headers = new Collection($this->getHeaders());
			$request->params  = new Collection($this->getParams());
			$request->cookies = new CookieCollection($this->getCookies());

			list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);

			$request->setMethod($_SERVER['REQUEST_METHOD']);
			$request->setProtocol($protocol);
			$request->setVersion($version);
			$request->setUrl(new URL());
		}


		/**
		 *
		 */
		public function getCookies()
		{
			$cookies = $_COOKIE;

			if (isset($this->cookieWrapper)) {
				foreach ($cookies as $name => $value) {
					$cookies[$name] = $this->cookieWrapper->unwrap($value);
				}
			}

			return $cookies;
		}


		/**
		 *
		 */
		public function getParams()
		{
			return array_merge($_GET, $_POST);
		}


		/**
		 *
		 */
		public function getHeaders()
		{
			$headers = array();

			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$header = substr($name, 5);
					$header = str_replace('_', ' ', $header);
					$header = strtolower($header);
					$header = ucwords($header);
					$header = str_replace(' ', '-', $header);

					$headers[$header] = $value;
				}
			}

			return $headers;
		}

		/**
		 *
		 */
		public function transport($response)
		{
			$this->prepareCookies($response);
			$this->prepareHeaders($response);

			http_response_code($response->getStatusCode());

			$content = $response->get();

			if (is_object($content)) {
				if (!is_callable([$content, 'compose'])) {
					throw new Flourish\ProgrammerException(
						'Cannot transport object of type "%s", must have compose()',
						get_class($content)
					);
				}

				echo $content->compose();

			} else {
				echo $content;
			}
		}


		/**
		 *
		 */
		private function prepareCookies($response)
		{
			foreach ($response->cookies as $name => $params) {
				settype($params, 'array');
				array_unshift($params, $name);

				if ($this->cookieWrapper) {
					$params[1] = $this->cookieWrapper->wrap($params[1]);
				}

				call_user_func_array('setcookie', $params);
			}
		}


		/**
		 *
		 */
		private function prepareHeaders($response)
		{
			foreach ($response->headers as $name => $value) {
				header(sprintf('%s: %s', $name, $value));
			}
		}
	}
}
