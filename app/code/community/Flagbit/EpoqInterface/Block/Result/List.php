<?php
/**
 * Block Form
 * 
 * @category Mage
 * @package  Flagbit_EpoqSearch
 * @author   Rouven Alexander Rieker <rouven.rieker@itabs.de>
 */
class Flagbit_EpoqInterface_Block_Result_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $_layer = Mage::getModel('catalog/layer');
            $this->_productCollection = Mage::getResourceModel('epoqinterface/product_collection');
            $this->_productCollection->addIdFilter($this->getProductIds());
            $this->_productCollection->setPageSize(count($this->getProductIds()));
            $_layer->prepareProductCollection($this->_productCollection);
        }
        return $this->_productCollection;
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
        if(method_exists(Mage_Catalog_Block_Product_List, __FUNCTION__)){
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
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
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