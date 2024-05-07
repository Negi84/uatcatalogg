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
use abc\core\engine\AController;
use abc\core\lib\AJson;
use abc\models\catalog\Product;
use stdClass;

class ControllerResponsesListingGridReportViewed extends AController {
    public function main()
    {
        $page = (int)$this->request->post['page'] ?: 1;
        $limit = $this->request->post['rows'];
        $sort = $this->request->post['sidx'];
        $order = $this->request->post['sord'];

        $this->data['search_parameters'] = [
            'language_id' => $this->language->getContentLanguageID(),
            'start'       => ($page - 1) * $limit,
            'limit'       => $limit,
            'sort'        => $sort,
            'order'       => $order
        ];

	    //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->data['search_parameters']['filter']['only_viewed'] = true;

		$this->loadLanguage('report/viewed');

        $results = Product::getProducts($this->data['search_parameters']);
        //push result into public scope to get access from extensions
        $this->data['results'] = $results;
        /** @see QueryBuilder::get() */
        $total = $results::getFoundRowsCount();
        $total_pages = $total > 0 ? ceil($total / $limit) : 0;

        $totalViews = Product::sum('viewed');

        $response = new stdClass();
        $response->page = $page;
        $response->total = $total_pages;
        $response->records = $total;
        $response->userdata = (object)[''];
        $response->userdata->classes = [];

	    $i = 0;
		foreach ($results as $result) {

            if ($result->viewed) {
                $percent = number_format(round(($result->viewed / $totalViews) * 100, 2), 2);
            } else {
                $percent = '0';
            }

            $response->rows[$i]['id'] = $i;
            $response->rows[$i]['cell'] = [
                $result->product_id,
                $result->name,
                $result->model,
                $result->viewed,
                $percent . '%',
            ];
			$i++;
		}
	    $this->data['response'] = $response;

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
	}
}