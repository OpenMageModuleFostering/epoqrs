<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Flagbit
 * @package    EpoqInterface
 * @copyright  Copyright (c) 2012 Flagbit
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author     Mike Becker<mike.becker@flagbit.de>
 */
class Flagbit_EpoqInterface_Block_Recommendation extends Mage_Core_Block_Abstract
{
    public function _toHtml()
    {
        if (!$section = $this->getSection()) {
            $section = 'product';
        }
        
        if (Mage::getStoreConfig(Flagbit_EpoqInterface_Block_Recommendation_Abstract::XML_USING_AJAX)) {
            // create one block for ajax input. Rules will be evaluated in js
            echo $this->getLayout()->createBlock('epoqinterface/recommendation_' . $section)
                ->setTemplate('epoqinterface/recommendation/'.$section.'.phtml')
                ->toHtml();
            return;
        }
        
        foreach (Mage::helper('epoqinterface')->getRulesForSection($section) as $rule)
        {
            // create block for each rule
            echo $this->getLayout()->createBlock('epoqinterface/recommendation_' . $section)
                ->setRule($rule)
                ->setTemplate('epoqinterface/recommendation/'.$section.'.phtml')
                ->toHtml();
        }
        
        
    }
}