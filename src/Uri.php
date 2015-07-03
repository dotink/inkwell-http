<?php namespace Inkwell\HTTP
{
	use Dotink\Flourish;

	use Psr\Http\Message\UriInterface;

	use InvalidArgumentException;

	/**
	 *
	 */
	class URI extends Flourish\URL implements UriInterface
	{
		/**
		 * Get an instance with the specified URI fragment.
		 *
		 * An empty fragment value is equivalent to removing the fragment.
		 *
		 * @access public
		 * @param string $fragment The fragment to use with the new instance
		 * @return self A new instance with the specified fragment
		 */
		public function withFragment($fragment)
		{
			return $this->modify(['fragment' => $fragment]);
		}


		/**
		 * Get an instance with the specified host.
		 *
		 * An empty host value is equivalent to removing the host.
		 *
		 * @param string $host The hostname to use with the new instance
		 * @return Uri A new instance with the specified host
		 * @throws InvalidArgumentException for invalid hostnames
		 */
		public function withHost($host)
		{
			return $this->modify(['host' => $host]);
		}


		/**
		 * Get an instance with the specified path.
		 *
		 * The path can either be empty or absolute (starting with a slash) or rootless (not
		 * starting with a slash).
		 *
		 * If the path is intended to be domain-relative rather than path relative then it must
		 * begin with a slash ("/"). Paths not starting with a slash ("/") are assumed to be
		 * relative to some base path known to the application or consumer.
		 *
		 * @access public
		 * @param string $path The path to use with the new instance
		 * @return Uri A new instance with the specified path
		 * @throws InvalidArgumentException for invalid paths
		 */
		public function withPath($path)
		{
			return $this->modify(['path' => $path]);
		}


		/**
		 * Get an instance with the specified port.
		 *
		 * A null value provided for the port is equivalent to removing the port information.
		 *
		 * @access public
		 * @param integer $port The port to use with the new instance
		 * @return Uri A new instance with the specified port
		 * @throws InvalidArgumentException for invalid ports
		 */
		public function withPort($port)
		{
			if ($port < 0 || $port > 65535) {
				throw new InvalidArgumentException(
					'The port specified %s is invalid',
					$port
				);
			}

			return $this->modify(['port' => $port]);
		}


		/**
		 * Return an instance with the specified query string.
		 *
		 * This method MUST retain the state of the current instance, and return
		 * an instance that contains the specified query string.
		 *
		 * Users can provide both encoded and decoded query characters.
		 * Implementations ensure the correct encoding as outlined in getQuery().
		 *
		 * An empty query string value is equivalent to removing the query string.
		 *
		 * @param string $query The query string to use with the new instance.
		 * @return self A new instance with the specified query string.
		 * @throws \InvalidArgumentException for invalid query strings.
		 */
		public function withQuery($query)
		{
			return $this->modify(['query' => $query]);
		}


		/**
		 * Get an instance with the specified scheme.
		 *
		 * An empty scheme is equivalent to removing the scheme.
		 *
		 * @param string $scheme The scheme to use with the new instance.
		 * @return Uri A new instance with the specified scheme.
		 * @throws InvalidArgumentException for invalid or unsupported schemes.
		 */
		public function withScheme($scheme)
		{
			if (!isset(static::$defaultPorts[$scheme])) {
				throw new InvalidArgumentException(
					'Unsupported scheme %s',
					$scheme
				);
			}

			return $this->modify(['scheme' => $scehem]);
		}


		/**
		 * Get an instance with the specified user information.
		 *
		 * Password is optional.  An empty string for the user is equivalent to removing user
		 * information.
		 *
		 * @param string $user The user name to use for authority
		 * @param string $pass The password associated with user
		 * @return Uri A new instance with the specified user information
		 */
		public function withUserInfo($user, $pass = null)
		{
			return $this->modify(['user' => $user, 'pass' => $pass]);
		}
	}
}
