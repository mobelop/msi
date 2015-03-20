<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Listing
 */
class Listing extends AbstractComponent
{
    const NAME = 'listing';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare custom data
     *
     * @return void
     */
    public function prepare()
    {
        $this->getContext()->addButtons($this->getData('buttons'), $this);

        $jsConfig = $this->getConfiguration($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getContext()->getNamespace(), $jsConfig);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDataSourceData()
    {
        /** @var Column[] $columns */
        $columns = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof Columns) {
                foreach ($component->getChildComponents() as $column) {
                    if ($column instanceof Column) {
                        $columns[] = $column;
                    }
                }
            }
        }
        $dataSources = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof DataSourceInterface) {
                $data = $component->getDataProvider()->getData();
                if (!empty($data['items']) && !empty($columns)) {
                    foreach ($columns as $column) {
                        $column->prepareItems($data['items']);
                    }
                }

                $dataSources[] = [
                    'type' => $component->getComponentName(),
                    'name' => $component->getName(),
                    'dataScope' => $component->getContext()->getNamespace(),
                    'config' => array_replace_recursive(
                        [
                            'data' => $data,
                            'totalCount' => $component->getDataProvider()->count(),
                        ],
                        (array) $component->getData('config'),
                        [
                            'params' => [
                                'namespace' => $this->getContext()->getNamespace()
                            ],
                        ]
                    ),
                ];
            }
        }
        return $dataSources;
    }
}
