<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\GlobalAttributesGroup;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class GlobalAttributesGroupTest extends ATestCase
{
    public function testValidator()
    {
        $attr = new  GlobalAttributesGroup();
        $errors = [];
        try {
            $data = [
                'sort_order' => false,
                'status' => false,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'sort_order' => 1,
                'status' => 1,
            ];
            $attr->validate($data);
        } catch (ValidationException $e) {
            $errors = $attr->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
