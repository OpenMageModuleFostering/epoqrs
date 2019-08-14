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
* @version $Id: Abstract.php 466 2010-07-08 12:30:54Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Model_Resource_Eav_Mysql4_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    
    protected $_productIds = array();
    
    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
    	return count($this->_productIds);
    }
    
    public function setProductIds($ids)
    {
        $this->_productIds = $ids;    
        return $this;
    }
    
    
    /**
     * Load entities records into items
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        if (!is_array($this->_productIds)) {
            return $this;
        }
        
    	$productIds = array_unique($this->_productIds);
//     	$idFieldName = 'entity_id';
    	$idFieldName = Mage::helper('epoqinterface')->getIdFieldName();

        if (!empty($productIds)) {

        	// add Filter to Query
        	$this->addFieldToFilter(
        		$idFieldName,
        		array('in'=>$productIds)
        	);
            // TODO: check for saleable and visibility etc.
            
	        $this->_pageSize = null;      
	        $entity = $this->getEntity();
	        
			$this->getSelect()->reset(Zend_Db_Select::LIMIT_COUNT);
           	$this->getSelect()->reset(Zend_Db_Select::LIMIT_OFFSET);	        
	
	        $this->printLogQuery($printQuery, $logQuery);
	
	        try {
	            $rows = $this->_fetchAll($this->getSelect());
	        } catch (Exception $e) {
	            Mage::printException($e, $this->getSelect());
	            $this->printLogQuery(true, true, $this->getSelect());
	            throw $e;
	        }
	
	        $items = array();
	        foreach ($rows as $v) {        	
				$items[$v[$idFieldName]] = $v;
	        }

	        foreach ($productIds as $productId){
	        	
	        	if(empty($items[$productId])){
	        		continue;
	        	}
	            $object = $this->getNewEmptyItem()
	                ->setData($items[$productId]);
  
	            $this->addItem($object);
	            if (isset($this->_itemsById[$object->getId()])) {
	                $this->_itemsById[$object->getId()][] = $object;
	            }
	            else {
	                $this->_itemsById[$object->getId()] = array($object);
	            }        	
	        }
	        
        }
        return $this;
    }      


    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    public function setOrder($attribute, $dir='desc')
    {
        return $this;
    }
    
    /* */
    
    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir='asc')
    {
    	if ($attribute == 'position') {
    		return $this;
    	}
    
    	return parent::addAttributeToSort($attribute, $dir);
    }
    
    /**
     * Add collection filters by identifiers
     *
     * @param   mixed $productId
     * @return  Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addIdFilter($productId, $exclude = false)
    {
    	if(!is_array($productId)){
    		$productId = array($productId);
    	}
    
    	$this->_productIds = $productId;
    	
    	if (empty($productId)) {
    	    $this->_setIsLoaded(true);
    	    return $this;
    	}
    	if (is_array($productId)) {
    	    if (!empty($productId)) {
    	        if ($exclude) {
    	            $condition = array('nin' => $productId);
    	        } else {
    	            $condition = array('in' => $productId);
    	        }
    	    } else {
    	        $condition = '';
    	    }
    	} else {
    	    if ($exclude) {
    	        $condition = array('neq' => $productId);
    	    } else {
    	        $condition = $productId;
    	    }
    	}
    	$this->addFieldToFilter(Mage::helper('epoqinterface')->getIdFieldName(), $condition);

    	return $this;
    }
    
    /**
     * Set collection page size
     *
     * @param   int $size
     * @return  Varien_Data_Collection
     */
    public function setPageSize($size)
    {
    	$this->_pageSize = null;
    	return $this;
    }
    
}
