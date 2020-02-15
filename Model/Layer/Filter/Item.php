<?php

namespace Yu\Filter\Model\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{

    /**
     * @var \Magento\Framework\App\RequestInterface 
     */
    private $request;

    /**
     * Construct
     *
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\UrlInterface $url,
            \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
            \Magento\Framework\App\RequestInterface $request,
            array $data = []
    )
    {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->request = $request;
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        $paramName = $this->getFilter()->getRequestVar();
        $paramValue = $this->request->getParam($paramName, null);
        $paramValueArray = [];



        if (!empty($paramValue)) {
            $paramValueArray = explode('-', $paramValue);
            foreach ($paramValueArray as $_param)
            {
                
            }
        }

        $paramValueArray[] = $this->getValue();
        $paramNewValue = implode('-', $paramValueArray);

        $query = [
            $paramName                               => $paramNewValue,
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];
        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $paramName = $this->getFilter()->getRequestVar();
        $paramValue = $this->request->getParam($paramName, null);
        $paramValueArray = [];
        $paramNewValueArray = [];

        if (!empty($paramValue)) {
            $paramValueArray = explode('-', $paramValue);
            foreach ($paramValueArray as $_param)
            {
                if ($_param == $this->getValue()) {
                    continue;
                }
                $paramNewValueArray[] = $_param;
            }
        }

        $paramNewValue = implode('-', $paramNewValueArray);

        $query = [$paramName => $paramNewValue];

        if ($this->getFilter()->getRequestVar() === 'price') {
            $query = [$this->getFilter()->getRequestVar() => null];
        }

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;

        return $this->_url->getUrl('*/*/*', $params);
    }

}
