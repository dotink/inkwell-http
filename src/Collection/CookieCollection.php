<?php namespace Inkwell\HTTP
{
	use Inkwell\HTTP;
	use Inkwell\Transport;
	use Dotink\Flourish\Collection;

	class CookieCollection extends Collection
	{
		public function get($name = NULL, $default = NULL)
		{
			$value = parent::get($name, $default);

			if ($name === NULL) {
				return $value;
			}

			return is_array($value)
				? $value[0]
				: $value;
		}


		public function set($name, $value = NULL, $expire = 0, $path = NULL, $domain = NULL, $secure = FALSE, $httponly = FALSE)
		{
			if ($value !== NULL) {
				$value = array_slice(func_get_args(), 1);
			}

			parent::set($name, $value);
		}
	}
}
