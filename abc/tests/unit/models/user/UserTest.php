<?php


use abc\models\user\User;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserValidation()
    {
        $user = new User();
        $errors = [];
        try {
            $data = [
                'user_group_id' => false,
                'status' => false,
            ];
            $user->validate($data);
        } catch (ValidationException $e) {
            $errors = $user->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'user_group_id' => 1,
                'status' => 1,
            ];
            $user->validate($data);
        } catch (ValidationException $e) {
            $errors = $user->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
