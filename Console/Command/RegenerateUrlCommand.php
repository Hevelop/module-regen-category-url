<?php
namespace Hevelop\RegenCategoryUrl\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class RegenerateUrlCommand extends Command
{
    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $collection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var State
     */
    protected $state;

    public function __construct(
        State $state,
        Collection $collection,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository
    )
    {
        $this->state = $state;
        $this->collection = $collection;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('hevelop:regencaturl')
            ->setDescription('Regenerate url for given categorys')
            ->addArgument(
                'cids',
                InputArgument::IS_ARRAY,
                'Categories to regenerate'
            );
        return parent::configure();
    }


    /**
     * @param InputInterface $inp
     * @param OutputInterface $out
     * @return int|null|void
     */
    public function execute(InputInterface $inp, OutputInterface $out)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->storeManager->setCurrentStore(0);

        $cids = $inp->getArgument('cids');
        if (!empty($cids)) {
            $this->collection->addIdFilter($cids);
        }
        $this->collection->addAttributeToSelect(['url_path', 'url_key']);
        $list = $this->collection->load();

        foreach ($list as $category) {
            $category->setData('url_path', null);
            $category->save();
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $category->getId(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0
            ]);
            try {
                foreach ($category->getStoreIds() as $storeId) {
                    $category->setStoreId($storeId);
                    $this->urlPersist->replace(
                        $this->categoryUrlRewriteGenerator->generate($category)
                    );
                }

                $out->writeln('<info>Regenerated url keys for category ' . $category->getId() . '</info>');
            } catch (\Exception $e) {
                $out->writeln('<error>Duplicated url for ' . $category->getId() . '</error>');
            }
        }
    }
}
