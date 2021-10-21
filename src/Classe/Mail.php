<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    /* VARIABLES CONTENANT LES ClÉS MAILJET */
    private $api_key = "";
    private $secret_key = "";

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->secret_key,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "maxime.paletou@icloud.com",
                        'Name' => "Maxime"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3278887,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}