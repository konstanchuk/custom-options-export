<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Konstanchuk\CustomOptionsExport\Model\Export as ExportModel;


abstract class Export extends Action
{
    /** @var  Export */
    protected $_exportModel;

    public function __construct(
        Action\Context $context,
        ExportModel $exportModel
    )
    {
        parent::__construct($context);
        $this->_exportModel = $exportModel;
    }

    protected function exportOptions($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        $type = $this->getRequest()->getParam('type');
        if ($type == ExportModel::TYPE_CSV) {
            $this->getResponse()
                ->setHeader('Content-Type', 'application/csv')
                ->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $this->getFileName('csv')))
                ->setHeader('Pragma', 'no-cache')
                ->setContent($this->_exportModel->prepareCsv($productIds));
        } else {
            $this->getResponse()
                ->setHeader('Content-Type', 'text/xml')
                ->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $this->getFileName('xml')))
                ->setHeader('Pragma', 'no-cache')
                ->setContent($this->_exportModel->prepareXml($productIds));
        }
        $this->getResponse()->sendResponse();
    }

    protected function getFileName($ext = 'xml')
    {
        return sprintf('custom-options-%s.%s', date('Y-m-d-H-i'), $ext);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Konstanchuk_CustomOptionsExport::сustom_options_export');
    }
}
