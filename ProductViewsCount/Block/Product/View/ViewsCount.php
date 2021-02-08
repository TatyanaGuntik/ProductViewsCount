<?php

namespace Mobecls\ProductViewsCount\Block\Product\View;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory;
use Mobecls\ProductViewsCount\Helper\GetPeriodCountViewProduct;
use mysql_xdevapi\Exception;

class ViewsCount extends \Magento\Framework\View\Element\Template
{
    protected $productsFactory;

    protected $request;

    protected $getPeriodCountViewProduct;

    protected $storeManager;

    public function __construct(
        Context $context,
        CollectionFactory $productsFactory,
        RequestInterface $request,
        GetPeriodCountViewProduct $getPeriodCountViewProduct,
        array $data = []
    )
    {
        $this->productsFactory = $productsFactory;
        $this->request = $request;
        $this->getPeriodCountViewProduct = $getPeriodCountViewProduct;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    public function getPeriod()
    {
        return $this->getPeriodCountViewProduct->getGeneralConfig('period');
    }

    public function getProductView()
    {
        $period = $this->getPeriod();

        $productId = $this->request->getParam('id', null);

        $currentStoreId = $this->storeManager->getStore()->getId();

        $today = time();
        $last = $today - (60 * 60 * 24 * $period);
        $from = date("Y-m-d", $last);
        $to = date("Y-m-d", $today);

        $collection = $this->productsFactory->create()
            ->addAttributeToSelect(
                '*'
            )->addViewsCount($from, $to)
            ->setStoreId(
                $currentStoreId
            )->addStoreFilter(
                $currentStoreId
            );
        $items = $collection->getItems();

        try {
            $viewCount = $items[$productId];
            $viewCount = $items[$productId]->getData('views');
            return $viewCount;
        } catch (\Exception $exception) {
           return null;
        }
    }
}