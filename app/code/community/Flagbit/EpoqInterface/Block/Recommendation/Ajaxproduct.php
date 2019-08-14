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
* @version $Id: Product.php 574 2010-11-19 08:19:43Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Block_Recommendation_Ajaxproduct extends Flagbit_EpoqInterface_Block_Recommendation_Abstract
{
	
	/**
	* Retrieve loaded category collection
	 *
	* @return Mage_Eav_Model_Entity_Collection_Abstract
	*/
	public function getItemCollection()
	{
// 	    Zend_Debug::dump($this->getProductIds());
		if (is_null($this->_productCollection))
		{
			$_layer = Mage::getModel('catalog/layer');
			$this->_productCollection = Mage::getResourceModel('epoqinterface/product_collection');
			$this->_productCollection->addIdFilter($this->getProductIds());
			$this->_productCollection->setPageSize(count($this->getProductIds()));
			$_layer->prepareProductCollection($this->_productCollection);
		}
		return $this->_productCollection;
	}
	
	public function resetItemsIterator()
	{
	}
	
	protected function _prepareData()
	{
		$this->getItems();
		return $this;
	}
	
	/**
	 * Wrapper for standart strip_tags() function with extra functionality for html entities
	 *
	 * @param string $data
	 * @param string $allowableTags
	 * @param bool $allowHtmlEntities
	 *
	 * @return string
	 */
	
	public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
	{
		if(method_exists(Mage_Catalog_Block_Product_List, __FUNCTION__))
		{
			return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
		}
		
		$result = strip_tags($data, $allowableTags);
		return $allowHtmlEntities ? $this->escapeHtml($result, $allowableTags) : $result;
	}
	
	/**
	* Escape html entities
	*
	* @param   mixed $data
	* @param   array $allowedTags
	*
	* @return  mixed
	*/
	public function escapeHtml($data, $allowedTags = null)
	{
		if (is_array($data))
		{
			$result = array();
			foreach ($data as $item)
			{
				$result[] = $this->escapeHtml($item);
			}
		} else {
			if (strlen($data))
			{
				if (is_array($allowedTags) and !empty($allowedTags))
				{
					$allowed = implode('|', $allowedTags);
					$result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
					$result = htmlspecialchars($result);
					$result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
				} else {
					$result = htmlspecialchars($data);
				}
			} else {
				$result = $data;
			}
		}
		return $result;
	}
}
