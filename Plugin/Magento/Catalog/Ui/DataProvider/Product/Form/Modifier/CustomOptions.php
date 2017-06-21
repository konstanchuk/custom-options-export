<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Plugin\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CoreCustomOptions;
use Magento\Ui\Component\Container;
use Magento\Framework\View\LayoutFactory;


class CustomOptions
{
    const BUTTON_EXPORT = 'button_export';

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    public function __construct(LayoutFactory $layoutFactory)
    {
        $this->_layoutFactory = $layoutFactory;
    }

    public function afterModifyMeta($subject, $result)
    {
        $html = $this->_layoutFactory->create()
            ->createBlock('Konstanchuk\CustomOptionsExport\Block\Adminhtml\Product\Edit\SelectButton')
            ->toHtml();
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'title' => __('Export Options'),
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/html',
                        'content' => $html,
                        'additionalClasses' => 'action-advanced',
                        'sortOrder' => 10,
                        'visible' => 1,
                    ],
                ],
            ],
        ];
        $result[CoreCustomOptions::GROUP_CUSTOM_OPTIONS_NAME]['children'][CoreCustomOptions::CONTAINER_HEADER_NAME]['children'][static::BUTTON_EXPORT] = $field;
        return $result;
    }
}