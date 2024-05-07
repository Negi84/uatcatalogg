<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * https://www.abantecart.com
 *
 * Copyright (c) 2011-2023  Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <https://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to https://www.abantecart.com for more information.
 */

namespace abc\modules\traits;

use abc\core\engine\AHtml;
use abc\core\lib\ADocument;
use abc\core\lib\ALanguageManager;

/**
 * @property ADocument $document
 * @property AHtml $html
 * @property ALanguageManager $language
 */
trait EditProductTrait
{
    public function addTabs(string $active = 'general')
    {
        $this->data['active'] = $active;
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', [$this->data]);
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);
    }

    public function addSummary()
    {
        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');
    }

    public function setBreadCrumbs(array $productInfo, ?string $currentUrl = '', ?string $currentText = '')
    {
        $this->document->resetBreadcrumbs();
        $this->document->initBreadcrumb(
            [
                'href' => $this->html->getSecureURL('index/home'),
                'text' => $this->language->get('text_home'),
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product'),
                'text' => $this->language->get('heading_title', 'catalog/product'),
            ]
        );
        if ($productInfo['product_id']) {
            $this->document->addBreadcrumb(
                [
                    'href'    => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $productInfo['product_id']),
                    'text'    => $productInfo['name'],
                    'current' => (!$currentUrl)
                ]
            );
        }
        if ($currentUrl) {
            $this->document->addBreadcrumb(
                [
                    'href'    => $currentUrl,
                    'text'    => $currentText,
                    'current' => true
                ]
            );
        }
    }
}