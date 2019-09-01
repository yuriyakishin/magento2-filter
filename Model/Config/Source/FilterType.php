<?php

namespace Yu\Filter\Model\Config\Source;

use \Yu\Filter\Model\Config;

class FilterType implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Config::XML_PATH_FILTER_TYPE_AND, 'label' => __('AND attribute filter')],
            ['value' => Config::XML_PATH_FILTER_TYPE_OR, 'label' => __('OR attribute filter')]
        ];
    }

}
