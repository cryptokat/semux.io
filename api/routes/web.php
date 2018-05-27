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
use Semux\Client\Api\SemuxApi;

$router->get('/', function () use ($router) {
	return "Hello Semux!";
});

$router->group(['prefix' => 'api'], function() use ($router) {
	$router->get('/', function () use ($router) {
		return response()->json(['version' => "1.2.0"]);
	});

	$router->get('/circulating-supply', function (ServerRequestInterface $request, SemuxApi $api) use ($router) {
		$latestBlockNumber = $api->getLatestBlockNumber()->getResult();
		$blockRewards = bcmul($latestBlockNumber, 3);

		$delegates = $api->getDelegates()->getResult();
		$burnedCoins = bcmul(sizeof($delegates), "1000");

		$premine = "25000000";

		return bcsub(bcadd($blockRewards, $premine), $burnedCoins);
	});

	$router->get('/total-supply', function () use ($router) {
		return "100000000";
	});
});
