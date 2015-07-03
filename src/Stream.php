<?php namespace Inkwell\HTTP
{
	use Dotink\Flourish;

	use Psr\Http\Message\UriInterface;

	use RuntimeException;

	/**
	 *
	 */
	class Stream implements StreamInterface
	{
		/**
		 *
		 */
		protected $resource;


		/**
		 *
		 */
		public function __construct($file, $mode = 'rw')
		{
			if (is_resource($file)) {
				$this->resource = $file;

			} else {
				$this->resource = fopen((string) $file, $mode);

				if ($this->resource === FALSE) {
					throw new Flourish\ProgrammerException(
						'Could not open %s for streaming',
						$file
					);
				}
			}
		}


		/**
		 *
		 */
		public function __toString()
		{
			if ($this->isReadable()) {
				try {
					$this->rewind();

					return $this->getContents();

				} catch (RuntimeException $e) {
					//
					// Nothing to do
					//
				}
			}

			return '';
		}


		/**
		 * Closes the stream and any underlying resources.
		 *
		 * @access public
		 * @return void
		 */
		public function close()
		{
			if ($this->resource) {
				fclose($this->detach());
			}
		}


		/**
		 * Separates any underlying resources from the stream.
		 *
		 * After the stream has been detached, the stream is in an unusable state.
		 *
		 * @access public
		 * @return resource Underlying PHP stream, if any, NULL if none is set
		 */
		public function detach()
		{
			$resource	   = $this->resource;
			$this->resource = NULL;

			return $resource;
		}


		/**
		 *
		 */
		public function eof()
		{
			return $this->resource
				? feof($this->resource)
				: TRUE;
		}


		/**
		 *
		 */
		public function getContents()
		{
			if (!$this->resource) {
				throw new RuntimeException(
					'Cannot get contents of stream with no resource'
				);
			}

			if (!$this->isReadable()) {
				throw new RuntimeException(
					'Cannot get contents from non-readable stream'
				);
			}

			if (($data = stream_get_contents($this->resource)) === FALSE) {
				throw new RuntimeException(
					'Failed to get contents from stream, an error occurred'
				);
			}

			return $data;
		}


		/**
		 *
		 */
		public function getMetadata($key = null)
		{
			if (!$this->resource) {
				return NULL;
			}

			$meta_data = stream_get_meta_data($this->resource);

			if (!$key) {
				return $meta_data;
			}

			if (!array_key_exists($key, $meta_data)) {
				return NULL;
			}

			return $metadata[$key];
		}


		/**
		 * Get the size of the stream if known.
		 *
		 * @access public
		 * @return integer Returns the size in bytes if known, or NULL if unknown.
		 */
		public function getSize()
		{
			if (!$this->resource) {
				return NULL;
			}

			return fstat($this->resource)['size'];
		}


		/**
		 *
		 */
		public function isReadable()
		{
			if (!$this->resource) {
				return FALSE;
			}

			return in_array($this->getMetadata('mode'), ['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
		}


		/**
		 *
		 */
		public function isSeekable()
		{
			return $this->resource
				? stream_get_meta_data($this->resource)['seekable']
				: FALSE;
		}


		/**
		 *
		 */
		public function isWritable()
		{
			if (!$this->resource) {
				return FALSE;
			}

			return is_writable($this->getMetadata('uri'));
		}


		/**
		 *
		 */
		public function read($length)
		{
			if (!$this->resource) {
				throw new RuntimeException(
					'Cannot read from stream with no resource'
				);
			}

			if (!$this->isReadable()) {
				throw new RuntimeException(
					'Cannot read from non-readable stream'
				);
			}

			if (($data = fread($this->resource, $length)) === FALSE) {
				throw new RuntimeException(
					'Failed to read from stream, an error occurred'
				);
			}

			return $data;
		}


		/**
		 *
		 */
		public function rewind()
		{
			return $this->seek(0);
		}


		/**
		 *
		 */
		public function seek($offset, $whence = SEEK_SET)
		{
			if (!$this->resource) {
				throw new RuntimeException(
					'Cannot seek on stream with no resource'
				);
			}

			if (!$this->isSeekable()) {
				throw new RuntimeException(
					'Cannot seak on non-seekable stream'
				);
			}

			if (fseek($this->resource, $offset, $whence) !== 0) {
				throw new RuntimeExcetpion(
					'Failed to seek stream, an error occurred'
				);
			}

			return TRUE;
		}


		/**
		 *
		 */
		public function tell()
		{
			if (!$this->resource) {
				throw new RuntimeException(
					'Cannot determine position on stream with no resource'
				);
			}

			$position = ftell($this->resource);

			if ($position === FALSE) {
				throw new RuntimeException(
					'Unable to get position on stream resource, an error occurred'
				);
			}

			return $position;
		}


		/**
		 *
		 */
		public function write($data)
		{
			if (!$this->resource) {
				throw new RuntimeException(
					'Cannot write to stream with no resource'
				);
			}

			if (!$this->isWritable()) {
				throw new RuntimeException(
					'Cannot write to non-writable stream'
				);
			}

			if (($bytes = fwrite($this->resource, $data)) === FALSE) {
				throw new RuntimeException(
					'Failed to write to stream, an error occurred'
				);
			}

			return $bytes;
		}

	}
}
