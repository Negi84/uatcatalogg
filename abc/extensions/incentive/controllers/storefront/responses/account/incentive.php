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

namespace abc\controllers\storefront;

use abc\core\engine\AController;
use abc\extensions\incentive\models\IncentiveDescription;
use abc\extensions\incentive\modules\traits\IncentiveTrait;

class ControllerResponsesAccountIncentive extends AController
{
    use IncentiveTrait;

    public function main()
    {
        $this->loadLanguage('incentive/incentive');
        $incentiveId = $this->request->get['incentive_id'];
        /** @var IncentiveDescription $inc */
        $inc = IncentiveDescription::where('incentive_id', '=', $incentiveId)
            ->where('language_id', '=', $this->language->getLanguageID())
            ->first();
        $this->replaceCodes($inc);
        $this->response->setOutput($inc?->description);
    }
}