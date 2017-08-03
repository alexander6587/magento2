<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filter\StripTags;

/**
 * Class \Magento\AdvancedSearch\Controller\Adminhtml\Search\System\Config\TestConnection
 *
 * @since 2.1.0
 */
class TestConnection extends Action
{
    /**
     * @var ClientResolver
     * @since 2.1.0
     */
    private $clientResolver;

    /**
     * @var JsonFactory
     * @since 2.1.0
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     * @since 2.1.0
     */
    private $tagFilter;

    /**
     * @param Context           $context
     * @param ClientResolver    $clientResolver
     * @param JsonFactory       $resultJsonFactory
     * @param StripTags         $tagFilter
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        ClientResolver $clientResolver,
        JsonFactory $resultJsonFactory,
        StripTags $tagFilter
    ) {
        parent::__construct($context);
        $this->clientResolver = $clientResolver;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
    }

    /**
     * Check for connection to server
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.1.0
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['engine'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Missing search engine parameter.')
                );
            }
            $response = $this->clientResolver->create($options['engine'], $options)->testConnection();
            if ($response) {
                $result['success'] = true;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $this->tagFilter->filter($message);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
