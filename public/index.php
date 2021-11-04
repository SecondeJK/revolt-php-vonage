<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\Webhook;

require __DIR__ . '/../vendor/autoload.php';

function getmicrotime($t): float
{
    list($usec, $sec) = explode(" ", $t);

    return ((float)$usec + (float)$sec);
}

$app = AppFactory::create();

$keypair = new Keypair(
    file_get_contents('../revolt_php_example.key'),
    '940597b9-7f52-416f-8fd4-a19e0f689602'
);

$vonage = new Client($keypair);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('RevoltPHP + Vonage Example App');

    return $response;
});

$app->get('/webhook/answer', function (Request $request, Response $response) {
    $ncco = [
        [
            'action' => 'talk',
            'language' => 'en-GB',
            'style' => 6,
            'text' => 'Emergency on factory floor, evacuate to exits immediately'
        ]
    ];

    $response->getBody()->write(json_encode($ncco));

    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/emergencyNotification', function (Request $request, Response $response) use ($vonage) {
    $startTime = microtime();

    $faker = Faker\Factory::create('en_GB');
    $phoneNumbers = [];

    for ($i = 0; $i < 4001; $i++) {
        sleep(1);
        $phoneNumbers[] = $faker->phoneNumber();
    }

    foreach ($phoneNumbers as $outboundNumber) {
        $outboundCall = new OutboundCall(
            new Phone($outboundNumber),
            new Phone('+447451284518')
        );

        $outboundCall
            ->setAnswerWebhook(
                new Webhook('https://aef9-82-30-208-179.ngrok.io/webhook/answer', 'GET')
            )
            ->setEventWebhook(
                new Webhook('https://aef9-82-30-208-179.ngrok.io/webhook/event', 'GET')
            )
        ;

    //    $outboundResponse = $vonage->voice()->createOutboundCall($outboundCall);
    }


    $response->getBody()->write('Outbound calls created.' . PHP_EOL);
    $endTime = microtime();

    $time = (getmicrotime($endTime) - getmicrotime($startTime));
    $response->getBody()->write('Completed function in: ' . $time);

    return $response;
});

$app->run();
