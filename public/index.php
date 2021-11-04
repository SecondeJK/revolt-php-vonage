<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Vonage\Client;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$keypair = new \Vonage\Client\Credentials\Keypair(
    file_get_contents('../revolt_php_example.key'),
    '940597b9-7f52-416f-8fd4-a19e0f689602'
);

$vonage = new Client($keypair);

$app->get('/webhook/answer', function (Request $request, Response $response) {
    $ncco = [
        [
            'action' => 'talk',
            'language' => 'en-GB',
            'style' => 7,
            'text' => 'Emergency on factory floor, evacuate to exits immediately'
        ]
    ];

    $response->getBody()->write(json_encode($ncco));

    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/makeCall/{number}', function (Request $request, Response $response, array $args) use ($vonage) {
    $client->calls()->create([
        'to' => [[
            'type' => 'phone',
            'number' => $args['number']
        ]],
        'from' => [
            'type' => 'phone',
            'number' => 'YOUR_VONAGE_NUMBER'
        ],
        'answer_url' => ['https://afb8ad306a73.ngrok.io/webhook/answer']
    ]);

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->run();