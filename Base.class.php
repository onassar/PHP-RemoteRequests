<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

    /**
     * Base
     * 
     * Class for basic cURL and stream requests.
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class Base
    {
        /**
         * _attemptSleepDelay
         * 
         * @access  protected
         * @var     int (default: 2000)
         */
        protected $_attemptSleepDelay = 2000;

        /**
         * _debugMode
         * 
         * @access  protected
         * @var     bool (default: false)
         */
        // protected $_debugMode = false;
        protected $_debugMode = true;

        /**
         * _expectedResponseFormat
         * 
         * @access  protected
         * @var     string (default: 'plain/text')
         */
        protected $_expectedResponseFormat = 'plain/text';

        /**
         * _ignoreErrors
         * 
         * @access  protected
         * @var     bool (default: true)
         */
        protected $_ignoreErrors = true;

        /**
         * _lastRemoteRequestHeaders
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_lastRemoteRequestHeaders = array();

        /**
         * _logClosure
         * 
         * @access  protected
         * @var     null|Closure (default: null)
         */
        protected $_logClosure = null;

        /**
         * _maxAttempts
         * 
         * @access  protected
         * @var     int (default: 2)
         */
        protected $_maxAttempts = 2;

        /**
         * _requestApproach
         * 
         * @access  protected
         * @var     string (default: 'streams')
         */
        protected $_requestApproach = 'streams';

        /**
         * _requestData
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_requestData = array();

        /**
         * _requestMethod
         * 
         * @access  protected
         * @var     string (default: 'get')
         */
        protected $_requestMethod = 'get';

        /**
         * _requestTimeout
         * 
         * @access  protected
         * @var     int (default: 10)
         */
        protected $_requestTimeout = 10;

        /**
         * _url
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_url = null;

        /**
         * __construct
         * 
         * @access  public
         * @return  void
         */
        public function __construct()
        {
        }

        /**
         * _addURLParams
         * 
         * @access  protected
         * @param   string $url
         * @param   array $params
         * @return  string
         */
        protected function _addURLParams(string $url, array $params): string
        {
            if (empty($params) === true) {
                return $url;
            }
            $queryString = http_build_query($params);
            $piece = parse_url($url, PHP_URL_QUERY);
            if ($piece === null) {
                $url = ($url) . '?' . ($queryString);
                return $url;
            }
            $url = ($url) . '&' . ($queryString);
            return $url;
        }

        /**
         * _attempt
         * 
         * Method which accepts a closure, and repeats calling it until
         * $maxAttempts have been made.
         * 
         * This was added to account for requests failing (for a variety of
         * reasons).
         * 
         * @access  protected
         * @param   \Closure $closure
         * @param   int $attempt (default: 1)
         * @return  null|string
         */
        protected function _attempt(\Closure $closure, int $attempt = 1): ?string
        {
            try {
                $response = call_user_func($closure);
                if ($attempt !== 1) {
                    $msg = 'Subsequent success on attempt #' . ($attempt);
                    $this->_log($msg);
                }
                return $response;
            } catch (\Exception $exception) {
                $msg = 'Failed closure';
                $this->_log($msg);
                $msg = $exception->getMessage();
                $this->_log($msg);
                $maxAttempts = $this->_maxAttempts;
                if ($attempt < $maxAttempts) {
                    $delay = $this->_attemptSleepDelay;
                    $msg = 'Going to sleep for ' . ($delay);
                    $this->_log($msg);
                    $this->_sleep($delay);
                    $response = $this->_attempt($closure, $attempt + 1);
                    return $response;
                }
                $msg = 'Failed attempt';
                $this->_log($msg);
            }
            return null;
        }

        /**
         * _debugModeLog
         * 
         * @access  protected
         * @param   array $values,...
         * @return  bool
         */
        protected function _debugModeLog(... $values): bool
        {
            $debugMode = $this->_debugMode;
            if ($debugMode === false) {
                return false;
            }
            $this->_log(... $values);
            return true;
        }

        /**
         * _get
         * 
         * @throws  \Exception
         * @access  protected
         * @return  null|mixed
         */
        protected function _get()
        {
            // Failed request
            $response = $this->_requestURL();
            if ($response === null) {
                return null;
            }

            // plain/text response
            $expectedResponseFormat = $this->_expectedResponseFormat;
            if ($expectedResponseFormat === 'plain/text') {
                return $response;
            }

            // JSON response
            if ($expectedResponseFormat === 'json') {
                json_decode($response);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }
                $response = json_decode($response, true);
                return $response;
            }

            // Invalid expected response format set
            $msg = 'Invalid expected response format set';
            throw new \Exception($msg);
        }

        /**
         * _getCURLRequestHeaders
         * 
         * @access  protected
         * @return  array
         */
        protected function _getCURLRequestHeaders(): array
        {
            $headers = array();
            return $headers;
        }

        /**
         * _getRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestData(): array
        {
            $requestData = $this->_requestData;
            return $requestData;
        }

        /**
         * _getRequestMethod
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestMethod(): string
        {
            $requestMethod = $this->_requestMethod;
            $requestMethod = strtoupper($requestMethod);
            return $requestMethod;
        }

        /**
         * _getRequestStreamContext
         * 
         * @access  protected
         * @return  resource
         */
        protected function _getRequestStreamContext()
        {
            $options = $this->_getRequestStreamContextOptions();
            $streamContext = stream_context_create($options);
            return $streamContext;
        }

        /**
         * _getRequestStreamContextOptions
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestStreamContextOptions(): array
        {
            $ignoreErrors = $this->_ignoreErrors;
            $debugMode = $this->_debugMode;
            if ($debugMode === true) {
                $ignoreErrors = false;
            }
            $requestMethod = $this->_getRequestMethod();
            $requestTimeout = $this->_requestTimeout;
            $options = array(
                'http' => array(
                    'ignore_errors' => $ignoreErrors,
                    'method' => $requestMethod,
                    'timeout' => $requestTimeout
                )
            );
            return $options;
        }

        /**
         * _getRequestURL
         * 
         * Returns the URL that ought to be requested, which will be modified
         * depending on whether or not any request data was set.
         * 
         * @access  protected
         * @return  string
         */
        protected function _getRequestURL(): string
        {
            $url = $this->_url;
            $requestData = $this->_getRequestData();
            $url = $this->_addURLParams($url, $requestData);
            return $url;
        }

        /**
         * _log
         * 
         * @access  protected
         * @param   array $values,...
         * @return  bool
         */
        protected function _log(... $values): bool
        {
            if ($this->_logClosure === null) {
                foreach ($values as $value) {
                    error_log($value);
                }
                return false;
            }
            $closure = $this->_logClosure;
            $args = $values;
            call_user_func_array($closure, $args);
            return true;
        }

        /**
         * _parseCURLResponse
         * 
         * This method was required because at times the cURL requests would not
         * return the headers, which would cause issues.
         * 
         * @access  protected
         * @param   string $response
         * @return  array
         */
        protected function _parseCURLResponse(string $response): array
        {
            $delimiter = "\r\n\r\n";
            $pieces = explode($delimiter, $response);
            if (count($pieces) === 1) {
                $headers = '';
                $body = $response;
                $response = array($headers, $body);
                return $response;
            }
            list($headers, $body) = explode("\r\n\r\n", $response, 2);
            $response = array($headers, $body);
            return $response;
        }

        /**
         * _requestURL
         * 
         * @throws  \Exception
         * @access  protected
         * @return  null|string
         */
        protected function _requestURL(): ?string
        {
            if ($this->_requestApproach === 'cURL') {
                $response = $this->_requestURLUsingCURL();
                return $response;
            }
            if ($this->_requestApproach === 'streams') {
                $response = $this->_requestURLUsingStreams();
                return $response;
            }
            $msg = 'Invalid request approach';
            throw new \Exception($msg);
        }

        /**
         * _requestURLUsingCURL
         * 
         * @see     https://stackoverflow.com/a/9183272/115025
         * @access  protected
         * @return  null|string
         */
        protected function _requestURLUsingCURL(): ?string
        {
            $closure = function() {
                $url = $this->_getRequestURL();
                $this->_debugModeLog('curl', $url);
                $requestTimeout = $this->_requestTimeout;
                $headers = $this->_getCURLRequestHeaders();
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $requestTimeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $requestTimeout);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                return $response;
            };
            $response = $this->_attempt($closure);
            if ($response === false) {
                return null;
            }
            if ($response === null) {
                return null;
            }
            list($headers, $body) = $this->_parseCURLResponse($response);
            $this->_setCURLResponseHeaders($headers);
            return $body;
        }

        /**
         * _requestURLUsingStreams
         * 
         * @see     http://php.net/manual/en/reserved.variables.httpresponseheader.php
         * @access  protected
         * @return  null|string
         */
        protected function _requestURLUsingStreams(): ?string
        {
            $closure = function() {
                $url = $this->_getRequestURL();
                $this->_debugModeLog('stream', $url);
                $streamContext = $this->_getRequestStreamContext();
                $response = file_get_contents($url, false, $streamContext);
                $this->_lastRemoteRequestHeaders = $http_response_header ?? $this->_lastRemoteRequestHeaders;
                return $response;
            };
            $response = $this->_attempt($closure);
            if ($response === false) {
                return null;
            }
            if ($response === null) {
                return null;
            }
            return $response;
        }

        /**
         * _setCURLResponseHeaders
         * 
         * @access  protected
         * @param   string $headers
         * @return  void
         */
        protected function _setCURLResponseHeaders(string $headers): void
        {
            $headers = explode("\n", $headers);
            $this->_lastRemoteRequestHeaders = $headers;
        }

        /**
         * _sleep
         * 
         * @access  protected
         * @param   int $duration in milliseconds
         * @return  void
         */
        protected function _sleep(int $duration): void
        {
            usleep($duration * 1000);
        }

        /**
         * get
         * 
         * @throws  \Exception
         * @access  public
         * @return  mixed
         */
        public function get()
        {
            $url = $this->_url;
            if ($url === null) {
                $msg = '$url not set';
                throw new \Exception($msg);
            }
            $response = $this->_get();
            return $response;
        }

        /**
         * getFormattedHeaders
         * 
         * @access  public
         * @return  array
         */
        public function getFormattedHeaders(): array
        {
            $headers = $this->_lastRemoteRequestHeaders ?? array();
            $formattedHeaders = array();
            foreach ($headers as $header) {
                $pieces = explode(':', $header);
                if (count($pieces) !== 2) {
                    continue;
                }
                $key = trim($pieces[0]);
                $value = trim($pieces[1]);
                $formattedHeaders[$key] = $value;
            }
            return $formattedHeaders;
        }

        /**
         * mergeRequestData
         * 
         * @access  public
         * @param   array $incomingRequestData
         * @return  void
         */
        public function mergeRequestData(array $incomingRequestData): void
        {
            $requestData = $this->_requestData;
            $requestData = array_merge($requestData, $incomingRequestData);
            $this->setRequestData($requestData);
        }

        /**
         * setAttemptSleepDelay
         * 
         * @access  public
         * @param   int $attemptSleepDelay
         * @return  void
         */
        public function setAttemptSleepDelay(int $attemptSleepDelay): void
        {
            $this->_attemptSleepDelay = $attemptSleepDelay;
        }

        /**
         * setExpectedResponseFormat
         * 
         * @access  public
         * @param   string $expectedResponseFormat
         * @return  void
         */
        public function setExpectedResponseFormat(string $expectedResponseFormat): void
        {
            $this->_expectedResponseFormat = $expectedResponseFormat;
        }

        /**
         * setLogClosure
         * 
         * @access  public
         * @param   \Closure $closure
         * @return  void
         */
        public function setLogClosure(\Closure $closure): void
        {
            $this->_logClosure = $closure;
        }

        /**
         * setMaxAttempts
         * 
         * @access  public
         * @param   int $maxAttempts
         * @return  void
         */
        public function setMaxAttempts(int $maxAttempts): void
        {
            $this->_maxAttempts = $maxAttempts;
        }

        /**
         * setRequestApproach
         * 
         * @access  public
         * @param   string $requestApproach
         * @return  void
         */
        public function setRequestApproach(string $requestApproach): void
        {
            $this->_requestApproach = $requestApproach;
        }

        /**
         * setRequestData
         * 
         * @access  public
         * @param   array $requestData
         * @return  void
         */
        public function setRequestData(array $requestData): void
        {
            $this->_requestData = $requestData;
        }

        /**
         * setRequestDataValue
         * 
         * @access  public
         * @param   string $key
         * @param   mixed $value
         * @return  void
         */
        public function setRequestDataValue(string $key, $value): void
        {
            $this->_requestData[$key] = $value;
        }

        /**
         * setRequestMethod
         * 
         * @access  public
         * @param   string $requestMethod
         * @return  void
         */
        public function setRequestMethod(string $requestMethod): void
        {
            $requestMethod = strtolower($requestMethod);
            $this->_requestMethod = $requestMethod;
        }

        /**
         * setRequestTimeout
         * 
         * Sets the timeout for requests (for both cURL and stream approaches)
         * in seconds.
         * 
         * @access  public
         * @param   int $requestTimeout
         * @return  void
         */
        public function setRequestTimeout(int $requestTimeout): void
        {
            $this->_requestTimeout = $requestTimeout;
        }

        /**
         * setURL
         * 
         * @access  public
         * @param   string $url
         * @return  void
         */
        public function setURL(string $url): void
        {
            $this->_url = $url;
        }
    }
