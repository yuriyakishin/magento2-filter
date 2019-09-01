<?php

namespace Yu\Filter\Model\Layer\Filter;

class DataBuilder extends \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder
{

    /**
     * Array of items data
     * array(
     *      $index => array(
     *          'label' => $label,
     *          'value' => $value,
     *          'count' => $count,
     *          'selected' => $selected
     *      )
     * )
     *
     * @return array
     */
    protected $_itemsData = [];

    /**
     * Add Item Data
     *
     * @param string $labelFrontend
     * @param string $value
     * @param int $count
     * @param bool $selected
     * @param string $labelBackend
     * @return void
     */
    public function addItemDataWithAdditionalParam($labelFrontend, $value, $count, $selected, $labelBackend)
    {
        $this->_itemsData[] = [
            'label'         => $labelFrontend,
            'value'         => $value,
            'count'         => $count,
            'selected'      => $selected,
            'label_backend' => $labelBackend
        ];
    }

}
