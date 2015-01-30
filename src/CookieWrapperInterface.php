<?php namespace Inkwell\HTTP
{
	interface CookieWrapperInterface
	{
		/**
		 *
		 */
		public function wrap($value);


		/**
		 *
		 */
		public function unwrap($value);
	}
}
