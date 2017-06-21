<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;


class SelectButton extends Template
{
    /**
     * Registry
     *
     * @var Registry
     */
    protected $_registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_registry = $registry;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Konstanchuk_CustomOptionsExport::selectButton.phtml');
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }
}