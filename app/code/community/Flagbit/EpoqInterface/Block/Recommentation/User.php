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
* @version $Id: User.php 238 2009-07-03 09:22:08Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Block_Recommentation_User extends Flagbit_EpoqInterface_Block_Recommentation_Abstract
{
	
	protected $_collectionModel = 'epoqinterface/recommendation_user';
    const XML_STATUS_PATH		= 'epoqinterface/display_recommendation/user';		
	
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if(!Mage::getStoreConfig(self::XML_STATUS_PATH)){
        	return '';
        }
    	
        return parent::_toHtml();
    }	
}
