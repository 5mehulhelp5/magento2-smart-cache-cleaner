<?php
/**
 * Copyright (c) 2024. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SmartCacheCleaner\Controller\Cache;

use Magento\Backend\Controller\Adminhtml\Cache;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class SmartClean extends Cache
{
    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        $response = ['error' => true, 'message' => __('An error occurred while refreshing cache.')];

        try {
            $cacheTypes = $this->getInvalidatedCacheTypes();

            if (empty($cacheTypes)) {
                $response['message'] = __('Nothing to flush as no cache is in an invalid state.');
                $this->addFlashMessage((string)$response['message'], 'notice');
            } else {
                $this->refreshCacheTypes($cacheTypes);
                $response['error'] = false;
                $response['message'] = __('%1 cache type(s) refreshed.', implode(', ', $cacheTypes));
                $this->addFlashMessage((string)$response['message']);
            }
        } catch (LocalizedException $e) {
            $response['message'] = $e->getMessage();
            $this->addFlashMessage($response['message'], 'error');
        } catch (\Exception|\Throwable $e) {
            $response['message'] = __('An error occurred while refreshing cache.');
            $this->messageManager->addExceptionMessage($e, $response['message']);
        }

        return $this->processResponse($response);
    }

    /**
     * Retrieve a list of invalidated cache types.
     *
     * @return array
     */
    private function getInvalidatedCacheTypes(): array
    {
        $invalidatedTypes = [];

        foreach ($this->_cacheTypeList->getInvalidated() as $cacheType) {
            $invalidatedTypes[$cacheType->getId()] = $cacheType->getCacheType();
        }

        return $invalidatedTypes;
    }

    /**
     * Refresh the specified cache types.
     *
     * @param array $cacheTypes
     * @throws LocalizedException
     */
    private function refreshCacheTypes(array $cacheTypes): void
    {
        $typeIds = array_keys($cacheTypes);
        $this->_validateTypes($typeIds);

        foreach ($typeIds as $typeId) {
            $this->_cacheTypeList->cleanType($typeId);
        }
    }

    /**
     * Add a flash message to the session.
     *
     * @param string $message
     * @param string $type
     */
    private function addFlashMessage(string $message, string $type = 'success'): void
    {
        if (!$this->getRequest()->isAjax()) {
            switch ($type) {
                case 'error':
                    $this->messageManager->addErrorMessage($message);
                    break;
                case 'notice':
                    $this->messageManager->addNoticeMessage($message);
                    break;
                default:
                    $this->messageManager->addSuccessMessage($message);
            }
        }
    }

    /**
     * Process the response based on the request type.
     *
     * @param array $response
     * @return ResultInterface
     */
    private function processResponse(array $response): ResultInterface
    {
        if ($this->getRequest()->isAjax()) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
