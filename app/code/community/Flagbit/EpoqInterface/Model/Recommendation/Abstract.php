<?php 
/*                                                                       *
* This script is part of the epoq Recommendation Service project         *
*                                                                        *
* epoqinterface is free software; you can redistribute it and/or modify  *
* it under the terms of the GNU General Public License version 2 as      *
* published by the Free Software Foundation.                             *
*                                                                        *
* This script is distributed in the hope that it will be useful, but     *
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
* TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
* Public License for more details.                                       *
*                                                                        *
* @version $Id: Abstract.php 666 2011-07-06 13:44:33Z rieker $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Model_Recommendation_Abstract extends Flagbit_EpoqInterface_Model_Abstract
{
    
    /** @var array $_collection **/
    protected $_collection = array();
    
    /** @var Zend_Rest_Client_Result $_result **/
    protected $_result;
    
    /**
     * Constructor
     *
     * @param string|Zend_Uri_Http $uri URI for the web service
     * @return void
     */
    public function __construct()
    {   
        $args = func_get_args();
        if (empty($args[0])) {
            $args[0] = array();
        }
        $this->_data = $args[0];

        $this->_construct();

        // get Data
        $this->_result = $this->_doRequest();
    }

    
    /**
     * get Product Collection
     *
     * @param string $rule
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getCollection($rule = 'default')
    {
        if (empty($this->_collection[$rule])) {
            /*@var $collection Flagbit_EpoqInterface_Model_Rescource_Eav_Mysql4_Product_Collection */
            $this->_collection[$rule] = Mage::getResourceModel('epoqinterface/product_collection');
            $this->_collection[$rule]->setProductIds($this->_getProductIdsByRule($rule));
            
        }  
        return $this->_collection[$rule];
        
    }    

    
    /**
     * return Zend Rest Client
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        if (!$this->_restClient instanceof Zend_Rest_Client) {
            if (array_key_exists('action', $this->getData()) && $this->getData('action') == 'processCart') {
                $url = $this->getRestUrl().'processCart?'.$this->_httpBuildQuery($this->getParamsArray());
            } else {
                $url = $this->getRestUrl().'getRecommendations'.'?'.$this->_httpBuildQuery($this->getParamsArray());
            }
// Zend_Debug::dump($url);
            $this->_restClient = new Zend_Rest_Client($url);
            $this->_restClient->getHttpClient()->setConfig(
                array(
                    'timeout' => Mage::getStoreConfig(self::XML_TIMEOUT_PATH)
                )
            );
        }
        return $this->_restClient;
    }

    
    /**
     * 
     * @param unknown_type $rule
     */
    protected function _getProductIdsByRule($rule = 'default')
    {
        // generate product ID array
        $productIds = array();
        
        if ($this->_result instanceof Zend_Rest_Client_Result &&
            $this->_result->getIterator() instanceof SimpleXMLElement) {
                
            foreach ($this->_result->getIterator()->domain as $domain)
            {
                $domainRule = (string)$domain->attributes()->rules;
                
                if ($rule == $domainRule) {
                    foreach ($domain->recommendation as $product)
                    {
                         $productIds[] = (string) $product->productId;
                    }
                }
            }

            // set Data
            $this->setRecommendationId((string) $this->_result->getIterator()->domain->recommendationId);

            $this->getSession()->setLastRecommendationId($this->getRecommendationId());
            $this->getSession()->setLastRecommendationProducts($productIds);

        }
        
        return $productIds;
    }
    
    /**
     * add parameters to url
     * 
     * @param array $array
     * @param string/int $previousKey
     * 
     * @return string $string
     */
    protected function _httpBuildQuery($array, $previousKey='')
    {
        $string = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $string .= ($string ? '&' : '').$this->_httpBuildQuery($value, $key);
                continue;
            }
            $string .= ($string ? '&' : '').(is_numeric($key) && $previousKey ? $previousKey : $key ).($value ? '='.urlencode($value) : '');
        }
        return $string;
    }

    
    /**
     * (non-PHPdoc)
     * @see Flagbit_EpoqInterface_Model_Abstract::getParamsArray()
     */
    protected function getParamsArray()
    {
        $variables = array(
            'tenantId'       => Mage::getStoreConfig(self::XML_TENANT_ID_PATH),
            'sessionId'      => Mage::getSingleton('core/session')->getSessionId(),
            'demo'           => Mage::getStoreConfig(self::XML_DEMO_PATH) ? Mage::getStoreConfig(self::XML_DEMO_ITEMS_AMOUNT) : 0,
            'widgetTheme'    => 'multixml',   
            'rules'          => Mage::helper('epoqinterface')->getRulesForSection($this->_section,true)
        ); 

        if ($customerId = Mage::getSingleton('customer/session')->getId()) {
            $variables['customerId'] = $customerId;
        }

        return $variables;
    }
    
}