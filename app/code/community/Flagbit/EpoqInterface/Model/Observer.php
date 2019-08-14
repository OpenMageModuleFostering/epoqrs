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
* @version $Id: Observer.php 8 2009-07-06 15:09:21Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Model_Observer {
	
	/**
	 * get Session
	 *
	 * @return Flagbit_EpoqInterface_Model_Session
	 */
	protected function getSession(){
		
		return Mage::getSingleton('epoqinterface/session');
	}
	
	/**
	 * Observer: Add product to shopping cart (quote)
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkoutCartProductAddAfter($observer){
		
		$this->getSession()->setCartUpdate('add');
	}
	
	/**
	 * Observer: Update cart items information
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function checkoutCartUpdateItemsAfter($observer){

		$this->getSession()->setCartUpdate('update');
	}
	
	
	/**
	 * Observer: Update cart items information
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function controllerActionPredispatchCheckoutCartDelete($observer){
		
		$this->getSession()->setCartUpdate('remove');
	}
	
	
	/**
	 * Observer: salesOrderPlaceAfter
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function salesOrderPlaceAfter($observer){
		
		/*@var $order Mage_Sales_Model_Order */
		$order = $observer->getOrder();		
		
		Mage::getSingleton('epoqinterface/customer_profiles')->send($order);	
	}
	
	/**
	 * Observer: sales_order_place_before
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function salesOrderPlaceBefore($observer){
		
		$this->getSession()->setCartUpdate('process');
		
		/*@var $block Flagbit_EpoqInterface_Block_Track_Cart */
		$block = Mage::app()->getLayout()->createBlock('epoqinterface/track_cart', 'epoqinterface_track_order');

		$this->getSession()->setBlockTrackCartOutput($block->toHtml());
		
	}
	
}