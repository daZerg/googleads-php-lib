<?php

namespace Google\AdsApi\AdWords\v201601\cm;

class AdGroupAd
{

    /**
     * @var int $adGroupId
     */
    protected $adGroupId = null;

    /**
     * @var \Google\AdsApi\AdWords\v201601\cm\Ad $ad
     */
    protected $ad = null;

    /**
     * @var \Google\AdsApi\AdWords\v201601\cm\AdGroupAdExperimentData $experimentData
     */
    protected $experimentData = null;

    /**
     * @var string $status
     */
    protected $status = null;

    /**
     * @var string $approvalStatus
     */
    protected $approvalStatus = null;

    /**
     * @var string[] $trademarks
     */
    protected $trademarks = null;

    /**
     * @var string[] $disapprovalReasons
     */
    protected $disapprovalReasons = null;

    /**
     * @var boolean $trademarkDisapproved
     */
    protected $trademarkDisapproved = null;

    /**
     * @var \Google\AdsApi\AdWords\v201601\cm\Label[] $labels
     */
    protected $labels = null;

    /**
     * @var \Google\AdsApi\AdWords\v201601\cm\String_StringMapEntry[] $forwardCompatibilityMap
     */
    protected $forwardCompatibilityMap = null;

    /**
     * @param int $adGroupId
     * @param \Google\AdsApi\AdWords\v201601\cm\Ad $ad
     * @param \Google\AdsApi\AdWords\v201601\cm\AdGroupAdExperimentData $experimentData
     * @param string $status
     * @param string $approvalStatus
     * @param string[] $trademarks
     * @param string[] $disapprovalReasons
     * @param boolean $trademarkDisapproved
     * @param \Google\AdsApi\AdWords\v201601\cm\Label[] $labels
     * @param \Google\AdsApi\AdWords\v201601\cm\String_StringMapEntry[] $forwardCompatibilityMap
     */
    public function __construct($adGroupId = null, $ad = null, $experimentData = null, $status = null, $approvalStatus = null, array $trademarks = null, array $disapprovalReasons = null, $trademarkDisapproved = null, array $labels = null, array $forwardCompatibilityMap = null)
    {
      $this->adGroupId = $adGroupId;
      $this->ad = $ad;
      $this->experimentData = $experimentData;
      $this->status = $status;
      $this->approvalStatus = $approvalStatus;
      $this->trademarks = $trademarks;
      $this->disapprovalReasons = $disapprovalReasons;
      $this->trademarkDisapproved = $trademarkDisapproved;
      $this->labels = $labels;
      $this->forwardCompatibilityMap = $forwardCompatibilityMap;
    }

    /**
     * @return int
     */
    public function getAdGroupId()
    {
      return $this->adGroupId;
    }

    /**
     * @param int $adGroupId
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setAdGroupId($adGroupId)
    {
      $this->adGroupId = $adGroupId;
      return $this;
    }

    /**
     * @return \Google\AdsApi\AdWords\v201601\cm\Ad
     */
    public function getAd()
    {
      return $this->ad;
    }

    /**
     * @param \Google\AdsApi\AdWords\v201601\cm\Ad $ad
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setAd($ad)
    {
      $this->ad = $ad;
      return $this;
    }

    /**
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAdExperimentData
     */
    public function getExperimentData()
    {
      return $this->experimentData;
    }

    /**
     * @param \Google\AdsApi\AdWords\v201601\cm\AdGroupAdExperimentData $experimentData
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setExperimentData($experimentData)
    {
      $this->experimentData = $experimentData;
      return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
      return $this->status;
    }

    /**
     * @param string $status
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setStatus($status)
    {
      $this->status = $status;
      return $this;
    }

    /**
     * @return string
     */
    public function getApprovalStatus()
    {
      return $this->approvalStatus;
    }

    /**
     * @param string $approvalStatus
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setApprovalStatus($approvalStatus)
    {
      $this->approvalStatus = $approvalStatus;
      return $this;
    }

    /**
     * @return string[]
     */
    public function getTrademarks()
    {
      return $this->trademarks;
    }

    /**
     * @param string[] $trademarks
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setTrademarks(array $trademarks)
    {
      $this->trademarks = $trademarks;
      return $this;
    }

    /**
     * @return string[]
     */
    public function getDisapprovalReasons()
    {
      return $this->disapprovalReasons;
    }

    /**
     * @param string[] $disapprovalReasons
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setDisapprovalReasons(array $disapprovalReasons)
    {
      $this->disapprovalReasons = $disapprovalReasons;
      return $this;
    }

    /**
     * @return boolean
     */
    public function getTrademarkDisapproved()
    {
      return $this->trademarkDisapproved;
    }

    /**
     * @param boolean $trademarkDisapproved
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setTrademarkDisapproved($trademarkDisapproved)
    {
      $this->trademarkDisapproved = $trademarkDisapproved;
      return $this;
    }

    /**
     * @return \Google\AdsApi\AdWords\v201601\cm\Label[]
     */
    public function getLabels()
    {
      return $this->labels;
    }

    /**
     * @param \Google\AdsApi\AdWords\v201601\cm\Label[] $labels
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setLabels(array $labels)
    {
      $this->labels = $labels;
      return $this;
    }

    /**
     * @return \Google\AdsApi\AdWords\v201601\cm\String_StringMapEntry[]
     */
    public function getForwardCompatibilityMap()
    {
      return $this->forwardCompatibilityMap;
    }

    /**
     * @param \Google\AdsApi\AdWords\v201601\cm\String_StringMapEntry[] $forwardCompatibilityMap
     * @return \Google\AdsApi\AdWords\v201601\cm\AdGroupAd
     */
    public function setForwardCompatibilityMap(array $forwardCompatibilityMap)
    {
      $this->forwardCompatibilityMap = $forwardCompatibilityMap;
      return $this;
    }

}