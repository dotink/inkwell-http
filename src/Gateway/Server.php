<?php namespace Inkwell\HTTP\Gateway
{
	use Inkwell\HTTP;
	use Inkwell\Transport;
	use Dotink\Flourish\Collection;

	class Server implements Transport\GatewayInterface
	{
		/**
		 *
		 */
		public function populate($request)
		{
			$request->headers = new Collection(getallheaders());
			$request->params  = new Collection(array_merge($_GET, $_POST));
			$request->cookies = new HTTP\CookieCollection($_COOKIE);

			$request->setMethod($_SERVER['REQUEST_METHOD']);
			$request->setProtocol(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos('/')));
			$request->setVersion(substr($_SERVER['SERVER_PROTOCOL'], strpos('/') + 1));
		}


		/**
		 *
		 */
		public function transport($response)
		{
			$this->prepareCookies($response);
			$this->prepareHeaders($response);
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
