<?php namespace Inkwell\HTTP\Resource
{
	use Inkwell\HTTP;
	use Inkwell\Transport\Resource;
	use Inkwell\HTTP\CookieCollection;
	use Inkwell\HTTP\FileCollection;

	use Dotink\Flourish\Collection;

	use Psr\Http\Message\RequestInterface;
	use Psr\Http\Message\UriInterface;

	use InvalidArgumentException;

	/**
	 *
	 */
	class Request extends Resource\Request implements RequestInterface
	{
		use HTTP\Message;

		/**
		 *
		 */
		public $cookies = NULL;


		/**
		 *
		 */
		public $files = NULL;


		/**
		 *
		 */
		public $params  = NULL;


		/**
		 *
		 */
		protected $uri = NULL;


		/**
		 * Clone the request object
		 *
		 * This will clone all child objects as well
		 *
		 * @access public
		 * @return void
		 */
		public function __clone()
		{
			$this->url     = clone $this->url;
			$this->files   = clone $this->files;
			$this->params  = clone $this->params;
			$this->cookies = clone $this->cookies;
			$this->headers = clone $this->headers;
		}


		/**
		 * Construct the new request object with optional information
		 *
		 * @access public
		 * @param UriInterface $uri The URI of the request
		 * @param Collection $headers The headers for the request
		 * @param Collection $params The parameters for the request
		 * @param CookieCollection $cookies The cookies for the request
		 * @param FileCollection $files The files for the request
		 * @return void
		 */
		public function __construct(UriInterface $uri = NULL, Collection $headers = NULL, Collection $params = NULL, CookieCollection $cookies = NULL, FileCollection $files = NULL)
		{
			$this->uri	   = $uri     ?: new HTTP\URI();
			$this->files   = $files   ?: new FileCollection();
			$this->params  = $params  ?: new Collection();
			$this->cookies = $cookies ?: new CookieCollection();
			$this->headers = $headers ?: new Collection();
		}


		/**
		 * Check that the request method is equal to a given value, case-insensitive
		 *
		 * @access public
		 * @param string $method The value to check against
		 * @return bool TRUE if the request method is equal to the value, FALSE otherwise
		 */
		public function checkMethod($method)
		{
			return strtoupper($this->method) == strtoupper($method);
		}


		/**
		 * Retrieves the HTTP method of the request in its original form
		 *
		 * @access public
		 * @return string Returns the request method.
		 */
		public function getMethod()
		{
			return $this->method;
		}


		/**
		 * Retrieves the message's request target.
		 *
		 * Retrieves the message's request-target either as it will appear (for clients), as it
		 * appeared at request (for servers), or as it was specified for the instance (see
		 * `withRequestTarget()`).
		 *
		 * In most cases, this will be the origin-form of the composed URI, unless a value was
		 * provided to the concrete implementation (see `withRequestTarget()` below).
		 *
		 * If no URI is available, and no request-target has been specifically provided, this method * will return the string "/".
		 *
		 * @access public
		 * @return string The request target
		 */
		public function getRequestTarget()
		{
			return $this->getTarget();
		}


		/**
		 * Get the routable target for the request
		 *
		 * @access public
		 * @return string The routable target for the request
		 */
		public function getTarget()
		{
			return $this->target ?: $this->getUri()->getPath() ?: '/';
		}


		/**
		 * Retrieves the URI instance.
		 *
		 * @access public
		 * @return UriInterface The object instance representing the URI of the request.
		 * @link http://tools.ietf.org/html/rfc3986#section-4.3
		 */
		public function getUri()
		{
			return $this->uri;
		}


		/**
		 * Sets the method on the request
		 *
		 * @access public
		 * @param string $method The method to set on the request
		 * @return Request The object instance for method chaining
		 */
		public function setMethod($method)
		{
			$this->method = $method;

			return $this;
		}


		/**
		 * Set the URI for this request
		 *
		 * It is possible to pass a string to represent a partial URL change.
		 *
		 * @access public
		 * @param Uri|string $uri The new URI to set on the request
		 * @return Request The object instance for method chaining
		 */
		public function setUri($uri)
		{
			$this->uri = $this->uri->modify($uri);

			return $this;
		}



		/**
		 * Return an instance with the specific request-target.
		 *
		 * If the request needs a non-origin-form request-target — e.g., for specifying an
		 * absolute-form, authority-form, or asterisk-form - this method may be used to create an
		 * instance with the specified request-target, verbatim.
		 *
		 * @access public
		 * @param mixed $target The request target
		 * @return Request A new object instance with the request target value set
		 * @link http://tools.ietf.org/html/rfc7230#section-2.7
		 */
		public function withRequestTarget($target)
		{
			$new = clone $this;

			$new->setTarget($target);

			return $new;
		}


		/**
		 * Return an instance with the provided HTTP method.
		 *
		 * @access public
		 * @param string $method Case-sensitive method.
		 * @return Request A new object instance with the method value set
		 * @throws InvalidArgumentException for invalid HTTP methods.
		 */
		public function withMethod($method)
		{
			$new = clone $this;

			$new->setMethod($method);

			return $new;
		}


		/**
		 * Returns an instance with the provided URI.
		 *
		 * This method will update the Host header of the returned request by default if the URI
		 * contains a host component. If the URI does not contain a host component, any
		 * pre-existing Host header will be carried over to the returned request.
		 *
		 * You can opt-in to preserving the original state of the Host header by setting
		 * `$preserve_host` to `TRUE`. When `$preserve_host` is set to `TRUE`, this method
		 * interacts with the Host header in the following ways:
		 *
		 * - If the the Host header is missing or empty, and the new URI contains a host component,
		 *   this method will update the Host header in the returned request.
		 * - If the Host header is missing or empty, and the new URI does not contain a host
		 *   component, this method will not update the Host header in the returned request.
		 * - If a Host header is present and non-empty, this method will not update
		 *   the Host header in the returned request.
		 *
		 * @access public
		 * @param UriInterface $uri New request URI to use.
		 * @param bool $preserve_host Preserve the original state of the Host header.
		 * @return Request A new object instance with the uri set to the new URI
		 * @link http://tools.ietf.org/html/rfc3986#section-4.3
		 */
		public function withUri(UriInterface $uri, $preserve_host = FALSE)
		{
			$new       = clone $this;
			$uri_host  = $uri->getHost();
			$orig_host = $new->getHeaderLine('Host');

			if ($host && (!$preserve_host || !$orig_host)) {
				$new->setHeader('Host', $host);
			}

			$new->uri = $uri;

			return $new;
		}
	}
}
