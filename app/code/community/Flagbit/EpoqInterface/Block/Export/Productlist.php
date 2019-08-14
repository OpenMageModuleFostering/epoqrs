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
* @version $Id: Productlist.php 466 2010-07-08 12:30:54Z weller $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
*/

class Flagbit_EpoqInterface_Block_Export_Productlist extends Flagbit_EpoqInterface_Block_Abstract
{

	/**
	 * Class Constuctor
	 * Cache Settings
	 */
	public function __construct(){
		
		// set Cache to one Hour
		$this->setCacheLifetime(3600);   
        $this->setCacheKey(
        	$this->getNameInLayout().
        	Mage::app()->getStore()->getId().
        	$this->getRequest()->getParam('part', 1).
        	$this->getRequest()->getParam('limit', 1000)
        );
        
		parent::__construct();
	}

    /**
     * generates the Output
     *
     * @return string
     */
    protected function _toHtml()
    { 
		// create XML Object
       	$xmlObj = new DOMDocument("1.0", "UTF-8");
		$xmlObj->formatOutput = true;
		
		// add RSS Element and Namespace
		$elemRss = $xmlObj->createElement( 'rss' );
		$elemRss->setAttribute ( 'version' , '2.0' );
		$elemRss->setAttribute ( 'xmlns:g' , 'http://base.google.com/ns/1.0' );
		$elemRss->setAttribute ( 'xmlns:e' , 'http://base.google.com/cns/1.0' );
		$elemRss->setAttribute ( 'xmlns:c' , 'http://base.google.com/cns/1.0' );
		$xmlObj->appendChild( $elemRss );
		
		// add Channel Element
		$elemChannel = $xmlObj->createElement( 'channel' );
		$elemRss->appendChild( $elemChannel );
  			
		// get Products
        $product = Mage::getModel('catalog/product');
        
        /*@var $products Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $products = $product->getCollection()
            ->addStoreFilter()
            ->addAttributeToSort('news_from_date','desc')
            ->addAttributeToSelect(array('name', 'short_description', 'price', 'image'), 'inner');
            //->addAttributeToSelect(array('special_price', 'special_from_date', 'special_to_date'), 'left');
            
        // split Export in Parts 
        if($this->getRequest()->getParam('part')){    
        	$products->getSelect()->limitPage($this->getRequest()->getParam('part', 1), $this->getRequest()->getParam('limit', 1000));
        }
                
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        /*
        using resource iterator to load the data one by one
        instead of loading all at the same time. loading all data at the same time can cause the big memory allocation.
        */
        Mage::getSingleton('core/resource_iterator')
            ->walk($products->getSelect(), array(array($this, 'addNewItemXmlCallback')), array('xmlObj'=> $xmlObj, 'product'=>$product));

        return $xmlObj->saveXML();	
    }


    /**
     * Product iterator callback function
     * add detailinformations to products
     *
     * @param array $args
     */
    public function addNewItemXmlCallback($args)
    {
        $product = $args['product'];
        $this->setData('product', $product);
        
        // reset time limit
        set_time_limit(30);		
       
        /*@var $product Mage_Catalog_Model_Product */
        $product->setData($args['row']);
        $product->load($product->getId());
        $product->loadParentProductIds();
        $parentProduct = $product->getParentProductIds();

        // get Productcategory
        $category = $product->getCategoryCollection()->load()->getFirstItem();
        $this->setData('category', $category);
    
        /*@var $xmlObj DOMDocument*/
        $xmlObj = $args['xmlObj'];

        // create Item xml Element
        $elemItem = $xmlObj->createElement('item');
    
        $data = array(
            'title'         => $product->getName(),
            'link'          => $product->getProductUrl(),        
        
        	// g Namespace
        	'g:id'			=> $product->getId(),
            'description'   => $product->getShortDescription(),
    		'g:price'		=> $this->getProductPrice($product),
        	'g:image_link'		=> (string) $this->helper('catalog/image')->init($product, 'image'),
        	'g:product_type'=> implode('>', $this->getCategoryPath(true)),
        	'g:brand'		=> $this->getProduct()->getManufacturer(),
        	
        	// e Namespace
        	'e:locakey'		=> substr(Mage::getSingleton('core/locale')->getLocale(), 0, 2),
        
        	// c Namespace
        	'c:mgtproducttype'	=> $product->getTypeId(),        
        	
		);
		
		// set Product variant
		if(isset($parentProduct[0])){
			$data['e:variant_of'] = $parentProduct[0];
		}
		
		// add Product Attributes
		$attributes = $this->getProductAttributes();
		foreach($attributes as $key => $value){
			$data['c:'.$key] = $value;
		}
		
		// translate array to XML
        $this->dataToXml($data, 'data', $elemItem, $xmlObj); 

        // add Product to Channel Element
        /*@var $elemChannel DOMNodeList */
        $elemChannel = $xmlObj->getElementsByTagName('channel');
        $elemChannel->item(0)->appendChild( $elemItem );

    }
    
    /**
     * get current Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
    	return $this->getData('product');
    }   
    

    /**
     * get current Category
     *
     * @return unknown
     */
    public function getCategory()
    {
    	return $this->getData('category');
    }     
      

	/**
	 * The main function for converting to an XML document.
	 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	 *
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
	 * @param DomElement $elem - should only be used recursively
	 * @param DOMDocument $xml - should only be used recursively
	 * @return object DOMDocument
	 */
	protected function dataToXml($data, $rootNodeName = 'data', $elem=null, $xml=null)
	{
		
		if ($xml === null)
		{
			$xml = new DOMDocument("1.0", "UTF-8");
			$xml->formatOutput = true;
			$elem = $xml->createElement( $rootNodeName );
  			$xml->appendChild( $elem );
		}
		
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "node_". (string) $key;
			}
			
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z0-9\_\:]/i', '', $key);
			
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$subelem = $xml->createElement( $key );
				$elem->appendChild( $subelem);
				
				// recrusive call.
				$this->DataToXml($value, $rootNodeName, $subelem, $xml);
			}
			else 
			{
				$subelem = $xml->createElement( $key );
				$subelem->appendChild(
					strstr($value, array('<', '>', '&'))
					? $xml->createCDATASection( $value )
					: $xml->createTextNode( $value )
				);
				$elem->appendChild( $subelem );

			}
		}
		
		// pass back as DOMDocument object
		return $xml;
	}	
    
}