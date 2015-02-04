<?php namespace Inkwell\HTTP
{
	use Dotink\Flourish\Collection;

	class CookieCollection extends Collection
	{
		/**
		 *
		 */
		public function get($name = NULL, $default = NULL)
		{
			if ($name !== NULL) {
				$value = parent::get($name, $default);

				return is_array($value)
					? $value['value']
					: $value;
			}

			$values = array();

			foreach (array_keys(parent::get()) as $name) {
				$values[$name] = $this->get($name, $default);
			}

			return $values;
		}


		/**
		 *
		 */
		public function set($name, $value = NULL, $expire = 0, $path = NULL, $domain = NULL, $secure = FALSE, $httponly = FALSE)
		{
			if ($value === NULL && is_array($values = func_get_arg(0))) {
				foreach ($values as $name => $value) {
					$this->set($name, $value);
				}

			} else {
				parent::set($name, [
					'value'    => $value,
					'expire'   => $expire,
					'path'     => $path,
					'domain'   => $domain,
					'secure'   => $secure,
					'httponly' => $httponly
				]);
			}

			return $this;
		}
	}
}
