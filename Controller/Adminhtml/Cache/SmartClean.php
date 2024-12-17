<?php
/**
 * Copyright (c) 2024. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SmartCacheCleaner\Controller\Adminhtml\Cache;

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
                $message = __('Nothing to flush as no cache is in an invalid state.');
                $response['message'] = $message;
                $this->addFlashMessage((string)$message, 'notice');
            } else {
                $this->refreshCacheTypes($cacheTypes);
                $message = __('%1 cache type(s) refreshed.', implode(', ', $cacheTypes));
                $response['error'] = false;
                $response['message'] = $message;
                $this->addFlashMessage((string)$message);
            }
        } catch (LocalizedException $e) {
            $message = $e->getMessage();
            $response['message'] = $message;
            $this->addFlashMessage($message, 'error');
        } catch (\Exception|\Throwable $e) {
            $message = __('An error occurred while refreshing cache.');
            $response['message'] = $message;
            $this->messageManager->addExceptionMessage($e, $message);
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
        if ($this->getRequest()->isAjax() === false) {
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
