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

function fakeDatabaseCall(): string
{
    $faker = Faker\Factory::create('en_GB');
    usleep(250000);
    return $faker->phoneNumber();
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

$app->get('/emergencyNotificationRevolt', function (Request $request, Response $response) use ($vonage) {
    $startTime = microtime();

    // Let's pretend we have to go one at a time to get the phone number because of dataabase/ORM tech debt
    // we're going to create a ton of callbacks from this, then run the event loop
    for ($i = 0; $i < 1200; $i++) {
        \Revolt\EventLoop::defer(function () {
            $outboundNumber = fakeDatabaseCall();

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
                //    $outboundResponse = $vonage->voice()->createOutboundCall($outboundCall);
            ;
        });
    }

    // and now, we run the eventloop until there are no more callbacks
    \Revolt\EventLoop::run();

    $response->getBody()->write('Outbound calls created.' . PHP_EOL);
    $endTime = microtime();

    $time = (getmicrotime($endTime) - getmicrotime($startTime));
    $response->getBody()->write('Completed function in: ' . $time);


    return $response;
});

$app->get('/emergencyNotificationSync', function (Request $request, Response $response) use ($vonage) {
    $startTime = microtime();

    // Let's pretend we have to go one at a time to get the phone number because of dataabase/ORM tech debt
    // we're going to create a ton of callbacks from this, then run the event loop
    for ($i = 0; $i < 1200; $i++) {
            $outboundNumber = fakeDatabaseCall();

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
                //    $outboundResponse = $vonage->voice()->createOutboundCall($outboundCall);
            ;
    }

    $response->getBody()->write('Outbound calls created.' . PHP_EOL);
    $endTime = microtime();

    $time = (getmicrotime($endTime) - getmicrotime($startTime));
    $response->getBody()->write('Completed function in: ' . $time);

    return $response;
});

$app->get('/test', function (Request $request, Response $response) use ($vonage) {
    $outboundCall = new OutboundCall(
        new Phone('+44XXXXXX'),
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

    $response->getBody()->write('Outbound calls created.' . PHP_EOL);

    return $response;
});

$app->run();
