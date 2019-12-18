<?php

namespace Yu\Filter\Model\Layer\Filter;

use \Yu\Filter\Model\Config;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{

    /**
     * @var array 
     */
    static public $selectedAttributes = [];

    /**
     * @var \Magento\Catalog\Model\Layer\FilterList
     */
    protected $filterList;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $attribute;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {

        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );

        $this->attribute                = $this->getAttributeModel();
        $this->scopeConfig              = $scopeConfig;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array
     */
    private function getSelectedAttributes(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);

        $attributeValueArray = array();

        // get attribute values from request
        if (empty($attributeValue)) {
            return [];
        } elseif (is_numeric($attributeValue)) {
            $attributeValueArray[] = $attributeValue;
        } elseif (!is_numeric($attributeValue)) {
            $attributeValueArray = explode(',', $attributeValue);
        }

        static::$selectedAttributes[$this->attribute->getAttributeCode()] = [
            'value'         => $attributeValueArray,
            'backend_model' => $this->attribute->getBackendType()
        ];

        return $attributeValueArray;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValueArray = $this->getSelectedAttributes($request);

        if (empty($attributeValueArray)) {
            return $this;
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        // add attributes to filter
        $productCollection->addFieldToFilter($this->attribute->getAttributeCode(), ['in' => $attributeValueArray]);

        foreach ($attributeValueArray as $attributeValue) {
            // add attributes to state
            $label = $this->getOptionText($attributeValue);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $attributeValue));
        }

        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection  = $this->getLayer()
            ->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData($this->attribute->getAttributeCode());

        $isAttributeFilterable = $this->getAttributeIsFilterable($this->attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $options = $this->attribute->getFrontend()->getSelectOptions();

        $optionsBackend = $this->attribute->setData('store_id', 0)->getSource()->getAllOptions();

        foreach ($options as $option) {
            foreach ($optionsBackend as $optionBackend) {
                $option['label_backend'] = '';
                if ($option['value'] == $optionBackend['value']) {
                    $option['label_backend'] = $optionBackend['label'];
                    break;
                }
            }
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData);
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Build option data
     *
     * @param array $option
     * @param boolean $isAttributeFilterable
     * @param array $optionsFacetedData
     * @return void
     */
    private function buildOptionData($option, $isAttributeFilterable, $optionsFacetedData)
    {
        $value = $this->getOptionValue($option);
        if ($value === false) {
            return;
        }

        $count = $this->getOptionCount($value, $optionsFacetedData);

        if ($isAttributeFilterable) {

            /****************** AND filter *************************/
            if ($this->scopeConfig->getValue(Config::XML_PATH_FILTER_TYPE_FIELD) == Config::XML_PATH_FILTER_TYPE_AND) {

                if ($count === 0) {

                    $collection = $this->getNewCollection($value);

                    if ($collection->getSize() === 0) {
                        return;
                    }
                }
            }

            /****************** OR filter *************************/
            if ($this->scopeConfig->getValue(Config::XML_PATH_FILTER_TYPE_FIELD) == Config::XML_PATH_FILTER_TYPE_OR) {

                $collection = $this->getNewCollection($value);

                if ($collection->getSize() === 0 && $count === 0) {
                    return;
                }

                unset($collection);

                $collection = $this->getNewCollection($value);

                foreach (static::$selectedAttributes as $attributeCode => $attributeData) {
                    if ($attributeCode != $this->attribute->getAttributeCode()) {
                        if ($attributeData['backend_model'] == 'varchar' || $attributeData['backend_model'] == 'text') {
                            $_attributeData = [];
                            foreach ($attributeData['value'] as $_value) {
                                $_attributeData[] = [
                                    'finset' => $_value
                                ];
                            }
                            $collection->addAttributeToFilter($attributeCode, $_attributeData);
                        } else {
                            $collection->addAttributeToFilter($attributeCode, $attributeData['value']);
                        }
                    }
                }

                $count = $count ?: $collection->getSize();
            }
        }

        $selected = 0;
        if (isset(static::$selectedAttributes[$this->attribute->getAttributeCode()])) {
            if (in_array($value, static::$selectedAttributes[$this->attribute->getAttributeCode()]['value'])) {
                $selected = 1;
            }
        }

        $this->itemDataBuilder->addItemDataWithAdditionalParam(
            strip_tags($option['label']),
            $value,
            $count,
            $selected,
            $option['label_backend']
        );
    }

    /**
     * Create and return new product collection
     * 
     * @param bool|string
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getNewCollection($optionValue)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addStoreFilter();
        $collection->addCategoryFilter($this->getLayer()->getCurrentCategory());

        if ($this->attribute->getBackendType() == 'varchar' || $this->attribute->getBackendType() == 'text') {
            $collection->addAttributeToFilter($this->attribute->getAttributeCode(), ['finset' => $optionValue]);
        } else {
            $collection->addAttributeToFilter($this->attribute->getAttributeCode(), $optionValue);
        }

        return $collection;
    }

    /**
     * Retrieve option value if it exists
     *
     * @param array $option
     * @return bool|string
     */
    private function getOptionValue($option)
    {
        if (empty($option['value']) && !is_numeric($option['value'])) {
            return false;
        }

        return $option['value'];
    }

    /**
     * Retrieve count of the options
     *
     * @param int|string $value
     * @param array $optionsFacetedData
     * @return int
     */
    private function getOptionCount($value, $optionsFacetedData)
    {
        return isset($optionsFacetedData[$value]['count']) ? (int) $optionsFacetedData[$value]['count'] : 0;
    }

    /**
     * Initialize filter items
     *
     * @return  \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        $data  = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count'])
                ->setData('selected', $itemData['selected'])
                ->setData('label_backend', $itemData['label_backend']);
        }
        $this->_items = $items;
        return $this;
    }

}