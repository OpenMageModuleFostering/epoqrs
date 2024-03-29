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
* @version $Id: Abstract.php 673 2011-07-27 14:18:59Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Block_Abstract extends Mage_Core_Block_Abstract
{

    const XML_TENANT_ID_PATH    = 'epoqinterface/config/tenant_id';		
    const XML_AJAX_ENABLED   	= 'epoqinterface/config/ajax';	
    const XML_DEMO_PATH			= 'epoqinterface/config/demo';	
    const XML_DEMO_ITEMS_AMOUNT = 'epoqinterface/config/demo_items';	

    const XML_RULE_CUSTOMER		= 'epoqinterface/display_recommendation/rule_customer';	
    const XML_RULE_CART			= 'epoqinterface/display_recommendation/rule_cart';	
    const XML_RULE_PRODUCT   	= 'epoqinterface/display_recommendation/rule_product';	
    
    protected $_lastRecommendationId = null;
    protected $_product = null;
	
    
    protected function arrayToString($array, $prefix = null){
    	
    	$output = array();
    	foreach($array as $key => $value){
			if(is_array($value)){
				$output = array_merge($output, $this->arrayToString($value, $key));
			}else{
				$output[] = "	".($prefix ? $prefix."['".$key."']" : $key)." = '".addslashes(str_replace(array("\r", "\n"), array('', ''), $value))."';"; 
			}
		}    	
    	return $prefix === null ? implode("\n", $output) : $output;
    }	
	
    protected function getParamsArray(){
    	
    	$variables = array(
    		'epoq_tenantId'		=> Mage::getStoreConfig(self::XML_TENANT_ID_PATH),
    		'epoq_sessionId'	=> (!Mage::getStoreConfig(self::XML_AJAX_ENABLED) ? Mage::getSingleton('core/session')->getSessionId() : ''),
    		'epoq_demoMode'		=> Mage::getStoreConfig(self::XML_DEMO_PATH) ? Mage::getStoreConfig(self::XML_DEMO_ITEMS_AMOUNT) : '',
    		'epoq_baseUrl'		=> Mage::getBaseUrl(),
    	); 
    	
    	if($customerId = Mage::getSingleton('customer/session')->getId()){
    		$variables['epoq_customerId'] = $customerId;
    	}
    	
    	if($this->getRequest()->getParam('recommendation_id')){
    		$variables['epoq_recommendationId'] = $this->getRequest()->getParam('recommendation_id');
    	}
    	/*
    	if($this->_lastRecommendationId !== null){
    		$variables['epoq_RecommendationId'] = $this->_lastRecommendationId;
    	}*/
    	
    	return $variables;
    }    
    
    protected function getJavascriptOutput($content, $function){
    	
    	$output  = "<script type=\"text/javascript\">\n";
    	$output .= $content."\n";
    	if ($function) {
    	    $output .= "epoq_".$function."();\n";
    	}
    	$output .= "</script>\n";
    	
    	return $output;
    }    
    
	/**
	 * get Session
	 *
	 * @return Flagbit_EpoqInterface_Model_Session
	 */
	protected function getSession(){
		
		return Mage::getSingleton('epoqinterface/session');
	}

	/**
	 * get final Product Price
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return float
	 */
	protected function getProductPrice(Mage_Catalog_Model_Product $product){
		
		if(version_compare(Mage::getVersion(), '1.3.2', '>=')){

			$_taxHelper  = $this->helper('tax');
			$_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
			$_minimalPriceValue = $product->getFinalPrice();
			return $_taxHelper->getPrice($product, $_minimalPriceValue, $_simplePricesTax);						
		}
		
		return $product->getFinalPrice();
	}
	
    protected function getProductAttributes(){
		
		$_attributes = array();
    	
		if($_additional = $this->getAdditionalData()){

			foreach ($_additional as $_data){
				
				$_attributes[$this->__($_data['code'])] = $this->helper('catalog/output')->productAttribute($this->getProduct(), $_data['value'], $_data['code']);
			}
		}   	
    	return $_attributes;
    }
    
    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     */
    public function getAdditionalData(array $excludeAttr = array())
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if (($attribute->getIsVisibleOnFront() or $attribute->getIsSearchable() or $attribute->getIsFilterable()) && !in_array($attribute->getAttributeCode(), $excludeAttr)) {

                $value = $attribute->getFrontend()->getValue($product);

                // TODO this is temporary skipping eco taxes
                if (is_string($value)) {
                    if (strlen($value) && $product->hasData($attribute->getAttributeCode())) {
                        if ($attribute->getFrontendInput() == 'price') {
                            $value = Mage::app()->getStore()->convertPrice($value,true);
                        } elseif (!$attribute->getIsHtmlAllowedOnFront()) {
                            $value = $this->htmlEscape($value);
                        }
                        if(in_array($attribute->getAttributeCode(), array('in_depth'))){
                        	continue;
                        }
                        
                        $data[$attribute->getAttributeCode()] = array(
                           // 'label' => $attribute->getFrontend()->getLabel(),
                           'value' => $value,
                           'code'  => $attribute->getAttributeCode()
                        );
                    }
                }
            }
        }
        return $data;
    }    
    
    protected function getCategoryPath($refresh = false)
    {
        if (!$this->_categoryPath or $refresh == true) {

            $path = array();
            if ($this->getCategory()) {
                $pathInStore = $this->getCategory()->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = Mage::getResourceModel('catalog/category_collection')
                    ->setStore(Mage::app()->getStore())
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('url_key')
                    ->addFieldToFilter('entity_id', array('in'=>$pathIds))
                    ->load()
                    ->getItems();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $categories[$categoryId]->setStoreId(Mage::app()->getStore()->getId());
                        $path[] = $categories[$categoryId]->getName();
                    }
                }
            }


            $this->_categoryPath = $path;
        }
        return $this->_categoryPath;
    }


    /**
     * get current Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {   
    	if($this->_product === null){	
    	    // always receive the id to load product. not whats set in the backend
			$productId = Mage::helper('epoqinterface')->getProductId('entity_id');
			$this->_product = Mage::getSingleton('catalog/product')->load($productId);
    	}    	
    	return $this->_product;
    }   
    
    
    /**
     * Escape quotes in java scripts
     *
     * @param mixed $data
     * @param string $quote
     * @return mixed
     */
    public function jsQuoteEscape($data, $quote = '\'')
    {
        return $this->helper('core')->jsQuoteEscape($data, $quote);
    }    

    /**
     * get current Category
     *
     * @return unknown
     */
    public function getCategory()
    {
    	return Mage::registry('current_category');
    } 	
	
}
