<?php namespace Inkwell\HTTP\Gateway
{
	use Inkwell\HTTP;
	use Inkwell\Transport;
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
			$request->files   = new HTTP\FileCollection($this->getFiles());
			$request->params  = new Flourish\Collection($this->getParams());
			$request->cookies = new HTTP\CookieCollection($this->getCookies());
			$request->headers = new Flourish\Collection($this->getHeaders());

			list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);

			$request->setMethod($_SERVER['REQUEST_METHOD']);
			$request->setProtocol($protocol);
			$request->setProtocolVersion($version);
		}


		/**
		 * Get the cookies from the SAPI interface
		 *
		 * In all cases, this will use the `$_COOKIE` superglobal to retrieve cookie values.
		 *
		 * @access public
		 * @return array $cookies The array of cookie values (unwrapped) keyed by name
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
		public function getFiles()
		{
			return $this->normalizeFiles($_FILES);
		}


		/**
		 *
		 */
		public function getParams()
		{
			parse_str(file_get_contents('php://input'), $input);

			return array_merge($_GET, $_POST, $input ?: array());
		}


		/**
		 *
		 */
		public function getHeaders()
		{
			$headers = array();

			foreach ($_SERVER as $name => $value) {
				if (substr($name, 0, 5) == 'HTTP_') {
					$headers[$this->normalizeHeaderName($name)] = $value;
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

			//
			// TODO: handle streaming better
			//

			echo $response->getBody();
		}


		/**
		 *
		 */
		private function normalizeFiles($data)
		{
			if (!is_array($data)) {
				return $data;
			}

			$files     = array();
			$file_keys = ['error', 'name', 'size', 'tmp_name', 'type'];
			$data_keys = array_keys($data);

			sort($data_keys);

			if ($file_keys != $data_keys) {
				foreach ($data_keys as $name) {
					$files[$name] = $this->normalizeFiles($data[$name]);
				}

			} elseif (isset($data['name']) && is_array($data['name'])) {
				foreach (array_keys($data['name']) as $name) {
					$files[$name] = $this->normalizeFiles([
						'name'     => $data['name'][$name],
						'type'     => $data['type'][$name],
						'size'     => $data['size'][$name],
						'error'    => $data['error'][$name],
						'tmp_name' => $data['tmp_name'][$name]
					]);
				}

			} else {
				return $data;
			}


			return $files;
		}


		/**
		 *
		 */
		public function normalizeHeaderName($name)
		{
			$header = substr($name, 5);
			$header = str_replace('_', ' ', $header);
			$header = strtolower($header);
			$header = ucwords($header);
			$header = str_replace(' ', '-', $header);

			return $header;
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
