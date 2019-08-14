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
* @version $Id: Product.php 583 2010-11-26 10:08:21Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/
class Flagbit_EpoqInterface_Block_Track_User extends Flagbit_EpoqInterface_Block_Abstract
{
    /**
     * (non-PHPdoc)
     * @see Mage_Core_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {

    	if (!$this->_beforeToHtml()
    		or !$this->getProduct() instanceof Mage_Catalog_Model_Product) {
    		return '';
    	}
        
		return $this->getJavascriptOutput(
            $this->arrayToString(
                $this->getParamsArray()
            ),
            '');
    }

    /**
     * (non-PHPdoc)
     * @see Flagbit_EpoqInterface_Block_Abstract::getParamsArray()
     */
	protected function getParamsArray(){
		
    	$variables = array(
    		'epoq_productId' => '',
    		'epoq_d_rules'	 => Mage::getStoreConfig(Flagbit_EpoqInterface_Block_Abstract::XML_RULE_CUSTOMER),
    	    'epoq_section'   => 'user',
		    'epoq_customerId' => Mage::getSingleton('customer/session')->getId(),
    	);

		return array_merge(parent::getParamsArray(), $variables);
	}
    
        
     
}