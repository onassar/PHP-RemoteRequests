<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

    /**
     * RateLimits
     * 
     * Trait that helps facilitate rate limit lookups.
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    trait RateLimits
    {
        /**
         * _getRateLimitLimitValue
         * 
         * @access  protected
         * @return  null|int
         */
        protected function _getRateLimitLimitValue(): ?int
        {
            $limit = $this->_getRateLimitProperty('X-Ratelimit-Limit');
            return $limit;
        }

        /**
         * _getRateLimitProperty
         * 
         * @access  protected
         * @param   string $headerKey
         * @return  null|int|string
         */
        protected function _getRateLimitProperty(string $headerKey)
        {
            $headerKey = strtolower($headerKey);
            $formattedHeaders = $this->getFormattedHeaders();
            $formattedHeaders = array_change_key_case($formattedHeaders);
            $value = $formattedHeaders[$headerKey] ?? null;
            return $value;
        }

        /**
         * _getRateLimitRemainingValue
         * 
         * @access  protected
         * @return  null|int
         */
        protected function _getRateLimitRemainingValue(): ?int
        {
            $remaining = $this->_getRateLimitProperty('X-Ratelimit-Remaining');
            return $remaining;
        }

        /**
         * _getRateLimitResetValue
         * 
         * @access  protected
         * @return  null|int|string
         */
        protected function _getRateLimitResetValue()
        {
            $reset = $this->_getRateLimitProperty('X-Ratelimit-Reset');
            return $reset;
        }

        /**
         * getRateLimits
         * 
         * @access  public
         * @return  array
         */
        public function getRateLimits(): array
        {
            $remaining = $this->_getRateLimitRemainingValue();
            $limit = $this->_getRateLimitLimitValue();
            $reset = $this->_getRateLimitResetValue();
            $rateLimits = compact('remaining', 'limit', 'reset');
            return $rateLimits;
        }
    }
