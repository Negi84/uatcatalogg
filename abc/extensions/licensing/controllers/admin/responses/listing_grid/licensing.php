<?php

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\lib\AError;
use abc\core\lib\AException;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\extensions\licensing\models\admin\catalog\ModelCatalogLicensing;
use H;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use stdClass;

/**
 * Class ControllerResponsesListingGridLicensing
 *
 * @property ModelCatalogLicensing $model_catalog_licensing
 */
class ControllerResponsesListingGridLicensing extends AController
{

    public $error = [];

    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('licensing/licensing');
        $this->loadModel('catalog/licensing');
        $product_id = (int)$this->request->get['product_id'];

        //Prepare filter config
        $grid_filter_params = ['license_key', 'order_id', 'oo.name'];
        $filter_grid = new AFilter(['method' => 'post', 'grid_filter_params' => $grid_filter_params]);
        $data = $filter_grid->getFilterData();

        $total = $this->model_catalog_licensing->getTotalLicenses($product_id, $data);

        $response = new stdClass();
        $response->page = $filter_grid->getParam('page');
        $response->total = $filter_grid->calcTotalPages($total);
        $response->records = $total;
        $response->userdata = new stdClass();
        $response->userdata->classes = [];

        $results = $this->model_catalog_licensing->getLicenses($product_id, $data);

        $i = 0;
        foreach ($results as $result) {
            $eCode = $result['url']
                ? '<a href="Javascript:void(0)" onclick="copyToClipboard($(this).next().html()); '
                . 'success_alert(\'copied!\', true, $(this));">'
                . '<i class="fa fa-copy fa-2x"></i></a>&nbsp;<span>' . $result['url'] . '</span>'
                : $result['license_key'];

            $response->rows[$i]['raw_data'] = $result;
            $response->rows[$i]['id'] = $result['license_id'];
            if ($result['order_id'] > 0) {
                $href = '<a target="_new" href="'
                    . $this->html->getSecureURL('sale/order/details', '&order_id=' . $result['order_id'])
                    . '">#' . $result['order_id'] . '</a>';
                $response->userdata->classes[$result['license_id']] = 'disable-delete';
                $status = '-';
            } elseif ($result['expiry_date'] && strtotime($result['expiry_date']) < time()) {
                $href = '';
                $status = $this->html->buildCheckbox(
                    [
                        'name'  => 'status['.$result['license_id'].']',
                        'value' => false,
                        'style' => 'btn_switch',
                        'disabled'  => true
                    ]
                );
            } else {
                $href = '';
                $status = $this->html->buildCheckbox(
                    [
                        'name'  => 'status['.$result['license_id'].']',
                        'value' => $result['status'],
                        'style' => 'btn_switch',
                    ]
                );
            }

            $opt_value_name = $result['option_value_name'] ?: '';
            $opt_value_name = !$opt_value_name ? $result['option_name'] : $opt_value_name . ' - ' . $result['option_value_sku'];
            $opt_value_name = !$opt_value_name ? 'n/a' : $opt_value_name;

            $response->rows[$i]['cell'] = [
                'license_id'        => $result['license_id'],
                'option_value_name' => $opt_value_name,
                'license_key'       => $eCode,
                'expiry_date'       => ($result['expiry_date'] ? \H::dateISO2Display($result['expiry_date'], 'Y-m-d') : ''),
                'order_id'          => $href,
                'status'            => $status,
                'date_modified'     => H::dateISO2Display($result['date_modified'])
            ];
            $i++;
        }
        $this->data['response'] = $response;
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function update()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/licensing')) {
            $error = new AError('');
            $error->toJSONResponse(
                'NO_PERMISSIONS_402',
                [
                    'error_text'  => sprintf(
                                            $this->language->get('error_permission_modify'),
                                            'listing_grid/manufacturer'
                    ),
                    'reset_value' => true
                ]
            );
            return null;
        }

        $this->loadModel('catalog/licensing');
        switch ($this->request->post['oper']) {
            case 'del':
                $ids = explode(',', $this->request->post['id']);
                if (!empty($ids)) {
                    foreach ($ids as $id) {
                        $this->model_catalog_licensing->deleteLicense($id);
                    }
                }
                break;
            default:
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    /**
     * update only one field
     *
     * @return void
     * @throws AException
     * @throws ReflectionException|InvalidArgumentException
     */
    public function update_field()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!$this->user->canModify('listing_grid/licensing')) {
            $error = new AError('');
            $error->toJSONResponse('NO_PERMISSIONS_402',
                [
                    'error_text'  => sprintf($this->language->get('error_permission_modify'), 'listing_grid/licensing'),
                    'reset_value' => true,
                ]);
            return;
        }
        $this->loadLanguage('licensing/licensing');
        $this->loadModel('catalog/licensing');

        if (isset($this->request->get['id'])) {
            //request sent from edit form. ID in url
            foreach ($this->request->post as $field => $value) {
                $this->model_catalog_licensing->editLicense($this->request->get['id'], [$field => $value]);
            }
            return;
        }

        //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $field => $value) {
            foreach ($value as $k => $v) {
                $this->model_catalog_licensing->editLicense($k, [$field => $v]);
            }
        }

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

}
