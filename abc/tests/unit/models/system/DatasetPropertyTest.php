<?php


use abc\models\system\DatasetProperty;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class DatasetPropertyTest extends TestCase
{

    public function testDatasetValidation()
    {
        $dataset = new DatasetProperty();
        $errors = [];
        try {
            $data = [
                'dataset_id' => false,
            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
        }
        $this->assertCount(1, $errors);

        $errors = [];
        try {
            $data = [
                'dataset_id' => 1,

            ];
            $dataset->validate($data);
        } catch (ValidationException $e) {
            $errors = $dataset->errors()['validation'];
            //var_Dump($errors);
        }
        $this->assertCount(0, $errors);
    }
}
