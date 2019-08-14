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
* @version $Id: Data.php 583 2010-11-26 10:08:21Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/
class Flagbit_EpoqInterface_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * XML Config Path to Product Identifier Setting
     *
     * @var string
     */
    const XML_CONFIG_PATH_PRODUCT_IDENTIFIER = 'epoqinterface/config/identifier';
    const XML_CONFIG_PATH_COOKIE_STATUS = 'epoqinterface/config/cookie';

    protected $_skuToIdMapping;

    /**
     * get product sku by id faster than single lookup
     *
     * @param int $id
     * @return string
     */
    public function getProductSkuById($ids){
         
        if($this->_skuToIdMapping == null){
    
            $productCol = Mage::getResourceModel('catalog/product_collection');
            $idsSelect = clone $productCol->getSelect();
            $idsSelect->reset(Zend_Db_Select::ORDER);
            $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $idsSelect->reset(Zend_Db_Select::COLUMNS);
            $idsSelect->from(null, array('e.'.$productCol->getEntity()->getIdFieldName(), 'e.sku'));
            $idsSelect->resetJoinLeft();
             
            $this->_skuToIdMapping = $productCol->getConnection()->fetchPairs($idsSelect, array());
             
        }
        
        // return single sku or array with skus
        if (!is_array($ids)) {
            return isset($this->_skuToIdMapping[$ids]) ? $this->_skuToIdMapping[$ids] : null;
        } else {
            $skuArr = array();
            
            foreach ($ids as $id) {
                $skuArr[] = isset($this->_skuToIdMapping[$id]) ? $this->_skuToIdMapping[$id] : null;
            }
            
            return $skuArr;
        }
    }
    
    /**
     * get current Product Id
     * @param string $type indicate which type of data should used
     *
     * @return int
     */
    public function getProductId($type = 'dynamic')
    {
    	$productId = null;
		if(Mage::registry('current_product') instanceof Mage_Catalog_Model_Product
			&& Mage::registry('current_product')->getId()){
			$productId = Mage::registry('current_product')->getData(($type!='dynamic'?$type:$this->getIdFieldName()));

			if(version_compare(Mage::getVersion(), '1.9.0', '<') 
				&& (string)Mage::getConfig()->getModuleConfig('Enterprise_PageCache')->active == 'true'){
				$processor = Mage::getSingleton('enterprise_pagecache/processor');
	            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
	            Mage::app()->saveCache(Mage::registry('current_product')->getData(($type!='static'?$type:$this->getIdFieldName())), $cacheId);
			}			
			
		}elseif((string)Mage::getConfig()->getModuleConfig('Enterprise_PageCache')->active == 'true'){
		    	
		    $processor = Mage::getSingleton('enterprise_pagecache/processor');
            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
            if(Mage::app()->loadCache($cacheId)){
            	$productId = Mage::app()->loadCache($cacheId);
            }		
		}

		return $productId;
    }  		

    /**
     * get Entity ID Field Name by Configuration or via Entity
     *
     * @return string
     */
    public function getIdFieldName()
    {
        $idFieldName = Mage::getStoreConfig(self::XML_CONFIG_PATH_PRODUCT_IDENTIFIER);
        if(!$idFieldName){
            $idFieldName = 'entity_id';
        }
        return $idFieldName;
    }

    
    /**
     * return cookie status
     *
     * @return bool
     */
    public function getCookieStatus()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_COOKIE_STATUS);
    }

    
    /**
     * get the rule of the current section
     *
     * @return string $rule
     */
    public function getRulesForSection($section, $asString = false)
    {
        $rule = '';
        switch(strtolower($section))
        {
            case 'product':
                $rule = Mage::getStoreConfig(Flagbit_EpoqInterface_Block_Abstract::XML_RULE_PRODUCT);
                break;
            case 'cart':
                $rule = Mage::getStoreConfig(Flagbit_EpoqInterface_Block_Abstract::XML_RULE_CART);
                break;
            case 'user':
                $rule = Mage::getStoreConfig(Flagbit_EpoqInterface_Block_Abstract::XML_RULE_CUSTOMER);
                break;
            default:
                $rule = '';
                break;
        }
    
        if (true === $asString) {
            return $rule;
        }
        
        return explode(';',$rule);
    }
    
}
