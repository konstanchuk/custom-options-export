<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Controller\Adminhtml\Export;

use Konstanchuk\CustomOptionsExport\Controller\Adminhtml\Export;
use Magento\Framework\App\ResponseInterface;


class ProductForm extends Export
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $this->exportOptions($productId);
    }
}