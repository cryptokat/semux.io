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
use Illuminate\Contracts\Cache\Repository as Cache;
use Semux\Client\Api\SemuxApi;

$router->get('/', function () use ($router) {
	return "Hello Semux!";
});

$router->group(['prefix' => 'api'], function() use ($router) {
	$router->get('/', function () use ($router) {
		return response()->json(['version' => "1.2.0"]);
	});

    $router->get('/info', function (SemuxApi $api) use ($router) {
        $info = $api->getInfo()->getResult();
        return response()->json([
            'network' => $info->getNetwork(),
            'capabilities' => $info->getCapabilities(),
            'latestBlockNumber' => $info->getLatestBlockNumber(),
            'latestBlockHash' => $info->getLatestBlockHash(),
            'activePeers' => $info->getActivePeers(),
            'pendingTransactions' => $info->getPendingTransactions()
        ]);
    });

	// provided for crypto trackers
	$router->get('/circulating-supply', function (SemuxApi $api) use ($router) {
		$latestBlockNumber = $api->getLatestBlockNumber()->getResult();
		$blockRewardsSEM = bcmul($latestBlockNumber, 3);

		$delegates = $api->getDelegates()->getResult();
		$burnedCoinsSEM = bcmul(sizeof($delegates), DELEGATE_FEE_SEM);

		return bcsub(bcadd($blockRewardsSEM, PREMINE_SEM), $burnedCoinsSEM);
	});

	// supply summary
    $router->get('/supply-summary', function (SemuxApi $api, Cache $cache) use ($router) {
        // total supply
        $maxSEM = "100000000";

        // community dist
        $airdropsSEM = $cache->remember("airdropsSEM", 60, function () use ($api) {
            $airdropsResp = $api->getAccount(ADDRESS_AIRDROPS)->getResult();
            return bcdiv(bcadd($airdropsResp->getAvailable(), $airdropsResp->getLocked()), SEM_NANO);
        });

        // development
        $devSEM = $cache->remember('devSEM', 60, function () use ($api) {
            $devResp = $api->getAccount(ADDRESS_DEV)->getResult();
            return bcdiv(bcadd($devResp->getAvailable(), $devResp->getLocked()), SEM_NANO);
        });

        // founders
        $foundersSEM = $cache->remember('foundersSEM', 60, function () use ($api) {
            $foundersResp = $api->getAccount(ADDRESS_FOUNDERS)->getResult();
            return bcdiv(bcadd($foundersResp->getAvailable(), $foundersResp->getLocked()), SEM_NANO);
        });

        // block rewards
        $blockRewardsSEM = $cache->remember('blockRewardsSEM', 1, function () use ($api) {
            return bcmul($api->getLatestBlockNumber()->getResult(), 3);
        });

        // burned coins
        $burnedCoinsSEM = $cache->remember('burnedCoinsSEM', 60, function () use ($api) {
            return bcmul(sizeof($api->getDelegates()->getResult()), DELEGATE_FEE_SEM);
        });

        // locked coins
        $lockedCoinsSEM = $cache->remember('lockedCoinsSEM', 60, function () use ($api) {
            $delegates = $api->getDelegates()->getResult();
            $locked = '0';
            foreach ($delegates as $delegate) {
                $locked = bcadd($locked, $delegate->getVotes());
            }
            return bcdiv($locked, SEM_NANO);
        });

        $distributedPremine = bcsub(PREMINE_SEM, bcadd(bcadd($airdropsSEM, $devSEM), $foundersSEM));

        $availableCommunityCoins = bcsub(bcadd($distributedPremine, $blockRewardsSEM), $burnedCoinsSEM);

        return response()->json([
            'maximumSupply' => $maxSEM,
            'totalPremine' => PREMINE_SEM,
            'totalSupply' => bcsub(bcadd($blockRewardsSEM, PREMINE_SEM), $burnedCoinsSEM),
            'availablePremine' => [
                'airdrops' => $airdropsSEM,
                'development' => $devSEM,
                'founders' => $foundersSEM
            ],
            'distributedPremine' => $distributedPremine,
            'blockRewards' => $blockRewardsSEM,
            'burnedCoins' => $burnedCoinsSEM,
            'lockedCoins' => $lockedCoinsSEM,
            'availableCommunityCoins' => $availableCommunityCoins
        ]);
    });
});
