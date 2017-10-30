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
namespace Bss\UrlRewriteImportExport\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;

class Export extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\UrlRewriteImportExport\Model\ResourceModel\Export
     */
    protected $export;

    /**
     * @var string
     */
    protected $varDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * Export constructor.
     * @param Context $context
     * @param \Bss\UrlRewriteImportExport\Model\ResourceModel\Export $export
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        \Bss\UrlRewriteImportExport\Model\ResourceModel\Export $export,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->export=$export;
        $this->filesystem = $filesystem;
        $this->datetime = $datetime;
        $this->io=$io;
        $this->csv=$csv;
        $this->fileFactory=$fileFactory;
        $this->resultRawFactory=$resultRawFactory;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getParams();
        $this->varDirectory=$this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $dir=$this->varDirectory->getAbsolutePath('bss/export');
        $this->io->mkdir($dir, 0775);
        $currentDate = $this->export->formatDate($this->datetime->date());
        $outputFile = $dir."/Url_Rewrite_" . $currentDate . ".csv";
        $fileName="Url_Rewrite_" . $currentDate . ".csv";
        $urlRewrites = $this->export->getUrlRewrites($requestData);
        $data=$this->export->getExportData($urlRewrites);
        try {
            $this->csv->saveData($outputFile, $data);
            $this->fileFactory->create(
                $fileName,
                [
                    'type'  => "filename",
                    'value' => "bss/export/".$fileName,
                    'rm'    => true,
                ],
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'text/csv',
                null
            );
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw;
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }
}
