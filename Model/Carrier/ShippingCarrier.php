<?php
namespace Mattobell\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Mattobell\Shipping\Api\Provider\ShippingRateCaculator;

class ShippingCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'shippingcarrier';

	protected $_logger;
    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;


    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
		$this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

		//$shippingPrice = $this->getConfigData('price');
        //$shippingPrice = $this->shippingPrice($request);
        $shippingPrice = $this->shippingPrice($request);
		$method = $this->_rateMethodFactory->create();
		$method->setCarrier($this->_code);
		$method->setCarrierTitle($this->getConfigData('title'));
		$method->setMethod($this->_code);
		$method->setMethodTitle($this->getConfigData('name'));
		$method->setPrice($shippingPrice);
		$method->setCost($shippingPrice);
		$result->append($method);


        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {

        return [$this->_code=> $this->getConfigData('name')];
    }
    /**
     * Hook
     */



    protected function shippingPrice($request)
    {
        //$price = $this->getConfigData('price');
        $postal_code = $request->_data['dest_postcode'];
        $dest_city = $request->_data['dest_city'];
        $dest_street = $request->_data['dest_street'];
        $dest_postcode = $request->_data['dest_country_id'];
        $dest_region_id = $request->_data['dest_region_id'];
        $dest_country_id = $request->_data['dest_country_id'];
        $dest_region_code = $request->_data['dest_region_code'];
        $package_weight = $request->_data['package_weight']; //package_weight

        $price = $this->shippingRateByLocation($package_weight, $postal_code);
        //$this->_logger->addDebug('OMG', (array) $request->_data);
        return $price;
    }

    public function shippingRateByLocation($weight, $postal_code)
    {
        $shippingRateCaculator = new ShippingRateCaculator();
        return $shippingRateCaculator->calculate($weight, $postal_code);
        //$this->_logger->addDebug($postal_code, ['empty']);
        //return $shippingRateCaculator->calculate($weight, '103242');
    }

}


//http://babystore.ng/checkout/?XDEBUG_SESSION_START=sublime.xdebug
