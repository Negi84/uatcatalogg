<?php

namespace Tests\unit\models\customer;

use abc\models\customer\CustomerNotification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class CustomerNotificationTest extends TestCase
{
    public function testValidator()
    {
        $customer = new CustomerNotification(
            [
                'customer_id' => -1,
                'status' => -1,

            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
            //var_dump($errors);
        }

        $this->assertCount(2, $errors);


        $customer = new CustomerNotification(
            [
                'category_id' => 1,
                'store_id' => 1,
            ]
        );
        $errors = [];
        try {
            $customer->validate();
        } catch (ValidationException $e) {
            $errors = $customer->errors()['validation'];
            //var_dump($errors);
        }
        $this->assertCount(0, $errors);

    }
}
