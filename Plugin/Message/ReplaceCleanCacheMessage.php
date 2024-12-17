<?php
/**
 * Copyright (c) 2024. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SmartCacheCleaner\Plugin\Message;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class ReplaceCleanCacheMessage
{
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        private readonly TypeListInterface $cacheTypeList
    ) {
    }

    public function aroundGetText(): string
    {
        $cacheTypes = implode(', ', $this->getCacheTypesForRefresh());
        $message = __('One or more of the Cache Types are invalidated: %1. ', $cacheTypes) . ' ';
        $url = $this->urlBuilder->getUrl('adminhtml/cache/smartClean');
        $message .= __('Please <a id="smart-cache-clean" href="%1">Click Here</a> to refresh cache types immediately.', $url);

        return $message;
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    private function getCacheTypesForRefresh(): array
    {
        $output = [];
        foreach ($this->cacheTypeList->getInvalidated() as $type) {
            $output[] = $type->getCacheType();
        }
        return $output;
    }
}