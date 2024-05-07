<?php

namespace Tests\unit\models\system;

use abc\models\system\Extension;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class ExtensionTest extends ATestCase
{
    public function testExtensionValidation()
    {
        $extension = new Extension();
        $errors = [];
        try {
            $data = [
                'status' => false,
                'priority' => false,
            ];
            $extension->validate($data);
        } catch (ValidationException $e) {
            $errors = $extension->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'status' => 1,
                'priority' => 1,

            ];
            $extension->validate($data);
        } catch (ValidationException $e) {
            $errors = $extension->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}