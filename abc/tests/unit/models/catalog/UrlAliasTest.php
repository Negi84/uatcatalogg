<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\UrlAlias;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Tests\unit\ATestCase;

class UrlAliasTest extends ATestCase
{
    public function testValidator()
    {
        $url = new UrlAlias();
        $errors = [];
        try {
            $data = [
                'language_id' => false,
            ];
            $url->validate($data);
        } catch (ValidationException $e) {
            $errors = $url->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'language_id' => 1,
            ];
            $url->validate($data);
        } catch (ValidationException $e) {
            $errors = $url->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
