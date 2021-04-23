<?php

namespace Space48\CmsContentSetup\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File as FileReader;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Setup\SampleData\Context;

class CmsContentSetup
{
    /**
     * Static folder name
     */
    const FOLDER_FIXTURES = 'fixtures';

    /**
     * File Reader instance
     *
     * @var FileReader
     */
    private $fileReader;

    /**
     * Dir Reader instance
     *
     * @var DirReader
     */
    private $dirReader;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixturesManager;

    /**
     * @param FileReader $fileReader
     * @param DirReader $dirReader
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FileReader $fileReader,
        DirReader $dirReader,
        StoreManagerInterface $storeManager,
        Context $context
    ) {

        $this->fileReader = $fileReader;
        $this->dirReader = $dirReader;
        $this->storeManager = $storeManager;
        $this->fixturesManager = $context->getFixtureManager();
    }

    /**
     * @param array $data
     * @param string $folder
     * @return array
     * @throws FileSystemException
     */
    public function prepareData(array $data, string $folder): array
    {
        $data['content'] = $this->getContent($data['file']);
        unset($data['file']);

        $data['store_id'] = $this->getStoreIds($data['stores'] ?? []);
        unset($data['stores']);

        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }

    /**
     * @param string $fileId
     * @param string $folder
     * @return string
     * @throws FileSystemException
     */
    private function getContent(string $fileId): string
    {
        list($moduleName, $fileName) = \Magento\Framework\View\Asset\Repository::extractModule(
            \Magento\Framework\Setup\SampleData\FixtureManager::normalizePath($fileId)
        );

        $filePath = $this->dirReader->getModuleDir(false, $moduleName) .
            DIRECTORY_SEPARATOR .
            self::FOLDER_FIXTURES .
            DIRECTORY_SEPARATOR .
            $fileName;
        if (!$this->fileReader->fileExists($filePath)) {
            throw new FileSystemException(new Phrase('Can not load content file: %1', [$filePath]));
        }

        return $this->fileReader->read($filePath);
    }

    /**
     * @param $stores
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreIds($stores): array
    {
        if (!is_array($stores)) {
            $stores = [$stores];
        }
        $ids = array_map(function ($store) { return $this->storeManager->getStore($store)->getStoreId(); }, $stores);

        return $ids ? array_filter($ids): [Store::DEFAULT_STORE_ID];
    }
}
