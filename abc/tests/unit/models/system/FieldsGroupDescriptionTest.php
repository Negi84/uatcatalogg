<?php

namespace Tests\unit\models\system;

use abc\models\system\FieldsGroupDescription;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class FieldsGroupDescriptionTest extends ATestCase
{
    public function testFieldsGroupDescriptionValidation()
    {
        $fields = new FieldsGroupDescription();
        $errors = [];
        try {
            $data = [
                'group_id' => false,
                'language_id' => false,
            ];
            $fields->validate($data);
        } catch (ValidationException $e) {
            $errors = $fields->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'group_id' => 1,
                'language_id' => 1,
            ];
            $fields->validate($data);
        } catch (ValidationException $e) {
            $errors = $fields->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}