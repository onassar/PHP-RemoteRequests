<?php

    // Namespace overhead
    namespace onassar\RemoteRequests\Traits;

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
         * If this value is greater than the $maxResultsPerRequest value, then
         * recursive calls may be made.
         * 
         * @access  protected
         * @var     null|int (default: null)
         */
        protected $_limit = null;

        /**
         * _maxResultsPerRequest
         * 
         * The $maxResultsPerRequest property defines the maximum number of objects that
         * can be retrieved through an API endpoint. This is defined by _them_,
         * and has nothing to do with application and/or business logic.
         * 
         * @access  protected
         * @var     null|int (default: null)
         */
        protected $_maxResultsPerRequest = null;

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
            $resultsPerPage = $this->_getResultsPerRequest();
            $offset = $this->_roundToLower($offset, $resultsPerPage);
            $page = ceil($offset / $resultsPerPage) + 1;
            return $page;
        }

        /**
         * _getPaginationRequestData
         * 
         * Returns an array of pagination data that should be included in any
         * requests. In the future, the keys should be defined as properties to
         * allow for flexibility in URL params.
         * 
         * @access  protected
         * @return  array
         */
        protected function _getPaginationRequestData(): array
        {
            $page = $this->_getPage();
            $resultsPerPage = $this->_getResultsPerRequest();
            $paginationRequestData = array(
                'page' => $page,
                'per_page' => $resultsPerPage
            );
            return $paginationRequestData;
        }

        /**
         * _getResultsPerRequest
         * 
         * @access  protected
         * @return  int
         */
        protected function _getResultsPerRequest(): int
        {
            $limit = $this->_limit;
            $maxResultsPerRequest = $this->_maxResultsPerRequest;
            $resultsPerPage = min($limit, $maxResultsPerRequest);
            return $resultsPerPage;
        }

        /**
         * _getURL
         * 
         * @access  protected
         * @return  null|mixed
         */
        protected function _getURLResponse()
        {
            $this->setExpectedResponseContentType('application/json');
            $response = parent::_getURLResponse();
            return $response;
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
         * setMaxResultsPerPage
         * 
         * @access  public
         * @param   int $maxResultsPerRequest
         * @return  void
         */
        public function setMaxResultsPerPage(int $maxResultsPerRequest): void
        {
            $this->_maxResultsPerRequest = $maxResultsPerRequest;
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
    }
