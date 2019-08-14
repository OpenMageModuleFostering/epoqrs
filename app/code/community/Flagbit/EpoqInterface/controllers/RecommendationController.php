<?php
class Flagbit_EpoqInterface_RecommendationController  extends Mage_Core_Controller_Front_Action
{
    public function listAction()
    {
        // indicate the template to load
        if (!$section = Mage::app()->getRequest()->getParam('section')) {
            $section = 'product';
        }

        $html = $this->getLayout()->createBlock('epoqinterface/recommendation_ajaxproduct')
            ->setProductIds(Mage::app()->getRequest()->getParam('pid'))
            ->setTemplate('epoqinterface/recommendation/'.$section.'.phtml')
            ->toHtml();

        $this->getResponse()->setBody($html);
    }

    /**
     * list recommendations to use in email. template include inline-css to provide the layout
     */
    public function maillistAction()
    {
        $html = $this->getLayout()->createBlock('epoqinterface/recommendation_ajaxproduct')
            ->setProductIds(Mage::app()->getRequest()->getParam('pid'))
            ->setTemplate('epoqinterface/recommendation/mailproduct.phtml')
            ->toHtml();

        $this->getResponse()->setBody($html);
    }

}
