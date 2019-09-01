<?php
/**
 * This plugin is deprecated
 */

namespace Yu\Filter\Plugin\CatalogSearch\Model\Layer\Filter;

class Attribute
{

    /**
     * Filter item factory
     *
     * @var \Magento\Catalog\Model\Layer\Filter\ItemFactory
     */
    protected $_filterItemFactory;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
    )
    {
        $this->_filterItemFactory = $filterItemFactory;
    }

    public function aroundApply(
        \Magento\CatalogSearch\Model\Layer\Filter\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $attributeValue = $request->getParam($subject->getRequestVar());

        $attributeValueArray = array();
        if (empty($attributeValue) && !is_numeric($attributeValue)) {
            return $subject;
        } elseif (is_numeric($attributeValue)) {
            $attributeValueArray[] = $attributeValue;
        } else {
            $attributeValueArray = explode(',', $attributeValue);
        }

        if (empty($attributeValueArray)) {
            return $subject;
        }

        $attribute = $subject->getAttributeModel();

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $subject->getLayer()
            ->getProductCollection();

        /* $attributeFilterParams = array();
          foreach($attributeValueArray as $_attributeValue) {
          $attributeFilterParams[] = [];
          } */

        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $attributeValueArray);

        $label = $subject->getAttributeModel()->getFrontend()->getOption($attributeValue);

        /** @var \Magento\Catalog\Model\Layer\Filter\Item */
        $_filterItem = $this->_filterItemFactory->create()
            ->setFilter($subject)
            ->setLabel($label)
            ->setValue($attributeValue);

        $subject->getLayer()
            ->getState()
            ->addFilter($_filterItem);

        //$subject->setItems([]); // set items to disable show filtering
        return $subject;
    }

}
