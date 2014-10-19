<?php namespace Inkwell\HTTP\Gateway
{
	use Inkwell\HTTP;
	use Inkwell\Transport;
	use Dotink\Flourish\Collection;
	use Dotink\Flourish\URL;

	class Server implements Transport\GatewayInterface
	{
		/**
		 *
		 */
		public function populate($request)
		{
			$request->headers         = new Collection(getallheaders());
			$request->params          = new Collection(array_merge($_GET, $_POST));
			$request->cookies         = new HTTP\CookieCollection($_COOKIE);

			list($protocol, $version) = explode('/', $_SERVER['SERVER_PROTOCOL']);

			$request->setMethod($_SERVER['REQUEST_METHOD']);
			$request->setProtocol($protocol);
			$request->setVersion($version);
			$request->setUrl(new URL());
		}


		/**
		 *
		 */
		public function transport($response)
		{
			$this->prepareCookies($response);
			$this->prepareHeaders($response);

			http_response_code($response->getStatusCode());

			echo $response->get();
		}


		/**
		 *
		 */
		private function prepareCookies($response)
		{
			foreach ($response->cookies as $name => $params) {
				settype($params, 'array');
				array_unshift($params, $name);

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
