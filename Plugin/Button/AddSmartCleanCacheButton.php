<?php
/**
 * Copyright (c) 2024. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SmartCacheCleaner\Plugin\Button;

use Magento\Backend\Block\Cache;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\View\Element\AbstractBlock;

class AddSmartCleanCacheButton
{


    /**
     * Add Smart Cache Flush button.
     *
     * @param ToolbarInterface $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePushButtons(
        ToolbarInterface $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ): void {
        if ($context instanceof Cache) {
            $url = $context->getUrl('adminhtml/cache/smartClean');

            $message = __('Are you sure you want to flush invalidated cache(s)?');
            $buttonList->add(
                'flush_invalidated_cache',
                [
                    'label' => __('Flush Invalidated Cache'),
                    'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $url . '\')',
                    'class' => 'flush-cache-storage'
                ],
                0,
                -1
            );
        }
    }
}