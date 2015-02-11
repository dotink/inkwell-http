<?php namespace Inkwell\HTTP\Gateway
{
	use Inkwell\Transport;
	use Inkwell\HTTP;
	use Dotink\Flourish;

	class Server
	{
		/**
		 *
		 */
		private $cookieWrapper = NULL;


		/**
		 *
		 */
		public function __construct(HTTP\CookieWrapperInterface $cookie_wrapper = NULL)
		{
			$this->cookieWrapper = $cookie_wrapper;
		}


		/**
		 *
		 */
		public function populate(HTTP\Resource\Request $request)
		{
			$request->headers = new Flourish\Collection($this->getHeaders());
			$request->params  = new Flourish\Collection($this->getParams());
			$request->cookies = new HTTP\CookieCollection($this->getCookies());

			list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);

			$request->setMethod($_SERVER['REQUEST_METHOD']);
			$request->setProtocol($protocol);
			$request->setVersion($version);
		}


		/**
		 *
		 */
		public function getCookies()
		{
			$cookies = $_COOKIE;

			if (isset($cookies[session_name()])) {
				unset($cookies[session_name()]);
			}

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
		public function transport(HTTP\Resource\Response $response)
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

				if ($params['value'] === NULL) {
					$params['expire'] = strtotime('-1 year');

				} elseif ($this->cookieWrapper) {
					$params['value'] = $this->cookieWrapper->wrap($params['value']);
				}

				if ($params['path'] === NULL) {
					$params['path'] = '/';
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
