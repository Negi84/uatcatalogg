<?php

namespace Tests\unit\models\catalog;

use abc\models\catalog\Review;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Tests\unit\ATestCase;

class ReviewTest extends ATestCase
{
    public function testValidator()
    {
        $review = new Review();
        $errors = [];
        try {
            $data = [
                'product_id' => false,
                'customer_id' => false,
                'rating' => false,
                'status' => 0.000001111,
            ];
            $review->validate($data);
        } catch (ValidationException $e) {
            $errors = $review->errors()['validation'];
        }
        $this->assertCount(4, $errors);

        $errors = [];
        try {
            $data = [
                'product_id' => 1,
                'customer_id' => 1,
                'rating' => 36,
                'status' => 1,
            ];
            $review->validate($data);
        } catch (ValidationException $e) {
            $errors = $review->errors()['validation'];
            // var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
