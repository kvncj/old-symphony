<?php

namespace App\Service;

use App\Model\Email\EmailTemplate;
use App\Model\Email\RecipientList;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $sparkpostClient)
    {
        /**
         * Scoped client with authorization key included.  Defined under packages/framework.yaml
         * 
         * Add the following under framework:
         * http_client:
         *  scoped_clients:
         *   sparkpost.client:
         *    base_uri: 'https://api.sparkpost.com/api/v1/'
         *     headers:
         *      Authorization: '%env(SPARKPOST_KEY)%'
         **/
        $this->httpClient = $sparkpostClient;
    }

    public function sendEmail(EmailTemplate $email, RecipientList $recipients)
    {
        $request = $this->httpClient->request('POST', 'transmissions', [
            'json' => [
                'content' => [
                    'from' => [
                        'name' => $email->getFromName(),
                        'email' => $email->getFromEmail(),
                    ],
                    'subject' => $email->getSubject(),
                    'html' => $email->getContent(),
                ],
                'recipients' => $recipients->format()
            ]
        ]);
        $response = $request->toArray();

        if (isset($response['errors'])) :
            foreach ($response['errors'] as $error) :
                throw new \Exception(print_r($error, true));
            endforeach;
        endif;
    }
}
