<?php
namespace abc\controllers\storefront;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\lib\AEncryption;
use abc\core\lib\AError;
use abc\extensions\licensing\models\storefront\extension\ModelExtensionLicensing;
use abc\models\catalog\ProductOptionValueDescription;
use abc\models\order\Order;
use H;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

/**
 * Class ControllerResponsesExtensionLicensing
 *
 * @property ModelExtensionLicensing $model_extension_licensing
 */
class ControllerResponsesExtensionLicensing extends AController
{

    public function main()
    {
    }

    public function getPdf()
    {
        $this->loadLanguage('licensing/licensing');
        $this->loadModel('extension/licensing');

        /**
         * @var AEncryption $enc
         */
        $enc = ABC::getObjectByAlias('AEncryption', [$this->config->get('encryption_key')]);
        if (!$this->request->get['opt']) {
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        $order_product_token = $this->request->get['opt'];
        //try to decrypt order token
        $decrypted = $enc->decrypt($order_product_token);
        list($order_id, $order_product_id, $email) = explode('::', $decrypted);

        $order_id = (int)$order_id;
        if (!$decrypted || !$order_id || !$email || !$order_product_id) {
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        $order_info = Order::getOrderArray($order_id, null, $this->customer->getId());

        //compare emails
        if ($order_info['email'] != $email) {
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        if (!$order_info) {
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        $licenses = $this->model_extension_licensing->getOrderLicensedProducts($order_id);
        if (!$licenses) {
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        $this->data['license_keys'] = [];
        foreach ($licenses as $row) {
            if ($row['order_product_id'] == $order_product_id && $row['license_key']) {
                $this->data['license_keys'][] = $row['license_key'];
                $this->data['product_name'] = $row['product_name'];
                $this->data['quantity'] = $row['quantity'];
                /** @var ProductOptionValueDescription $valueDescription */
                $valueDescription = ProductOptionValueDescription::where('product_id', '=', $row['product_id'])
                    ->where('product_option_value_id', '=', $row['product_option_value_id'])
                    ->first();
                list($this->data['quantity'], $this->data['validity_period']) = explode(' / ', $valueDescription->name);
            }
        }

        if (!$this->data['license_keys']) {
            $err = new AError('Licensing: Requested order product ID #'.$order_product_id.' has no licenses!');
            $err->toLog();
            abc_redirect($this->html->getSecureURL('error/not_found'));
        }

        //$image_data = $resource->getResource( $this->config->get('config_logo') );
        $this->data['product_logo'] = $this->config->get('config_url') . 'resources/' . $this->config->get('config_logo');
        //exit($this->data['product_logo']);

        $this->data['order_id'] = $order_id;
        $this->data['date'] = H::dateInt2Display(time(), $this->language->get('date_format_short'));
        $this->data['order_date'] = H::dateISO2Display(
            $order_info['date_added'],
            $this->language->get('date_format_short')
        );
        $this->data['customer_name'] = $order_info['firstname'] . ' ' . $order_info['lastname'];
        $this->data['customer_email'] = $order_info['email'];
        $this->data['customer_phone'] = $order_info['telephone'];
        $address_data = [
            'firstname' => '',
            'lastname'  => '',
            'company'   => $order_info['company'],
            'address_1' => $order_info['payment_address_1'],
            'address_2' => $order_info['payment_address_2'],
            'city'      => $order_info['payment_city'],
            'zone'      => $order_info['payment_zone'],
            'postcode'  => $order_info['payment_postcode'],
            'country'   => $order_info['payment_country'],
        ];

        $this->data['customer_address'] = $this->customer->getFormattedAddress(
            $address_data,
            $order_info['payment_address_format']
        );

        $this->data['customer_phone'] = $order_info['telephone'];
        $this->data['reseller_name'] = $this->config->get('store_name');
        $this->data['reseller_email'] = $this->config->get('store_main_email');
        $this->data['reseller_address'] = $this->config->get('config_address');
        $this->data['reseller_contact'] = $this->config->get('config_owner');
        $this->data['licensing_billing_details'] = $this->language->get('licensing_billing_details');
        $this->data['css_url'] = $this->view->templateResource('css/license_pdf.css');

        if (ABC::env('HTTPS') === true) {
            $this->data['base'] = ABC::env('HTTPS_SERVER');
        } else {
            $this->data['base'] = ABC::env('HTTP_SERVER');
        }

        $this->data['direction'] = $this->language->get('direction');
        $this->data['language'] = $this->language->get('code');

        $this->view->batchAssign($this->language->getASet('licensing/licensing'));
        $this->view->batchAssign($this->data);

        try {

            //A4 paper
            $mpdf = new mPDF(
                [
                    'mode'              => 'utf-8',
                    'format'            => 'A4',
                    'default_font_size' => '',
                    'default_font'      => '',
                    'margin_left'       => 0,
                    'margin_right'      => 0,
                    'margin_top'        => 0,
                    'margin_bottom'     => 0,
                    'margin_header'     => 0,
                    'margin_footer'     => 0,
                    'orientation'       => null,
                    'tempDir'           => ABC::env('DIR_SYSTEM').'temp'.DS
                ]);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;  // 1 or 0 - whether to indent the first level of a list
            $html = $this->view->fetch('responses/extension/license_pdf.tpl');
            $mpdf->WriteHTML($html);
            $mpdf->Output('license_certificate.pdf', 'D');
            exit;
        } catch (MpdfException $e) {
            $this->log->error('Licensing PDF-export error: ' . $e->getMessage()
                . " File: " . $e->getFile() . ":" . $e->getLine());
            $this->processTemplate('responses/extension/license_pdf.tpl');
        }

    }
}