<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2023 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>
  
 UPGRADE NOTE: 
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.  
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\models\catalog\Product;
use abc\modules\traits\EditProductTrait;

if (!class_exists('abc\core\ABC') || !ABC::env('IS_ADMIN')) {
	header('Location: static_pages/?forbidden='.basename(__FILE__));
}

class ControllerPagesCatalogProductExtensions extends AController
{
    use EditProductTrait;

    private $error = [];

	public function main(){

		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);

        $productId = (int)$this->request->get['product_id'];

		$this->loadLanguage('catalog/product');
		$this->document->setTitle($this->language->get('heading_title'));

        $this->data['product_info'] = $productInfo = Product::getProductInfo($productId);
        if (!$productInfo) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->document->setTitle($productInfo['name'] . ' ' . $this->language->get('tab_extensions'));

		$this->view->assign('error_warning', $this->error['warning']);
		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

        $this->setBreadCrumbs(
            $productInfo,
            $this->html->getSecureURL('catalog/product_files', '&product_id=' . $productId),
            $this->language->get('tab_extensions')
        );

        $this->addTabs('extensions');
        $this->addSummary();

		$this->view->assign('help_url', $this->gen_help_url('product_extensions'));
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/catalog/product_extensions.tpl');

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
	}
}