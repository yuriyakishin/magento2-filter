<?php

namespace Yu\Filter\Plugin\Ui\Component\Form\Element;

class MultiSelect
{

    public function afterPrepare(\Magento\Ui\Component\Form\Element\MultiSelect $subject, $result)
    {
        if ($subject->getName() === 'yu_colors') {

            $config['component']   = 'Yu_Filter/js/form/element/colors';
            $config['elementTmpl'] = 'Yu_Filter/form/element/colors';
            $config['multiple']    = true;

            $subject->setData('config', array_replace_recursive((array) $subject->getData('config'), $config));
        }
        return $result;
    }

}
