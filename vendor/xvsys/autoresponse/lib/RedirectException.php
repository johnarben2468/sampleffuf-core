<?php

namespace xvsys\autoresponse;

/**
 *
 * @author Alexander Schlegel
 */
class RedirectException extends \Exception {

    /**
     *
     * @var string 
     */
    private $url;

    /**
     *
     * @var int 
     */
    private $status;

    /**
     * 
     * @param string $url
     * @param int $status
     */
    public function __construct($url, $status = 301) {
        parent::__construct();
        $this->url = $url;
        $this->status = $status;
    }

    /**
     * 
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * 
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

}
