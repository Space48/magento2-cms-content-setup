<?php


namespace Space48\CmsContentSetup\Model\Setup;


use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Space48\CmsContentSetup\Model\CmsContentSetup;

class Blocks
{
    const FOLDER_CMS_BLOCK = 'blocks';

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var BlockResource
     */
    private $blockResource;

    /**
     * @var CmsContentSetup
     */
    private $cmsContentSetup;

    public function __construct(
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        BlockResource $blockResource,
        CmsContentSetup $cmsContentSetup
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->blockResource = $blockResource;
        $this->cmsContentSetup = $cmsContentSetup;
    }

    public function install(array $blocks)
    {
        foreach ($blocks as $blockData) {
            $blockData = $this->cmsContentSetup->prepareData($blockData, self::FOLDER_CMS_BLOCK);
            /** @var Block $block */
            $block = $this->blockFactory->create();

            $block->setStoreId($blockData['store_id']);
            $block->setIdentifier($blockData['identifier']);
            if ($this->blockResource->getIsUniqueBlockToStores($block)) {
                $block->setData($blockData);
                $this->blockRepository->save($block);
            }
        }
    }

}
