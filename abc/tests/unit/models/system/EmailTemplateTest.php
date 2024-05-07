<?php

namespace Tests\unit\models\system;
use abc\models\system\EmailTemplate;
use Illuminate\Validation\ValidationException;
use Tests\unit\ATestCase;

class EmailTemplateTest extends ATestCase
{
    public function testEmailTemplateValidation()
    {
        $template = new EmailTemplate();
        $errors = [];
        try {
            $data = [
                'language_id' => false,
            ];
            $template->validate($data);
        } catch (ValidationException $e) {
            $errors = $template->errors()['validation'];
        }

        $this->assertCount(4, $errors);


        $errors = [];
        try {
            $data = [
                'language_id' => 1,
                'subject'   => 'some subj',
                'text_body' => 'some text',
                'html_body' => 'some html',
            ];
            $template->validate($data);
        } catch (ValidationException $e) {
            $errors = $template->errors()['validation'];
        }
        $this->assertCount(0, $errors);
    }
}
