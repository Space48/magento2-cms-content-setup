<?php


namespace Space48\CmsContentSetup\Model\Setup;


use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Space48\CmsContentSetup\Model\CmsContentSetup;

class Pages
{
    const FOLDER_CMS_PAGE = 'pages';

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @var CmsContentSetup
     */
    private $cmsContentSetup;

    public function __construct(
        PageFactory $pageFactory,
        PageRepository $pageRepository,
        PageResource $pageResource,
        CmsContentSetup $cmsContentSetup
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->pageResource = $pageResource;
        $this->cmsContentSetup = $cmsContentSetup;
    }

    public function install(array $pages)
    {
        foreach ($pages as $pageData) {
            $pageData = $this->cmsContentSetup->prepareData($pageData, self::FOLDER_CMS_PAGE);
            $pageExists = false;
            foreach ($pageData['store_id'] as $storeId) {
                /** @var Page $page */
                $page = $this->pageFactory->create();
                $page->setStoreId($storeId);
                $this->pageResource->load($page, $pageData['identifier']);
                if ($page->getId()) {
                    $pageExists = true;
                    break;
                }
            }

            if (!$pageExists) {
                $page->setData($pageData);
                $this->pageRepository->save($page);
            }
        }
    }

}
