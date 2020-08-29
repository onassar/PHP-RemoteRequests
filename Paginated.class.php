<?php

    // Namespace overhead
    namespace onassar\RemoteRequests;

    /**
     * Paginated
     * 
     * Class that helps facilitate paginated requests (which presumes that 
     * responses are coming in as JSON).
     * 
     * @extends Base
     * @link    https://github.com/onassar/PHP-RemoteRequests
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class Paginated extends Base
    {
        /**
         * _expectedResponseFormat
         * 
         * @access  protected
         * @var     string (default: 'json')
         */
        protected $_expectedResponseFormat = 'json';

        /**
         * _limit
         * 
         * This property is used to define the maximum number of objects to
         * attempt to retrieve, before stopping any possible recursive-requests
         * and sending back the response.
         * 
         * @access  protected
         * @var     int (default: 40)
         */
        protected $_limit = 40;

        /**
         * _maxPerPage
         * 
         * This property is used to limit the possible response to a specific
         * number of objects. It's important this is seen as different from
         * $limit in that $limit is more about when to stop making recursive
         * calls, where as this is about how many objects to request _per_ call.
         * 
         * @access  protected
         * @var     int (default: 40)
         */
        protected $_maxPerPage = 40;

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
         * __construct
         * 
         * @access  public
         * @return  void
         */
        public function __construct()
        {
        }

        /**
         * _getPage
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
