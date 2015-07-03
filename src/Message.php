<?php namespace Inkwell\HTTP
{
	use Dotink\Flourish;

	use Psr\Http\Message\StreamInterface;

	use InvalidArgumentException;

	/**
	 *
	 */
	trait Message
	{
		/**
		 * A list of HTTP headers which cannot contain multiple values
		 *
		 * @static
		 * @access protected
		 * @var array
		 */
		static protected $singleValueHeaders = [

		];


		/**
		 *
		 */
		public $headers = NULL;


		/**
		 *
		 */
		protected $headerTranslations = array();


		/**
		 *
		 */
		protected $protocol = NULL;


		/**
		 *
		 */
		protected $protocolVersion = NULL;


		/**
		 *
		 */
		public function checkProtocol($protocol)
		{
			return strtoupper($protocol) == $this->protocol;
		}


		/**
		 *
		 */
		public function checkVersion($version)
		{
			return version == $this->version;
		}


		/**
		 * Gets the body of the message.
		 *
		 * @return StreamInterface Returns the body as a stream.
		 */
		public function getBody()
		{
			$data = $this->get();

			if (is_object($data)) {
				if ($data instanceof StreamInterface) {
					return $data;
				} elseif (is_callable([$data, 'compose'])) {
					$data = $data->compose();

				} elseif (is_callable([$data, '__toString'])) {
					$data = (string) $data;

				} else {
					$data = '';
				}

			} elseif (is_array($data)) {
				$data = 'array';
			}

			$stream = new Stream('php://temp', 'r+');

			$stream->write($data);

			return $stream;
		}



		/**
		 * Retrieves a message header value by the given case-insensitive name.
		 *
		 * This method returns an array of all the header values of the given case-insensitive
		 * header name.
		 *
		 * If the header does not appear in the message, this method will return an empty array.
		 *
		 * @param string $name Case-insensitive header field name
		 * @return array An array of string values as provided for the given header
		 */
		public function getHeader($name)
		{
			$value = $this->getHeaderLine($name);

			return $value
				? explode(',', $value)
				: array();
		}


		/**
		 * Retrieves a comma-separated string of the values for a single header.
		 *
		 * This method returns all of the header values of the given case-insensitive header name
		 * as a string concatenated together using a comma.
		 *
		 * If the header does not appear in the message, this method will return an empty string.
		 *
		 * @param string $name Case-insensitive header field name
		 * @return string A string of values as provided for the given header separated by commas
		 */
		public function getHeaderLine($name)
		{
			if (!$this->hasHeader($name)) {
				return '';
			}

			return $this->headers->get($this->headerTranslations[strtolower($name)]);
		}


		/**
		 * Retrieves all message header values.
		 *
		 * The keys represent the header name as it will be sent over the wire, and each value is
		 * an array of strings associated with the header.
		 *
		 * While header names are not case-sensitive, getHeaders() will preserve the exact case in
		 * which headers were originally specified.
		 *
		 * @return array Returns an associative array of the message's headers.
		 */
		public function getHeaders()
		{
			$headers = array();

			foreach ($this->headers->get() as $key => $value) {
				$headers[$key] = !in_array(strtolower($key), static::$singleValueHeaders)
					? explode(',', $value)
					: $value;
			}

			return $headers;
		}


		/**
		 * Retrieves the HTTP protocol version as a string.
		 *
		 * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
		 *
		 * @return string HTTP protocol version.
		 */
		public function getProtocolVersion()
		{
			return $this->protocolVersion ?: '1.1';
		}


		/**
		 * Checks if a header exists by the given case-insensitive name.
		 *
		 * @param string $name Case-insensitive header field name.
		 * @return bool Returns TRUE if any header names match the given header, FALSE otherwise
		 */
		public function hasHeader($name)
		{
			return isset($this->headerTranslations[strtolower($name)]);
		}


		/**
		 *
		 */
		public function setProtocol($protocol)
		{
		 	$protocol = strtoupper($protocol);

			if (!in_array($protocol, ['HTTP'])) {
				throw new Flourish\ProgrammerException(
					'Invalid protocol %s specified',
					$protocol
				);
			}

			$this->protocol = $protocol;

			return $this;
		}


		/**
		 *
		 */
		public function setProtocolVersion($version)
		{
			if (!preg_match('#\d+[.]\d+#', $version)) {
				throw new Flourish\ProgrammerException(
					'Invalid version %s specified',
					$version
				);
			}

			$this->protocolVersion = $version;

			return $this;
		}



		/**
		 * Get an instance with the specified header appended with the given value.
		 *
		 * Existing values for the specified header will be maintained. The new value(s) will be
		 * appended to the existing list. If the header did not exist previously, it will be added.
		 *
		 * @param string $name Case-insensitive header field name to add.
		 * @param string|array $value The header value(s) to add
		 * @return Message A new object instance with the header value(s) set
		 * @throws InvalidArgumentException for invalid header names or values
		 */
		public function withAddedHeader($name, $value)
		{
			$new   = clone $this;
			$tname = strtolower($name);

			settype($value, 'array');

			if (isset($new->headerTranslations[$tname])) {
				//
				// TODO: Check to make sure header name is not listed on single value headers and
				// if it is, blow up.
				//

				array_unshift($value, $new->headers->get($new->headerTranslations[$tname]));

				$new->headers->set($new->headerTranslations[$tname], implode(',', $value));

			} else {
				//
				// TODO: check to make sure if header is in list of single value headers that the
				// $value is not an array, or if it is, only contains a single element.
				//

				$new->headerTranslations[$tname] = $name;

				$new->headers->set($name, $value);

			}

			return $new;
		}



		/**
		 * Get an instance with the provided value replacing the specified header.
		 *
		 * While header names are case-insensitive, the casing of the header will be preserved by
		 * this function, and returned from getHeaders().
		 *
		 * @param string $name Case-insensitive header field name
		 * @param string|array $value The header value(s) to set
		 * @return Message A new object instance with the header value(s) set
		 * @throws InvalidArgumentException for invalid header names or values
		 */
		public function withHeader($name, $value)
		{
			$new   = clone $this;
			$tname = strtolower($name);

			if (!isset($new->headerTranslations[$tname])) {
				$new->headerTranslations[$tname] = $name;
			}

			$new->headers->set($new->headerTranslations[$tname], $value);

			return $new;
		}



		/**
		 * Get an instance without the specified header.
		 *
		 * @param string $name Case-insensitive header field name to remove.
		 * @return Message A new object instance with the header value(s) set
		 */
		public function withoutHeader($name)
		{
			$new   = clone $this;
			$tname = strtolower($name);

			if (isset($new->headerTranslations[$tname])) {
				$new->headers->set($new->headerTranslations[$tname], NULL);

				unset($new->headerTranslations[$tname]);
			}

			return $new;
		}


		/**
		 * Get an instance with the specified HTTP protocol version.
		 *
		 * The version string must contain only the HTTP version number (e.g.,
		 * "1.1", "1.0").
		 *
		 * @param string $version HTTP protocol version
		 * @return Message A new object instance with the protocol version set
		 */
		public function withProtocolVersion($version)
		{
			$new = clone $this;

			$new->setProtocolVersion($version);

			return $new;
		}


		/**
		 * Get an instance with the specified message body.
		 *
		 * @param StreamInterface $body Body.
		 * @return Message A new object instance with the body set
		 * @throws InvalidArgumentException When the body is not valid.
		 */
		public function withBody(StreamInterface $body)
		{
			$new = clone $this;

			$new->set($body);

			return $new;
		}
	}
}
