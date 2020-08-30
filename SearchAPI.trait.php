<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

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
         * _getQueryRequestData
         * 
         * @access  protected
         * @param   string $query
         * @return  array
         */
        protected function _getQueryRequestData(string $query): array
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
            $host = $this->_host;
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
            $paginationRequestData = $this->_getPaginationRequestData();
            $queryRequestData = $this->_getQueryRequestData($query);
            $this->mergeRequestData($queryRequestData, $paginationRequestData);
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
         * @param   array &persistent (default: array())
         * @return  array
         */
        public function search(string $query, array &$persistent = array()): array
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
            $mod = $this->_offset % $this->_getResultsPerPage();
            if ($mod !== 0) {
                array_splice($results, 0, $mod);
            }
            $persistent = array_merge($persistent, $results);
            $persistentCount = count($persistent);
            if ($persistentCount >= $this->_limit) {
                return array_slice($persistent, 0, $this->_limit);
            }
            if ($resultsCount < $this->_maxPerPage) {
                return array_slice($persistent, 0, $this->_limit);
            }

            // Recursively get more
            $this->_offset += count($results);
            return $this->search($query, $persistent);
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
