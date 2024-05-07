<?php


use abc\models\user\UserNotification;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class UserNotificationTest extends TestCase
{
    public function testUserNotificationValidation()
    {
        $user = new UserNotification();
        $errors = [];
        try {
            $data = [
                'user_id' => false,
                'store_id' => false,
            ];
            $user->validate($data);
        } catch (ValidationException $e) {
            $errors = $user->errors()['validation'];
        }
        $this->assertCount(2, $errors);

        $errors = [];
        try {
            $data = [
                'user_id' => 1,
                'store_id' => 1,
            ];
            $user->validate($data);
        } catch (ValidationException $e) {
            $errors = $user->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
