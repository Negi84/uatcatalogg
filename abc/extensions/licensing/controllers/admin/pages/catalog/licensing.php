<?php

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\extensions\licensing\models\admin\catalog\ModelCatalogLicensing;
use abc\models\catalog\Product;
use abc\models\catalog\ProductOption;

/**
 * Class ControllerPagesCatalogLicensing
 *
 * @property ModelCatalogLicensing $model_catalog_licensing
 */
class ControllerPagesCatalogLicensing extends AController
{
    public $error = [];
    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $product_id = (int)$this->request->get['product_id'];
        if (!$product_id || !Product::find($product_id)) {
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }

        $language_id = $this->language->getContentLanguageID();
        $this->loadLanguage('licensing/licensing');

        $this->document->setTitle($this->language->get('licensing_name'));

        $this->view->assign('error_warning', $this->session->data['warning']);
        if (isset($this->session->data['warning'])) {
            unset($this->session->data['warning']);
        }
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('catalog/licensing'),
            'text'      => $this->language->get('licensing'),
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $grid_settings = [
            'table_id'     => 'license_grid',
            'url'          => $this->html->getSecureURL('listing_grid/licensing', '&product_id='.(int)$product_id),
            'editurl'      => $this->html->getSecureURL('listing_grid/licensing/update',
                                                        '&product_id='.(int)$product_id),
            'update_field' => $this->html->getSecureURL('listing_grid/licensing/update_field',
                                                        '&product_id='.(int)$product_id),
            'sortname'     => 'date_modified',
            'sortorder'    => 'desc',
            'actions'      => [
                'delete' => [
                    'text' => $this->language->get('button_delete'),
                ],
            ],
        ];

        $grid_settings['colNames'] = [
            $this->language->get('licensing_column_id'),
            $this->language->get('licensing_column_option_value_name'),
            $this->language->get('licensing_column_key'),
            $this->language->get('licensing_column_expiry_date'),
            $this->language->get('licensing_column_order_id'),
            $this->language->get('column_status'),
            $this->language->get('licensing_column_date_modified'),
        ];
        $grid_settings['colModel'] = [
            [
                'name'     => 'license_id',
                'index'    => 'license_id',
                'align'    => 'center',
                'width'    => 65,
                'sortable' => false,
                'search'   => false,
            ],
            [
                'name'     => 'option_value_name',
                'index'    => 'oo.name',
                'align'    => 'center',
                'width'    => 140,
                'sortable' => false,
            ],
            [
                'name'     => 'license_key',
                'index'    => 'license_key',
                'align'    => 'center',
                'width'    => 200,
                'sortable' => false,
            ],
            [
                'name'     => 'expiry_date',
                'index'    => 'expiry_date',
                'align'    => 'center',
                'width'    => 200,
                'sortable' => false,
                'search'   => false,
            ],
            [
                'name'   => 'order_id',
                'index'  => 'order_id',
                'align'  => 'left',
                'width'  => 70,
                'search' => true,
            ],
            [
                'name'     => 'status',
                'index'    => 'status',
                'align'    => 'center',
                'width'    => 130,
                'search'   => false,
                'sortable' => false,
            ],
            [
                'name'   => 'date_modified',
                'index'  => 'date_modified',
                'align'  => 'center',
                'width'  => 90,
                'search' => false,
            ],
        ];

        ProductOption::setCurrentLanguageID($language_id);
        $product_options = ProductOption::with('description', 'values', 'values.description')
            ->where('product_id', '=', $product_id)
            ->get();

        $option_list = [];
        foreach ($product_options as $option) {
            $option_name = trim($option->description->name);
            $opt_id = $option->product_option_id;
            $option_list[$opt_id]['name'] = $option_name ?: 'n/a';
            $option_list[$opt_id]['values'] = [];
            foreach ($option->values as $optionValue) {
                $option_list[$opt_id]['values'][] = [
                    'product_option_value_id' => $optionValue->product_option_value_id,
                    'name'                    => $optionValue->description->name ?: 'n/a',
                ];
            }
        }
        $grid_search_form = [];
        $this->data['option_list'] = $option_list;
        if ($option_list) {
            $form = new AForm();
            $form->setForm([
                'form_name' => 'license_upload_form',
            ]);

            $grid_search_form['id'] = 'license_upload_form';
            $grid_search_form['form_open'] = $form->getFieldHtml(
                [
                'type'   => 'form',
                'name'   => 'license_upload_form',
                'action' => $this->html->getSecureURL('catalog/licensing/upload', '&product_id='.$product_id),
                'attr'   => 'class="form-inline"',
                ]);
            $grid_search_form['submit'] = $form->getFieldHtml(
                [
                'type'  => 'button',
                'name'  => 'submit',
                'text'  => 'Upload',
                'style' => 'button1',
                ]);

            $grid_search_form['fields']['file'] = $form->getFieldHtml(
                [
                'type'  => 'file',
                'name'  => 'license_file',
                'value' => '',
                'style' => 'btn-xs',
                ]);

        }

        $grid_settings['search_form'] = true;
        $grid_settings['multiaction_options']['delete'] = $this->language->get('text_delete_selected');
        $grid = $this->dispatch('common/listing_grid', [$grid_settings]);
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());
        $this->view->assign('search_form', $grid_search_form);

        $this->data['active'] = 'license';
        //load tabs controller
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', [$this->data]);
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);
        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/catalog/license_list.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function upload()
    {
        if (!$this->request->is_POST()) {
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }
        $product_id = (int)$this->request->get['product_id'];
        if (!$product_id) {
            abc_redirect($this->html->getSecureURL('catalog/product'));
        }
        $product_option_value_id = (int)$this->request->post['product_option_value_id'];
        if (!$product_option_value_id) {
            $this->session->data['error'] = $this->language->get('licensing_option_value_error');
        }

        if (is_uploaded_file($this->request->files['license_file']['tmp_name'])) {
            if (! in_array(pathinfo($this->request->files['license_file']['name'], PATHINFO_EXTENSION), ['txt','csv'])
            ) {
                unlink($this->request->files['license_file']['tmp_name']);
                $this->session->data['error'] = $this->language->get('licensing_filetype_error');
            } else {
                $this->loadLanguage('licensing/licensing');
                $this->loadModel('catalog/licensing');
                $filepath = $this->request->files['license_file']['tmp_name'];
                $count = $this->model_catalog_licensing->importLicenses(
                    $filepath,
                    $product_id,
                    $product_option_value_id
                );
                unlink($filepath);
                $this->session->data['success'] = sprintf($this->language->get('licensing_import_success'), $count);
            }
        }
        abc_redirect($this->html->getSecureURL('catalog/licensing', '&product_id='.$product_id));
    }
}
