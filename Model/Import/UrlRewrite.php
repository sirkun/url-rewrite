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
namespace Bss\UrlRewriteImportExport\Model\Import;

use Bss\UrlRewriteImportExport\Model\Import\UrlRewrite\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class UrlRewrite extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{

    const COL_ENTITY_TYPE = 'entity_type';

    const VALIDATOR_MAIN = 'validator';

    const DEFAULT_OPTION_VALUE_SEPARATOR = ';';

    /**
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_ENTITY_TYPE => 'Invalid Entity Type',
        ValidatorInterface::ERROR_REQUEST_PATH_NOT_EXIST => 'Request path does not exist',
        ValidatorInterface::ERROR_INVALID_REDIRECT_TYPE => 'Invalid Redirect Type',
        ValidatorInterface::ERROR_EMPTY_ENTITY_TYPE => 'Empty Entity Type',
        ValidatorInterface::ERROR_PRODUCT_ID_NOT_EXIST => 'Product Id does not exist',
        ValidatorInterface::ERROR_CATEGORY_ID_NOT_EXIST => 'Category Id does not exist',
        ValidatorInterface::ERROR_CMS_PAGE_ID_NOT_EXIST=> 'CMS Page Id not does exist',
        ValidatorInterface::ERROR_EMPTY_PRODUCT_ID => 'Empty Product Id',
        ValidatorInterface::ERROR_EMPTY_CATEGORY_ID => 'Empty Category Id',
        ValidatorInterface::ERROR_EMPTY_CMS_PAGE_ID => 'Empty CMS Page Id',
        ValidatorInterface::ERROR_EMPTY_TARGET_PATH => 'Entity type is custom, target_path must not be empty',
        ValidatorInterface::ERROR_EMPTY_REQUEST_PATH => 'Empty Request Path',
        ValidatorInterface::ERROR_EXISTED_REQUEST_PATH => 'Existed request_path and the system cannot update new request path',
        ValidatorInterface::ERROR_INVALID_STORE_ID => 'Invalid store id'
    ];

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $swatchAttributes = [];

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::COL_ENTITY_TYPE,
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

    /**
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * @var array
     */
    protected $_validators = [];

    /**
     * @var array
     */
    protected $_permanentAttributes = [self::COL_ENTITY_TYPE];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

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
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewrite;

    /**
     * @var \Bss\UrlRewriteImportExport\Model\ResourceModel\Import
     */
    protected $import;

    /**
     * @var UrlRewrite\Validator\UrlRewrite
     */
    protected $validator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * UrlRewrite constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
     * @param \Bss\UrlRewriteImportExport\Model\ResourceModel\Import $import
     * @param UrlRewrite\Validator\UrlRewrite $validator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite,
        \Bss\UrlRewriteImportExport\Model\ResourceModel\Import $import,
        \Bss\UrlRewriteImportExport\Model\Import\UrlRewrite\Validator\UrlRewrite $validator,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->dateTime = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource;
        $this->errorAggregator = $errorAggregator;
        $this->urlRewrite = $urlRewrite;
        $this->import = $import;
        $this->validator = $validator;
        $this->storeManager = $storeManager;

        $this->readAdapter = $this->_connection->getConnection('core_read');
        $this->writeAdapter = $this->_connection->getConnection('core_write');

        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
    }

    /**
     * @param $type
     * @return mixed
     */
    protected function _getValidator($type)
    {
        return $this->_validators[$type];
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'bss_url_rewrite';
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {

        if (!$this->validator->validateEntityType($rowData['entity_type'])) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_ENTITY_TYPE, $rowNum);
            return false;
        }

        if ($rowData['current_request_path']=="") {
            $this->addRowError(ValidatorInterface::ERROR_EMPTY_REQUEST_PATH, $rowNum);
            return false;
        }

        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!$this->validator->validateForDelete(
                $rowData['current_request_path'],
                $this->import->getExistRequestPath()
            )
            ) {
                $this->addRowError(ValidatorInterface::ERROR_REQUEST_PATH_NOT_EXIST, $rowNum);
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteUrlRewrite();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceUrlRewrite();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveUrlRewrite();
        }

        return true;
    }

    /**
     * @return void
     */
    protected function saveUrlRewrite()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validator->validateStoreId($this->getAllStoreIds(), $rowData['store_id']) === false) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_INVALID_STORE_ID,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    continue;
                }
                if ($this->getTargetPath($rowData, $rowNum) === false) {
                    continue;
                }
                $this->processData($rowData, $rowNum);
            }
        }
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function processData($rowData, $rowNum)
    {
        $redirectType = $this->validator->validateRedirectType($rowData['redirect_type'])?$rowData['redirect_type']:0;
        $model = $this->urlRewrite->create();
        $requestPath = $rowData['current_request_path'];

        if (in_array($rowData['current_request_path'], $this->import->getExistRequestPath())) {
            $existUrlId = $this->import->getExistUrlRewriteId($rowData['current_request_path'], $rowData['store_id']);
            if ($existUrlId > 0) {
                $model->load($existUrlId);
                if ($rowData['new_request_path'] != "") {
                    if ($this->import->getExistUrlRewriteId($rowData['new_request_path'], $rowData['store_id'])>0) {
                        $this->addRowError(
                            ValidatorInterface::ERROR_EXISTED_REQUEST_PATH,
                            $rowNum,
                            null,
                            null,
                            ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                        );
                        return false;
                    } else {
                        $requestPath = $rowData['new_request_path'];
                    }
                }
            }
        }

        if (empty($rowData['entity_type'])) {
            $rowData['entity_type'] = 'custom';
        }

        if ($rowData['entity_type'] == 'custom' && empty($rowData['target_path'])) {
            $this->addRowError(
                ValidatorInterface::ERROR_EMPTY_TARGET_PATH,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        $importData = [
            'entity_type' => $rowData['entity_type'],
            'entity_id' => $this->getEntityId($rowData),
            'request_path' => $requestPath,
            'target_path' => $this->getTargetPath($rowData, $rowNum),
            'redirect_type' => $redirectType,
            'description' => $rowData['description'],
            'store_id' => $rowData['store_id']
        ];

        $model->setEntityType($importData['entity_type'])
            ->setEntityId($importData['entity_id'])
            ->setRequestPath($importData['request_path'])
            ->setTargetPath($importData['target_path'])
            ->setRedirectType($redirectType)
            ->setStoreId($importData['store_id'])
            ->setDescription($importData['description']);
        $model->save();
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool|string
     */
    protected function getTargetPath($rowData, $rowNum)
    {
        if ($rowData['entity_type']=='cms-page') {
            return $this->getCmsPath($rowData, $rowNum);
        }

        if ($rowData['entity_type']=='product') {
            return $this->getProductPath($rowData, $rowNum);
        }

        if ($rowData['entity_type']=='category') {
            return $this->getCategoryPath($rowData, $rowNum);
        }

        return $rowData['target_path'];
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool|string
     */
    protected function getCmsPath($rowData, $rowNum)
    {
        $entityId = $rowData['cms_page_id'];
        if ($entityId=="") {
            $this->addRowError(
                ValidatorInterface::ERROR_EMPTY_CMS_PAGE_ID,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        if (!in_array($entityId, $this->import->getExistCmsPageIds())) {
            $this->addRowError(
                ValidatorInterface::ERROR_CMS_PAGE_ID_NOT_EXIST,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        return "cms/page/view/page_id/".$entityId;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool|string
     */
    protected function getProductPath($rowData, $rowNum)
    {
        $entityId = $rowData['product_id'];
        if ($entityId=="") {
            $this->addRowError(
                ValidatorInterface::ERROR_EMPTY_PRODUCT_ID,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        if (!in_array($entityId, $this->import->getExistProductIds())) {
            $this->addRowError(
                ValidatorInterface::ERROR_PRODUCT_ID_NOT_EXIST,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        if (!in_array($rowData['category_id'], $this->import->getExistCategoryIds()) &&
            $rowData['category_id']!=""
        ) {
            $this->addRowError(
                ValidatorInterface::ERROR_CATEGORY_ID_NOT_EXIST,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        if ($rowData['category_id']=="") {
            return "catalog/product/view/id/".$entityId;
        }

        return "catalog/product/view/id/".$entityId."/category/".$rowData['category_id'];
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @return bool|string
     */
    protected function getCategoryPath($rowData, $rowNum)
    {
        $entityId = $rowData['category_id'];
        if ($entityId=="") {
            $this->addRowError(
                ValidatorInterface::ERROR_EMPTY_CATEGORY_ID,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        if (!in_array($entityId, $this->import->getExistCategoryIds())) {
            $this->addRowError(
                ValidatorInterface::ERROR_CATEGORY_ID_NOT_EXIST,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
            return false;
        }

        return "catalog/category/view/id/".$entityId;
    }

    /**
     * @param array $rowData
     * @return int|null
     */
    protected function getEntityId($rowData)
    {
        $entityId = null;
        switch ($rowData['entity_type']) {
            case 'product':
                $entityId = $rowData['product_id'];
                break;
            case 'category':
                $entityId = $rowData['category_id'];
                break;
            case 'cms-page':
                $entityId = $rowData['cms_page_id'];
                break;
            case 'custom':
                $entityId = 0;
                break;
        }
        return $entityId;
    }

    /**
     * @return void
     */
    protected function replaceUrlRewrite()
    {
        $this->deleteForReplace();
        $this->saveUrlRewrite();
    }

    /**
     * @return $this
     */
    protected function deleteUrlRewrite()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
                $urlRewrtie = $this->urlRewrite->create()->load($rowData['current_request_path'], 'request_path');
                $urlRewrtie->delete();
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteForReplace()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->validator->validateForDelete(
                    $rowData['current_request_path'],
                    $this->import->getExistRequestPath()
                )
                ) {
                    $this->addRowError(
                        ValidatorInterface::ERROR_REQUEST_PATH_NOT_EXIST,
                        $rowNum,
                        null,
                        null,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    );
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
                $urlRewrtie = $this->urlRewrite->create()->load($rowData['current_request_path'], 'request_path');
                $urlRewrtie->delete();
                $this->processData($rowData, $rowNum);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getAllStoreIds()
    {
        $storeIds = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeIds[] = $store->getStoreId();
        }
        return $storeIds;
    }

    /**
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }
}
