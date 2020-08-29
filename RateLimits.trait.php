<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

    /**
     * RateLimits
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    trait RateLimits
    {
        /**
         * _getRateLimitProperty
         * 
         * @access  protected
         * @param   string $headerKey
         * @return  null|int
         */
        protected function _getRateLimitProperty(string $headerKey): ?int
        {
            $formattedHeaders = $this->getFormattedHeaders();
            $value = $formattedHeaders[$headerKey] ?? null;
            return $value;
        }

        /**
         * getRateLimits
         * 
         * @access  public
         * @return  array
         */
        public function getRateLimits(): array
        {
            $remaining = $this->_getRateLimitProperty('X-Ratelimit-Remaining');
            $limit = $this->_getRateLimitProperty('X-Ratelimit-Limit');
            $reset = $this->_getRateLimitProperty('X-Ratelimit-Reset');
            $rateLimits = compact('remaining', 'limit', 'reset');
            return $rateLimits;
        }
    }
