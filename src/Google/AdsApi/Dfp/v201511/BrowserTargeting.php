<?php

namespace Google\AdsApi\Dfp\v201511;

class BrowserTargeting
{

    /**
     * @var boolean $isTargeted
     */
    protected $isTargeted = null;

    /**
     * @var \Google\AdsApi\Dfp\v201511\Technology[] $browsers
     */
    protected $browsers = null;

    /**
     * @param boolean $isTargeted
     * @param \Google\AdsApi\Dfp\v201511\Technology[] $browsers
     */
    public function __construct($isTargeted = null, array $browsers = null)
    {
      $this->isTargeted = $isTargeted;
      $this->browsers = $browsers;
    }

    /**
     * @return boolean
     */
    public function getIsTargeted()
    {
      return $this->isTargeted;
    }

    /**
     * @param boolean $isTargeted
     * @return \Google\AdsApi\Dfp\v201511\BrowserTargeting
     */
    public function setIsTargeted($isTargeted)
    {
      $this->isTargeted = $isTargeted;
      return $this;
    }

    /**
     * @return \Google\AdsApi\Dfp\v201511\Technology[]
     */
    public function getBrowsers()
    {
      return $this->browsers;
    }

    /**
     * @param \Google\AdsApi\Dfp\v201511\Technology[] $browsers
     * @return \Google\AdsApi\Dfp\v201511\BrowserTargeting
     */
    public function setBrowsers(array $browsers)
    {
      $this->browsers = $browsers;
      return $this;
    }

}