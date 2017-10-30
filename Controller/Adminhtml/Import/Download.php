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
namespace Bss\UrlRewriteImportExport\Controller\Adminhtml\Import;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\ImportExport\Controller\Adminhtml\Import\Download
{
    const URL_REWRITE_SAMPLE_FILE = 'Bss_UrlRewriteImportExport';

    /**
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        if ($this->getRequest()->getParam('filename') == 'bss_url_rewrite') {
            $moduleDir = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                self::URL_REWRITE_SAMPLE_FILE
            );
        } else {
            $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
        }
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            $this->messageManager->addError(__('There is no sample file for this entity.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/import');
            return $resultRedirect;
        }

        $fileSize = isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($directoryRead->readFile($filePath));
        return $resultRaw;
    }
}
