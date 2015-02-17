<?php namespace Inkwell\HTTP
{
	use Dotink\Flourish\Collection;
	use SplFileInfo;

	class FileCollection extends Collection
	{
		/**
		 *
		 */
		public function get($name = NULL, $default = NULL)
		{
			if ($name !== NULL) {
				$value = parent::get($name, NULL);

				return is_array($value) && isset($value['tmp_name']) && is_string($value['tmp_name'])
					? new SplFileInfo($value['tmp_name'])
					: $default;
			}

			return $default;
		}


		/**
		 *
		 */
		public function set($name, $path = NULL, $file_name = NULL, $type = NULL)
		{
			if ($path === NULL && is_array($values = func_get_arg(0))) {
				foreach ($values as $name => $value) {
					$this->set($name, $value);
				}

			} else {
				parent::set($name, [
					'name'     => $file_name ?: pathinfo($path, PATHINFO_BASENAME),
					'type'     => $type,
					'size'     => filesize($path),
					'error'    => NULL,
					'tmp_name' => $path,
				]);
			}

			return $this;
		}
	}
}
