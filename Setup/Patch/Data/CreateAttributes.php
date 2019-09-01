<?php

namespace Yu\Filter\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class CreateAttributes implements DataPatchInterface, PatchRevertableInterface
{

    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'yu_colors',
            [
                'type'                       => 'varchar',
                'label'                      => 'Colors',
                'input'                      => 'multiselect',
                'required'                   => false,
                'user_defined'               => true,
                'searchable'                 => false,
                'filterable'                 => true,
                'comparable'                 => false,
                'visible_in_advanced_search' => false,
                'is_used_in_grid'            => false,
                'is_visible_in_grid'         => false,
                'is_filterable_in_grid'      => false,
                'is_visible_on_front'        => true,
                'source'                     => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'backend'                    => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'frontend'                   => \Yu\Filter\Model\Attribute\Frontend\Colors::class,
                'option'                     => ['values' => [
                        'red',
                        'green',
                        'blue',
                        'grey',
                        'black',
                        'yellow',
                        'pink',
                        'brown',
                        'white']],
                'group'                      => 'Product Details'
            ]
        );
    }

    public function getAliases()
    {
        return [];
    }

    public function revert()
    {
        
    }

    public static function getDependencies()
    {
        return [];
    }

}
