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
use abc\core\engine\AForm;
use abc\core\engine\AResource;
use abc\core\lib\ATaskManager;
use abc\models\catalog\ProductDescription;
use abc\models\customer\Customer;
use H;

if (ABC::env('IS_DEMO')) {
    header('Location: static_pages/demo_mode.php');
}

class ControllerPagesSaleContact extends AController
{
    public $error = [];

    public function email()
    {
        $this->data['protocol'] = 'email';
        $this->main();
    }

    public function sms()
    {
        $driver = $this->config->get('config_sms_driver');
        //if sms driver not set or disabled - redirect
        if (!$driver || !$this->config->get($driver . '_status')) {
            abc_redirect($this->html->getSecureURL('sale/contact/email'));
        }

        $this->data['protocol'] = 'sms';
        $this->main();
    }

    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (!H::has_value($this->data['protocol'])) {
            $this->data['protocol'] = 'email';
        }

        $this->document->setTitle($this->language->get('text_send_' . $this->data['protocol']));

        $this->data['token'] = $this->session->data['token'];

        if (isset($this->error)) {
            $this->data['error_warning'] = '';
            foreach ($this->error as $message) {
                $this->data['error_warning'] .= $message . '<br/>';
            }
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['error_subject'] = $this->error['subject'] ?? '';
        $this->data['error_message'] = $this->error['message'] ?? '';
        $this->data['error_recipient'] = $this->error['recipient'] ?? '';

        $this->document->initBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('index/home'),
                'text'      => $this->language->get('text_home'),
                'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href'      => $this->html->getSecureURL('sale/contact'),
                'text'      => $this->language->get('text_send_' . $this->data['protocol']),
                'separator' => ' :: ',
                'current'   => true,
            ]
        );

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->data['action'] = $this->html->getSecureURL('sale/contact');
        $this->data['cancel'] = $this->html->getSecureURL('sale/contact');

        //get store from main switcher and current config
        $this->data['store_id'] = (int)$this->session->data['current_store_id'];

        $this->data['customers'] = $this->data['products'] = [];
        $customerIds = $this->request->get_or_post('to');
        if (!$customerIds && H::has_value($this->session->data['sale_contact_presave']['to'])) {
            $customerIds = $this->session->data['sale_contact_presave']['to'];
        }
        $productIds = $this->request->get_or_post('products');
        if (!$productIds && H::has_value($this->session->data['sale_contact_presave']['products'])) {
            $productIds = $this->session->data['sale_contact_presave']['products'];
        }

        //process list of customer or product IDs to be notified
        if ($customerIds && is_array($customerIds)) {
            $customers = Customer::search(['filter' => ['include' => $customerIds]])->toArray();
            foreach ($customers as $customer_info) {
                $this->data['customers'][$customer_info['customer_id']] = $customer_info['firstname']
                    . ' ' . $customer_info['lastname'] . ' (' . $customer_info['email'] . ')';
            }
        }
        if ($productIds && is_array($productIds)) {
            //get thumbnails by one pass
            $resource = new AResource('image');
            $thumbnails = $resource->getMainThumbList(
                'products',
                $productIds,
                $this->config->get('config_image_grid_width'),
                $this->config->get('config_image_grid_height')
            );

            $productNames = ProductDescription::where('language_id', $this->language->getLanguageID())
                ->whereIn('product_id', $productIds)
                ->useCache('product')
                ->get()
                ?->pluck('name', 'product_id')?->toArray();

            foreach ($productIds as $product_id) {
                if (!$productNames[$product_id]) {
                    continue;
                }
                $thumbnail = $thumbnails[$product_id];
                $this->data['products'][$product_id] = [
                    'name'  => $productNames[$product_id],
                    'image' => $thumbnail['thumb_html'],
                ];
            }
        }

        foreach (['recipient', 'subject', 'message'] as $n) {
            $this->data[$n] = $this->request->post_or_get($n);
            if (!$this->data[$n] && H::has_value($this->session->data['sale_contact_presave'][$n])) {
                $this->data[$n] = $this->session->data['sale_contact_presave'][$n];
            }
        }

        $form = new AForm('ST');
        $form->setForm(
            [
                'form_name' => 'sendFrm',
                'update'    => $this->data['update'],
            ]
        );

        $this->data['form']['form_open'] = $form->getFieldHtml(
            [
                'type'   => 'form',
                'name'   => 'sendFrm',
                'action' => '',
                'attr'   => 'data-confirm-exit="true" class="form-horizontal"',
            ]
        );

        $this->data['form']['submit'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'submit',
                'text'  => $this->language->get('button_send'),
                'style' => 'button1',
            ]
        );
        $this->data['form']['cancel'] = $form->getFieldHtml(
            [
                'type'  => 'button',
                'name'  => 'cancel',
                'text'  => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );

        $this->data['form']['fields']['protocol'] = $form->getFieldHtml(
            [
                'type'  => 'hidden',
                'name'  => 'protocol',
                'value' => $this->data['protocol'],
            ]
        );

        $this->data['form']['build_task_url'] = $this->html->getSecureURL('r/sale/contact/buildTask');
        $this->data['form']['complete_task_url'] = $this->html->getSecureURL('r/sale/contact/complete');
        $this->data['form']['abort_task_url'] = $this->html->getSecureURL('r/sale/contact/abort');

        //set store selector
        $this->view->assign('form_store_switch', $this->html->getStoreSwitcher());

        //build recipient filter
        $options = ['' => $this->language->get('text_custom_send')];

        $dbFilter = ['status' => 1, 'approved' => 1];
        if ($this->data['protocol'] == 'sms') {
            $dbFilter['filter']['only_with_mobile_phones'] = 1;
        }

        $dbFilter['filter']['newsletter_protocol'] = $this->data['protocol'];
        $newsletterDbFilter = $dbFilter;
        $newsletterDbFilter['filter']['all_subscribers'] = 1;

        $allSubscribersCount = Customer::getTotalCustomers($newsletterDbFilter);

        if ($allSubscribersCount) {
            $options['all_subscribers'] = $this->language->get('text_all_subscribers')
                . ' '
                . sprintf(
                    $this->language->get('text_total_to_be_sent'),
                    $allSubscribersCount
                );
        }
        $newsletterDbFilter = $dbFilter;
        $newsletterDbFilter['filter']['only_subscribers'] = 1;
        $onlySubscribersCount = Customer::getTotalCustomers($newsletterDbFilter);
        if ($onlySubscribersCount) {
            $options['only_subscribers'] = $this->language->get('text_subscribers_only')
                . ' '
                . sprintf($this->language->get('text_total_to_be_sent'), $onlySubscribersCount);
        }

        $newsletterDbFilter = $dbFilter;
        $newsletterDbFilter['filter']['only_customers'] = 1;
        $onlyCustomersCount = Customer::getTotalCustomers($newsletterDbFilter);
        if ($onlyCustomersCount) {
            $options['only_customers'] = $this->language->get('text_customers_only')
                . ' ' . sprintf($this->language->get('text_total_to_be_sent'), $onlyCustomersCount);
        }

        $options['ordered'] = $this->language->get('text_customers_who_ordered');
        $this->data['form']['fields']['to'] = $form->getFieldHtml(
            [
                'type'     => 'selectbox',
                'name'     => 'recipient',
                'value'    => $this->data['recipient'],
                'options'  => $options,
                'required' => true,
            ]
        );

        $this->data['recipients_count_url'] = $this->html->getSecureURL('r/sale/contact/getRecipientsCount');

        $this->data['form']['fields']['customers'] = $form->getFieldHtml([
            'type'        => 'multiselectbox',
            'name'        => 'to[]',
            'value' => $customerIds,
            'options'     => $this->data['customers'],
            'style'       => 'chosen',
            'ajax_url'    => $this->html->getSecureURL('r/listing_grid/customer/customers'),
            'placeholder' => $this->language->get('text_customers_from_lookup'),
        ]);

        $this->data['form']['fields']['product'] = $form->getFieldHtml(
            [
                'type'        => 'multiselectbox',
                'name'        => 'products[]',
                'value'       => $productIds,
                'options'     => $this->data['products'],
                'style'       => 'chosen',
                'ajax_url'    => $this->html->getSecureURL('r/product/product/products'),
                'placeholder' => $this->language->get('text_products_from_lookup'),
            ]
        );

        if ($this->data['protocol'] == 'email') {
            $this->data['form']['fields']['subject'] = $form->getFieldHtml(
                [
                    'type'     => 'input',
                    'name'     => 'subject',
                    'value'    => $this->data['subject'],
                    'required' => true,
                ]
            );
        }

        $this->loadModel('setting/store');

        $this->data['form']['fields']['message'] = $form->getFieldHtml(
            [
                'type'     => ($this->data['protocol'] == 'email' ? 'texteditor' : 'textarea'),
                'name'     => 'message',
                'value'    => $this->data['message'],
                'style'    => 'ml_ckeditor',
                'required' => true,
                'base_url' => $this->model_setting_store->getStoreURL($this->data['store_id']),
            ]
        );

        //if email address given
        if (H::has_value($this->request->get['email'])) {
            $this->data['emails'] = (array)$this->request->get['email'];
        }

        $this->data['customers_list'] = $this->html->getSecureURL('user/customers');
        $this->data['presave_url'] = $this->html->getSecureURL('r/sale/contact/presave');

        $this->data['help_url'] = $this->gen_help_url('send_mail');

        if ($this->data['protocol'] == 'email') {
            $resources_scripts = $this->dispatch(
                'responses/common/resource_library/get_resources_scripts',
                [
                    'object_name' => 'contact',
                    'object_id'   => '',
                    'types'       => ['image'],
                ]
            );
            $this->data['resources_scripts'] = $resources_scripts->dispatchGetOutput();
            $this->data['rl'] = $this->html->getSecureURL(
                'common/resource_library',
                '&action=list_library'
                . '&object_name='
                . '&object_id'
                . '&type=image'
                . '&mode=single'
            );
        }

        //load tabs controller
        if ($this->data['protocol'] == 'email' || !H::has_value($this->data['protocol'])) {
            $this->data['active'] = 'email';
        } elseif ($this->data['protocol'] == 'sms') {
            $this->data['active'] = 'sms';
        }

        $this->data['protocols'] = [];
        $this->data['protocols']['email'] = [
            'title' => $this->language->get('text_email'),
            'href'  => $this->html->getSecureURL('sale/contact/email'),
            'icon'  => 'mail',
        ];
        $driver = $this->config->get('config_sms_driver');
        //if sms driver not set or disabled - redirect
        if ($driver && $this->config->get($driver . '_status')) {
            $this->data['protocols']['sms'] = [
                'title' => $this->language->get('text_sms'),
                'href'  => $this->html->getSecureURL('sale/contact/sms'),
            ];
        }

        //check for incomplete tasks
        $tm = new ATaskManager();
        $incomplete = $tm->getTasks(
            [
                'filter' => [
                    'name' => 'send_now',
                ],
            ]
        );

        foreach ($incomplete as $incm_task) {
            //show all incomplete tasks for Top Administrator user group
            if ($this->user->getUserGroupId() != 1) {
                if ($incm_task['starter'] != $this->user->getId()) {
                    continue;
                }
                //rename task to prevent collision with new
                if ($incm_task['name'] == 'send_now') {
                    $tm->updateTask($incm_task['task_id'], ['name' => 'send_now_' . date('YmdHis')]);
                }
            }
            //define incomplete tasks by last time run
            $max_exec_time = (int)$incm_task['max_execution_time'];
            if (!$max_exec_time) {
                //if no limitations for execution time for task - think it's 2 hours
                $max_exec_time = 7200;
            }
            if (time() - H::dateISO2Int($incm_task['last_time_run']) > $max_exec_time) {
                $this->data['incomplete_tasks_url'] = $this->html->getSecureURL('r/sale/contact/incomplete');
                break;
            }
        }

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/sale/contact.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}