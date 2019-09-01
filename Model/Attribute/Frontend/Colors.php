<?php

namespace Yu\Filter\Model\Attribute\Frontend;

class Colors extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend {

    public function getValue(\Magento\Framework\DataObject $object)
    {
        return parent::getValue($object);
    }
}
