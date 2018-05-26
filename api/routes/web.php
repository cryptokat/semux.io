<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Psr\Http\Message\ServerRequestInterface;
use Semux\Client\Configuration as SemuxClientConfig;
use Semux\Client\Api\SemuxApi as SemuxApi;

$router->get('/', function () use ($router) {
	return "Hello Semux!";
});

$router->group(['prefix' => 'api'], function() use ($router) {
	$router->get('/', function () use ($router) {
		return response()->json(['version' => "1.2.0"]);
	});

	$router->get('/circulating-supply', function (ServerRequestInterface $request) use ($router) {
		$latestBlockNumber = getApi()->getLatestBlockNumber()->getResult();
		$blockRewards = gmp_mul($latestBlockNumber, 3);
		return (string) gmp_add($blockRewards, "25000000");
	});

	$router->get('/total-supply', function () use ($router) {
		return "100000000";
	});
});

function getApi() {
	$config = new SemuxClientConfig();
	$config->setHost('https://semux.io/api/semux/v2.1.0')->setUsername('user')->setPassword('pass');
	$api = new SemuxApi(new GuzzleHttp\Client(), $config);
	return $api;
}
