<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

    /**
     * Pagination
     * 
     * Trait that helps facilitate paginated requests (which presumes that 
     * responses are coming in as JSON).
     * 
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    trait Pagination
    {
        /**
         * _limit
         * 
         * The $limit property should be used to define how many objects are
         * desirable, regardless of how many objects are returned by an API
         * call.
         * 
         * If this value is greater than the $maxPerPage value, then recursive
         * calls may be made.
         * 
         * @access  protected
         * @var     null|int (default: null)
         */
        protected $_limit = null;

        /**
         * _maxPerPage
         * 
         * The $maxPerPage property defines the maximum number of objects that
         * can be retrieved through an API endpoint. This is defined by _them_,
         * and has nothing to do with application and/or business logic.
         * 
         * @access  protected
         * @var     null|int (default: null)
         */
        protected $_maxPerPage = null;

        /**
         * _offset
         * 
         * This property is used if $paginationApproach is set to 'offset',
         * which implies that requests for objects are based on what offset in
         * the index to begin the retrieval.
         * 
         * @access  protected
         * @var     int (default: 0)
         */
        protected $_offset = 0;

        /**
         * _paginationApproach
         * 
         * Property which allows for either page-based pagination or
         * offset-based pagination.
         * 
         * @access  protected
         * @var     string (default: 'pages')
         */
        protected $_paginationApproach = 'pages';

        /**
         * _get
         * 
         * @access  protected
         * @return  null|mixed
         */
        protected function _get()
        {
            $this->setExpectedResponseFormat('json');
            $response = parent::_get();
            return $response;
        }

        /**
         * _getPage
         * 
         * Returns the page number that any outbound queries should be set as
         * using the $offset to determine what the "next" page would be.
         * 
         * @access  protected
         * @return  int
         */
        protected function _getPage(): int
        {
            $offset = $this->_offset;
            $resultsPerPage = $this->_getResultsPerPage();
            $offset = $this->_roundToLower($offset, $resultsPerPage);
            $page = ceil($offset / $resultsPerPage) + 1;
            return $page;
        }

        /**
         * _getPaginationRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getPaginationRequestData(): array
        {
            $page = $this->_getPage();
            $resultsPerPage = $this->_getResultsPerPage();
            $paginationRequestData = array(
                'page' => $page,
                'per_page' => $resultsPerPage
            );
            return $paginationRequestData;
        }

        /**
         * _getRequestData
         * 
         * @access  protected
         * @return  array
         */
        protected function _getRequestData(): array
        {
            $requestData = parent::_getRequestData();
            $paginationRequestData = $this->_getPaginationRequestData();
            $requestData = array_merge($requestData, $paginationRequestData);
            return $requestData;
        }

        /**
         * _getResultsPerPage
         * 
         * @access  protected
         * @return  int
         */
        protected function _getResultsPerPage(): int
        {
            $limit = $this->_limit;
            $maxPerPage = $this->_maxPerPage;
            $resultsPerPage = min($limit, $maxPerPage);
            return $resultsPerPage;
        }

        /**
         * _roundToLower
         * 
         * @access  protected
         * @param   int $int
         * @param   int $interval
         * @return  int
         */
        protected function _roundToLower(int $int, int $interval): int
        {
            $int = (string) $int;
            $int = preg_replace('/[^0-9]/', '', $int);
            $int = (int) $int;
            $lowered = floor($int / $interval) * $interval;
            return $lowered;
        }

        /**
         * setLimit
         * 
         * @access  public
         * @param   int $limit
         * @return  void
         */
        public function setLimit(int $limit): void
        {
            $this->_limit = $limit;
        }

        /**
         * setMaxPerPage
         * 
         * @access  public
         * @param   int $maxPerPage
         * @return  void
         */
        public function setMaxPerPage(int $maxPerPage): void
        {
            $this->_maxPerPage = $maxPerPage;
        }

        /**
         * setOffset
         * 
         * @access  public
         * @param   int $offset
         * @return  void
         */
        public function setOffset(int $offset): void
        {
            $this->_offset = $offset;
        }

        /**
         * setPaginationApproach
         * 
         * @access  public
         * @param   string $paginationApproach
         * @return  void
         */
        public function setPaginationApproach(string $paginationApproach): void
        {
            $this->_paginationApproach = $paginationApproach;
        }
    }
