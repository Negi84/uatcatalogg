<?php


use abc\models\user\AntMessage;
use PHPUnit\Framework\TestCase;
use Illuminate\Validation\ValidationException;


class AntMessageTest extends TestCase
{
    public function testAntMessageValidation()
    {
        $ant = new AntMessage();
        $errors = [];
        try {
            $data = [
                'priority' => false,
                'viewed' => false,
            ];
            $ant->validate($data);
        } catch (ValidationException $e) {
            $errors = $ant->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'priority' => 1,
                'viewed' => 1,
            ];
            $ant->validate($data);
        } catch (ValidationException $e) {
            $errors = $ant->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
