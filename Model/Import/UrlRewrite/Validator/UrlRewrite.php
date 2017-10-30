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
namespace Bss\UrlRewriteImportExport\Model\Import\UrlRewrite\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Bss\UrlRewriteImportExport\Model\Import\UrlRewrite\RowValidatorInterface;

class UrlRewrite extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var
     */
    protected $context;

    /**
     * @param $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function isValid($value)
    {
        parent::isValid($value);
    }

    /**
     * @param string $entityType
     * @return bool
     */
    public function validateEntityType($entityType)
    {
        $validEntityTypes = ['custom', 'category', 'product', 'cms-page'];
        if (!in_array($entityType, $validEntityTypes) && $entityType!="") {
            return false;
        }
        return true;
    }

    /**
     * @param string $requestPath
     * @param $existRequestPath
     * @return bool
     */
    public function validateForDelete($requestPath, $existRequestPath)
    {
        if (!in_array($requestPath, $existRequestPath)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $redirectType
     * @return bool
     */
    public function validateRedirectType($redirectType)
    {
        $validRedirectTypes = ['0', '301', '302'];
        if (!in_array($redirectType, $validRedirectTypes) || $redirectType=="") {
            return false;
        }
        return true;
    }

    /**
     * @param array $storeIds
     * @param int $storeId
     * @return bool
     */
    public function validateStoreId($storeIds, $storeId)
    {
        if (!in_array($storeId, $storeIds)) {
            return false;
        }
        return true;
    }
}
