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

class Import
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var array
     */
    protected $existRequestPath = [];

    /**
     * @var array
     */
    protected $existProductIds = [];

    /**
     * @var array
     */
    protected $existCategoryIds = [];

    /**
     * @var array
     */
    protected $existCmsPageIds = [];

    /**
     * Import constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Request\Http $request,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->resource = $resource;
        $this->readAdapter = $this->resource->getConnection('core_read');
        $this->writeAdapter = $this->resource->getConnection('core_write');
        $this->request = $request;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    /**
     * @param $entity
     * @return bool|mixed
     */
    protected function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resource->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * @return array|bool
     */
    public function getExistRequestPath()
    {
        if (empty($this->existRequestPath)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        [$this->getTableName('url_rewrite')],
                        ['request_path']
                    );

                $result = $this->readAdapter->query($select);
                foreach ($result as $value) {
                    $this->existRequestPath[] = $value['request_path'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->existRequestPath;
    }

    /**
     * @return array|bool
     */
    public function getExistProductIds()
    {
        if (empty($this->existProductIds)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        [$this->getTableName('catalog_product_entity')],
                        ['entity_id']
                    );

                $result = $this->readAdapter->query($select);
                foreach ($result as $value) {
                    $this->existProductIds[] = $value['entity_id'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->existProductIds;
    }

    /**
     * @return array|bool
     */
    public function getExistCategoryIds()
    {
        if (empty($this->existCategoryIds)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        [$this->getTableName('catalog_category_entity')],
                        ['entity_id']
                    );

                $result = $this->readAdapter->query($select);
                foreach ($result as $value) {
                    $this->existCategoryIds[] = $value['entity_id'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->existCategoryIds;
    }

    /**
     * @return array|bool
     */
    public function getExistCmsPageIds()
    {
        if (empty($this->existCmsPageIds)) {
            try {
                $select = $this->readAdapter->select()
                    ->from(
                        [$this->getTableName('cms_page')],
                        ['page_id']
                    );

                $result = $this->readAdapter->query($select);
                foreach ($result as $value) {
                    $this->existCmsPageIds[] = $value['page_id'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->existCmsPageIds;
    }

    /**
     * @param $requestPath
     * @return int
     */
    public function getExistUrlRewriteId($requestPath, $storeId)
    {
        $select = $this->readAdapter->select()
            ->from(
                $this->getTableName('url_rewrite'),
                [
                    'url_rewrite_id'
                ]
            )->where('request_path = :request_path')
            ->where('store_id = :store_id')
            ->limit(1);
        $bind = [
            ':request_path' => $requestPath,
            ':store_id' => $storeId
        ];
        $urlRewriteId = (int) $this->readAdapter->fetchOne($select, $bind);
        return $urlRewriteId;
    }
}
