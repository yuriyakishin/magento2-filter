<?php

namespace Yu\Filter\Model\Layer;

class State extends \Magento\Catalog\Model\Layer\State
{

    /**
     * Get applied to layer filter items
     *
     * @return Item[]
     */
    public function getFilters()
    {
        $filters = $this->getData('filters');
        if ($filters === null) {
            $filters = [];
            $this->setData('filters', $filters);
        }
        return $filters;
    }

}
