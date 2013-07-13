<?php
namespace Destiny\Action\League;

use Destiny\Service\Fantasy\ChampionService;
use Destiny\Service\Fantasy\LeaderboardService;
use Destiny\AppException;
use Destiny\Service\Fantasy\GameService;
use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Application;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Utils\String;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Game {

	/**
	 * @Route ("/league/game/{gameId}")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamService = TeamService::instance ();
		$challengeService = ChallengeService::instance ();
		$champService = ChampionService::instance ();
		$gameService = GameService::instance ();
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$userId = Session::get ( 'userId' );
		$teamId = Session::get ( 'teamId' );
		$gameId = (isset ( $params ['gameId'] ) && ! empty ( $params ['gameId'] )) ? $params ['gameId'] : '';
		if (empty ( $gameId )) {
			throw new AppException ( 'Game not found' );
		}
		$game = $gameService->getGameById ( $gameId );
		if (empty ( $game )) {
			throw new AppException ( 'Game not found' );
		}
		if (empty ( $teamId )) {
			throw new AppException ( 'A team is required' );
		}
		
		$model->title = 'Game';
		$model->user = Session::getCredentials ()->getData ();
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		
		$model->team = $teamService->getTeamByUserId ( $userId );
		$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		
		$game ['champions'] = $gameService->getGameChampions ( $gameId );
		for($x = 0; $x < count ( $game ['champions'] ); $x ++) {
			$game ['champions'] [$x] ['summonerName'] = String::strictUTF8 ( $game ['champions'] [$x] ['summonerName'] );
		}
		$model->game = $game;
		$model->teamChampionScores = $gameService->getTeamGameChampionsScores ( $teamId, $gameId );
		$model->teamGameScores = $gameService->getTeamGameScores ( $teamId, $gameId, 'GAME' );
		
		$gameLeaderboard = LeaderboardService::instance ()->getTeamLeaderboardByGame ( $gameId, 10 );
		foreach ( $gameLeaderboard as $i => $team ) {
			$gameLeaderboard [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $team ['champions'] ) );
		}
		$model->gameLeaderboard = $gameLeaderboard;
		return 'league/game';
	}

}