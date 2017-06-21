<?php

/**
 * Custom Options Export Extension for Magento 2
 *
 * @author     Volodymyr Konstanchuk http://konstanchuk.com
 * @copyright  Copyright (c) 2017 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace Konstanchuk\CustomOptionsExport\Model;

use Magento\Framework\App\ResourceConnection;


class Export
{
    const TYPE_XML = 1;
    const TYPE_CSV = 2;

    /** @var ResourceConnection $_resourceConnection */
    protected $_resourceConnection;

    protected $getOptionsSql;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->_resourceConnection = $resourceConnection;

        $connection = $this->getDbConnection();
        $tblCatalogProductEntity = $connection->getTableName('catalog_product_entity');
        $tblCatalogProductOption = $connection->getTableName('catalog_product_option');
        $tblCatalogProductOptionPrice = $connection->getTableName('catalog_product_option_price');
        $tblCatalogProductOptionTitle = $connection->getTableName('catalog_product_option_title');
        $tblCatalogProductOptionTypeValue = $connection->getTableName('catalog_product_option_type_value');
        $tblCatalogProductOptionTypePrice = $connection->getTableName('catalog_product_option_type_price');
        $tblCatalogProductOptionTypeTitle = $connection->getTableName('catalog_product_option_type_title');
        $this->getOptionsSql =<<<SQL
            SELECT
                cpe.sku as sku,
                cpo.type as type, cpo.is_require as is_require, cpo.sku as cpo_sku, cpo.max_characters as max_characters,
                    cpo.file_extension as file_extension, cpo.image_size_x as image_size_x, cpo.image_size_y as image_size_y,
                    cpo.sort_order as cpo_sort_order,
                cpotv.sku as cptv_sku, cpotv.sort_order as cpotv_sort_order,
                cpop.store_id as cpop_store_id, cpop.price as cpop_price, cpop.price_type as cpop_price_type,
                cpotp.store_id as cpotp_store_id, cpotp.price as cpotp_price, cpotp.price_type as cpotp_price_type,
                cpot.store_id as cpot_store_id, cpot.title as cpot_title,
                cpott.store_id as cpott_store_id, cpott.title as cpott_title
            FROM `$tblCatalogProductEntity` cpe
            JOIN `$tblCatalogProductOption` cpo ON cpe.entity_id = cpo.product_id
            LEFT JOIN `$tblCatalogProductOptionPrice` cpop ON cpo.option_id = cpop.option_id
            LEFT JOIN `$tblCatalogProductOptionTitle` cpot ON cpo.option_id = cpot.option_id
            LEFT JOIN `$tblCatalogProductOptionTypeValue` cpotv ON cpo.option_id = cpotv.option_id
            LEFT JOIN `$tblCatalogProductOptionTypePrice` cpotp ON cpotv.option_type_id = cpotp.option_type_id
            LEFT JOIN `$tblCatalogProductOptionTypeTitle` cpott ON cpotv.option_type_id = cpott.option_type_id
SQL;
    }

    protected function getDbConnection()
    {
        return $this->_resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    protected function getOptionRows(array $productIds = null)
    {
        $connection = $this->getDbConnection();
        if (is_null($productIds)) {
            return $connection->fetchAll($this->getOptionsSql);
        }
        $ids = implode(',', array_map('intval', $productIds));
        $where = ' WHERE cpe.entity_id IN (' . $ids . ')';
        return $connection->fetchAll($this->getOptionsSql . $where);
    }

    protected function groupOptions(array $optionRows)
    {
        $options = [];
        foreach($optionRows as $row) {
            if (isset($options[$row['sku']])) {
                if (isset($options[$row['sku']][$row['type']])) {
                    $o = $options[$row['sku']][$row['type']];
                } else {
                    $o = array(
                        'is_require' => (int)$row['is_require'],
                        'title' => $row['cpot_title'],
                        'sort_order' => (int)$row['cpo_sort_order'],
                    );
                }
            } else {
                $o = array(
                    'is_require' => (int)$row['is_require'],
                    'title' => $row['cpot_title'],
                    'sort_order' => (int)$row['cpo_sort_order'],
                );
            }
            switch ($row['type']) {
                case 'field':
                case 'area':
                    $o['params'][] = array(
                        'sku' => $row['cpo_sku'],
                        'max_characters' => (int)$row['max_characters'],
                        'price' => (float)$row['cpop_price'],
                        'price_type' => $row['cpop_price_type'],
                    );
                    break;
                case 'file':
                    $o['params'][] = array(
                        'sku' => $row['cpo_sku'],
                        'price' => (float)$row['cpop_price'],
                        'price_type' => $row['cpop_price_type'],
                        'file_extension' => $row['file_extension'],
                        'image_size_x' => (int)$row['image_size_x'],
                        'image_size_y' => (int)$row['image_size_y'],
                    );
                    break;
                case 'drop_down':
                case 'radio':
                case 'checkbox':
                case 'multiple':
                    $o['params'][] = array(
                        'sku' => $row['cptv_sku'],
                        'sort_order' => (int)$row['cpotv_sort_order'],
                        'title' => $row['cpott_title'],
                        'price' => (float)$row['cpotp_price'],
                        'price_type' => $row['cpotp_price_type'],
                    );
                    break;
                case 'date':
                case 'date_time':
                case 'time':
                    $o['params'][] = array(
                        'sku' => $row['cpo_sku'],
                        'price' => (float)$row['cpop_price'],
                        'price_type' => $row['cpop_price_type'],
                    );
                    break;
                default:
                    continue;
                    break;
            }
            $options[$row['sku']][$row['type']] = $o;
        }
        return $options;
    }

    public function getOptions(array $productIds = null)
    {
        return $this->groupOptions($this->getOptionRows($productIds));
    }

    public function prepareXml(array $productIds = null)
    {
        $products = $this->groupOptions($this->getOptionRows($productIds));
        $xml = new \SimpleXMLElement('<products></products>');
        foreach($products as $sku => $options) {
            $product = $xml->addChild('product');
            $product->addAttribute('sku', $sku);
            foreach($options as $typeName => $values) {
                $type = $product->addChild('option');
                $type->addAttribute('type', $typeName);
                $type->addChild('title', $values['title']);
                $type->addChild('is_require', $values['is_require']);
                $type->addChild('sort_order', $values['sort_order']);
                $params = $type->addChild('params');
                foreach($values['params'] as $p) {
                    $item = $params->addChild('item');
                    $price = $item->addChild('price', $p['price']);
                    $price->addAttribute('type', $p['price_type']);
                    unset($p['price']);
                    unset($p['price_type']);
                    foreach($p as $key => $value) {
                        $item->addChild($key, $value);
                    }
                }
            }
        }
        return $xml->asXML();
    }

    public function prepareCsv(array $productIds = null)
    {
        $optionRows = $this->getOptionRows($productIds);
        if (count($productIds) > 0) {
            $columns = count($optionRows) > 0 ? array_keys($optionRows[0]) : [];
        } else {
            $columns = $this->getColumnsNames($this->getOptionsSql);
        }
        return $this->exportCsv($columns, $optionRows);
    }

    protected function getColumnsNames($sql)
    {
        $sql = preg_replace('/limit (.*)/sim', ' limit 0', $sql);
        $result = $this->getDbConnection()->query($sql);
        $columns = [];
        for ($i = 0; $i < $result->columnCount(); ++$i) {
            $column = $result->getColumnMeta($i);
            $columns[] = $column['name'];
        }
        return $columns;
    }

    protected function exportCsv(array $columns, array $data, $delimiter = ',')
    {
        $csv = [implode($delimiter, array_map(function ($item) {
            return '"' . str_replace('"','\"', $item) . '"';
        }, $columns))];
        foreach ($data as $item) {
            $cols = [];
            foreach ($columns as $column) {
                $value = $item[$column];
                if (is_null($value)) {
                    $cols[] = '';
                } else if (is_integer($value)) {
                    $cols[] = $value;
                } else {
                    $cols[] = '"' . str_replace('"','\"', $value) . '"';
                }
            }
            $csv[] = implode($delimiter, $cols);
        }
        return implode(PHP_EOL, $csv);
    }

}