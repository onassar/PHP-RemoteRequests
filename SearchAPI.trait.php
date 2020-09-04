<?php

    // Namespace overhead
    namespace onassar\RemoteRequests\Traits;

    /**
     * SearchAPI
     * 
     * Trait that helps facilitate searching through a 3rd-party API.
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    trait SearchAPI
    {
        /**
         * _apiKey
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_apiKey = null;

        /**
         * _responseResultsIndex
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_responseResultsIndex = null;

        /**
         * _formatSearchResults
         * 
         * @access  protected
         * @param   array $results
         * @param   string $query
         * @return  array
         */
        protected function _formatSearchResults(array $results, string $query): array
        {
            $results = $this->_includeOriginalQuery($results, $query);
            return $results;
        }

        /**
         * _getAuthRequestData
         * 
         * @access  protected
         * @param   string $requestType
         * @return  array
         */
        protected function _getAuthRequestData(string $requestType): array
        {
            $authRequestData = array();
            return $authRequestData;
        }

        /**
         * _getRandomString
         * 
         * @see     https://stackoverflow.com/questions/4356289/php-random-string-generator
         * @access  protected
         * @param   int $length (default: 32)
         * @return  string
         */
        protected function _getRandomString(int $length = 32): string
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        /**
         * _getSearchQueryRequestData
         * 
         * @access  protected
         * @param   string $query
         * @return  array
         */
        protected function _getSearchQueryRequestData(string $query): array
        {
            $queryRequestData = compact('query');
            return $queryRequestData;
        }

        /**
         * _getSearchRequestURL
         * 
         * @access  protected
         * @return  string
         */
        protected function _getSearchRequestURL(): string
        {
            $host = $this->_hosts['search'] ?? $this->_host;
            $path = $this->_paths['search'];
            $url = 'https://' . ($host) . ($path);
            return $url;
        }

        /**
         * _includeOriginalQuery
         * 
         * @access  protected
         * @param   array $results
         * @param   string $query
         * @return  array
         */
        protected function _includeOriginalQuery(array $results, string $query): array
        {
            foreach ($results as &$result) {
                $result['original_query'] = $query;
            }
            return $results;
        }

        /**
         * _setSearchRequestData
         * 
         * @access  protected
         * @param   string $query
         * @return  void
         */
        protected function _setSearchRequestData(string $query): void
        {
            $authRequestData = $this->_getAuthRequestData('search');
            $paginationRequestData = $this->_getPaginationRequestData();
            $queryRequestData = $this->_getSearchQueryRequestData($query);
            $args = array($authRequestData, $queryRequestData, $paginationRequestData);
            $this->mergeRequestData(... $args);
        }

        /**
         * _setSearchRequestURL
         * 
         * @access  protected
         * @return  void
         */
        protected function _setSearchRequestURL(): void
        {
            $searchURL = $this->_getSearchRequestURL();
            $this->setURL($searchURL);
        }

        /**
         * search
         * 
         * @access  public
         * @param   string $query
         * @param   array &persistentResults (default: array())
         * @return  array
         */
        public function search(string $query, array &$persistentResults = array()): array
        {
            // Request results
            $this->_setSearchRequestData($query);
            $this->_setSearchRequestURL();
            $key = $this->_responseResultsIndex;
            $response = $this->_getURLResponse() ?? array();
            $results = $response[$key] ?? array();
            if (empty($results) === true) {
                return $results;
            }

            // Format + more than enough found
            $results = $this->_formatSearchResults($results, $query);
            $resultsCount = count($results);
            $mod = $this->_offset % $this->_getResultsPerRequest();
            if ($mod !== 0) {
                array_splice($results, 0, $mod);
            }
            $persistentResults = array_merge($persistentResults, $results);
            $persistentResultsCount = count($persistentResults);
            if ($persistentResultsCount >= $this->_limit) {
                return array_slice($persistentResults, 0, $this->_limit);
            }
            if ($resultsCount < $this->_maxResultsPerRequest) {
                return array_slice($persistentResults, 0, $this->_limit);
            }

            // Recursively get more
            $this->_offset += count($results);
            return $this->search($query, $persistentResults);
        }

        /**
         * setAPIKey
         * 
         * @access  public
         * @param   string $apiKey
         * @return  void
         */
        public function setAPIKey(string $apiKey): void
        {
            $this->_apiKey = $apiKey;
        }
    }
