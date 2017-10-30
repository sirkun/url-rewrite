<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *
 * MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\UrlRewriteImportExport\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class Export
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resouceConnection;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Export constructor.
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
    }

    /**
     * @param array $requestData
     * @return \Zend_Db_Statement_Interface
     */
    public function getUrlRewrites($requestData)
    {
        $select = $this->readAdapter->select()
            ->from(
                ['main_table' => $this->getTableName('url_rewrite')],
                ['*']
            );
        switch ($requestData['export-by']) {
            case 'entity-type':
                $urlRewrite = $this->filterByEntityType($select, $requestData);
                break;
            case 'store-id':
                $urlRewrite = $this->filterByStoreId($select, $requestData);
                break;
            case 'redirect-type':
                $urlRewrite = $this->filterByRedirectType($select, $requestData);
                break;
            default:
                $urlRewrite = $this->readAdapter->query($select);
        }

        return $urlRewrite;
    }

    /**
     * @param $select
     * @param array $requestData
     * @return \Zend_Db_Statement_Interface
     */
    protected function filterByStoreId($select, $requestData)
    {
        if ($requestData['store-id']=='all') {
            return $this->readAdapter->query($select);
        } else {
            $select->where(
                "main_table.store_id = :store_id"
            );
            $bind = [
                ':store_id' => $requestData['store-id']
            ];
            return $this->readAdapter->query($select, $bind);
        }
    }

    /**
     * @param $select
     * @param $requestData
     * @return \Zend_Db_Statement_Interface
     */
    protected function filterByEntityType($select, $requestData)
    {
        if ($requestData['entity-type']=='all') {
            return $this->readAdapter->query($select);
        } else {
            $select->where(
                "main_table.entity_type = :entity_type"
            );
            $bind = [
                ':entity_type' => $requestData['entity-type']
            ];
            return $this->readAdapter->query($select, $bind);
        }
    }

    /**
     * @param $select
     * @param $requestData
     * @return \Zend_Db_Statement_Interface
     */
    protected function filterByRedirectType($select, $requestData)
    {
        if ($requestData['redirect-type']=='all') {
            return $this->readAdapter->query($select);
        } else {
            $select->where(
                "main_table.redirect_type = :redirect_type"
            );
            $bind = [
                ':redirect_type' => $requestData['redirect-type']
            ];
            return $this->readAdapter->query($select, $bind);
        }
    }

    /**
     * @param string $entity
     * @return bool|mixed
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * @param $dateTime
     * @return string
     */
    public function formatDate($dateTime)
    {
        $dateTimeAsTimeZone = $this->timezone
            ->date($dateTime)
            ->format('YmdHis');
        return $dateTimeAsTimeZone;
    }

    /**
     * @param object $urlRewrites
     * @return array
     */
    public function getExportData($urlRewrites)
    {
        $data[0] = [
            'entity_type',
            'product_id',
            'category_id',
            'cms_page_id',
            'store_id',
            'current_request_path',
            'new_request_path',
            'target_path',
            'redirect_type',
            'description'
        ];
        foreach ($urlRewrites as $urlRewrite) {
            $row=null;
            $row[0] = $urlRewrite['entity_type'];

            switch ($urlRewrite['entity_type']) {
                case 'product':
                    $row[1] = $urlRewrite['entity_id'];
                    $row[2] = $this->getCategoryId($urlRewrite['target_path']);
                    $row[3] = "";
                    break;
                case 'category':
                    $row[1] = "";
                    $row[2] = $urlRewrite['entity_id'];
                    $row[3] = "";
                    break;
                case 'cms-page':
                    $row[1] = "";
                    $row[2] = "";
                    $row[3] = $urlRewrite['entity_id'];
                    break;
				case 'custom':
                    $row[1] = "";
                    $row[2] = "";
                    $row[3] = "";
                    break;	
            }
            $row[4] = $urlRewrite['store_id'];
            $row[5] = $urlRewrite['request_path'];
            $row[6] = "";
            $row[7] = $urlRewrite['target_path'];
            $row[8] = $urlRewrite['redirect_type'];
            $row[9] = $urlRewrite['description'];
            $data[]=$row;
        }
        return $data;
    }

    /**
     * @param string $targetPath
     * @return string
     */
    protected function getCategoryId($targetPath)
    {
        if (strpos($targetPath, 'category')!==false) {
            $arr = explode('/', $targetPath);
            return $arr['6'];
        }
        return "";
    }
}
