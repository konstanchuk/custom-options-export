<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Konstanchuk\CustomOptionsExport\Model\Export as ExportModel;


class Export extends Command
{
    const FILE_NAME_OPTION = 'file';
    const TYPE_OPTION = 'type';

    const PRODUCTS_SKU = 'products';

    /** @var ExportModel  */
    protected $_export;

    public function __construct(ExportModel $export, $name = null)
    {
        parent::__construct($name);
        $this->_export = $export;
    }

    protected function configure()
    {
        $this->setName('customoptions:export')
            ->setDescription('Custom Options Export')
            ->setDefinition([
                new InputOption(
                    static::FILE_NAME_OPTION,
                    '-f',
                    InputOption::VALUE_REQUIRED,
                    'output file'
                ),
                new InputOption(
                    static::TYPE_OPTION,
                    '-t',
                    InputOption::VALUE_REQUIRED,
                    'export type ( 0 - xml, 1 - csv)'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getOption(static::FILE_NAME_OPTION);
        $type = $input->getOption(static::TYPE_OPTION);
        if (is_null($filename)) {
            throw new \InvalidArgumentException(sprintf('Option \'%s\' is missing.', static::FILE_NAME_OPTION));
        }
        if (file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File \'%s\' already exists', $filename));
        }
        if (is_null($type)) {
            throw new \InvalidArgumentException(sprintf('Option \'%s\' is missing. Use 0 for csv or 1 for xml format', static::TYPE_OPTION));
        }
        if ($type == 0) {
            $data = $this->_export->prepareCsv();
        } else if ($type == 1) {
            $data = $this->_export->prepareXml();
        } else {
            throw new \InvalidArgumentException(sprintf('Option \'%s\' is invalid. Use 0 for csv or 1 for xml format', static::TYPE_OPTION));
        }
        if (file_put_contents($filename, $data) === false) {
            throw new \Exception(sprintf('failed to write data to %s', $filename));
        }
        $output->writeln(sprintf('file was created. see %s', $filename));
    }
}