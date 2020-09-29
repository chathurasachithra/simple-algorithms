<?php

use Carbon\Carbon;

class AlgorithmHelper
{
    protected $carbon;

    /**
     * AlgorithmHelper constructor.
     * @param Carbon $carbon
     */
    public function __construct(Carbon $carbon)
    {
        $this->carbon = $carbon;
    }

    /**
     * Get best lineup team
     *
     * @param $playerPool
     * @param $cap
     * @param $positions
     * @param bool $cronStatus
     * @return array
     */
    public function getLineUpByPlayerPool($playerPool, $cap, $positions)
    {
        try {
            $team = [];
            $bestTeam = ['team' => [], 'salary' => 0, 'points' => 0];
            $response = $this->getTeam($team, $positions, 0, $playerPool[$positions[0]['position']], $bestTeam, $playerPool, $cap);
            return $this->formatLineup($response['best_team']['team']);
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * Get best lineup from the pool
     *
     * @param $team
     * @param $positions
     * @param $element
     * @param $pool
     * @param $bestTeam
     * @param $playerPool
     * @param $cap
     * @return array
     */
    public function getTeam($team, $positions, $element, $pool, $bestTeam, $playerPool, $cap)
    {
        if (count($pool) > 0) {
            foreach ($pool as $key => $player) {

                $team = $this->addPlayerToTeam($team, $positions[$element], $player);
                $bestTeam = $this->getBestTeam($team, $bestTeam, $cap);
                if (isset($positions[$element + 1])) {
                    $response = $this->getTeam($team, $positions, $element + 1, $playerPool[$positions[$element + 1]['position']], $bestTeam, $playerPool, $cap);
                    $team = $response['team'];
                    $bestTeam = $response['best_team'];
                }
            }
        }
        return ['team' => $team, 'best_team' => $bestTeam];
    }

    /**
     * Check possibility to add player to team and add the player
     *
     * @param $team
     * @param $position
     * @param $player
     * @return array
     */
    public function addPlayerToTeam($team, $position, $player)
    {
        $exist = false;
        foreach ($team as $teamPlayer) {
            $teamPlayers = explode('|', $teamPlayer['player']);
            foreach ($teamPlayers as $teamSinglePlayer) {
                if ($player['player'] == $teamSinglePlayer) {
                    $exist = true;
                }
            }
        }
        if ($exist == false) {
            $player['position'] = $position['position'];
            $team[$position['element']] = $player;
        }
        return $team;
    }

    /**
     * Compare current team with new team and return best team
     *
     * @param $team
     * @param $bestTeam
     * @param $cap
     * @return array
     */
    public function getBestTeam($team, $bestTeam, $cap)
    {
        $currentBestTeam = $bestTeam;
        $salary = 0;
        $points = 0;
        foreach ($team as $player) {

            $salary = $salary + $player['salary'];
            $points = $points + $player['points'];
        }
        if ($salary <= $cap && $points >= $currentBestTeam['points'] && (count($team) >= count($currentBestTeam['team']))) {
            $bestTeam = ['team' => $team, 'salary' => $salary, 'points' => $points];
        }
        return $bestTeam;
    }

    /**
     * Format the generated lineup to process in other methods
     *
     * @param $bestTeam
     * @return array
     */
    public function formatLineup($bestTeam)
    {
        $lineupTeam = [];
        foreach ($bestTeam as $teamPlayer) {
            $lineupTeam[] = ['pos' => $teamPlayer['position'], 'player' => $teamPlayer['player']];
        }
        return $lineupTeam;
    }

    /**
     * Expand multiple player possibilities
     *
     * @param $playerPool
     * @param $league
     * @return array
     */
    public function collapseMultiplePlayersInPool($playerPool, $league)
    {
        //Processing for reduce possibilities. Remove duplicates and game specific development
        switch ($league) {
            case config('constants.leagues.fan_duel'):

                //2PG players
                $array = [];
                $selectedArray = [];
                foreach ($playerPool['PG'] as $set1) {
                    foreach ($playerPool['PG'] as $set2) {

                        if ($this->checkPositionPlayerDuplicates([$set1['player'], $set2['player']], $selectedArray)) {

                            $selectedArray[] = [$set1['player'], $set2['player']];
                            $element = [
                                'player' => $set1['player'] . '|' . $set2['player'],
                                'points' => $set1['points'] + $set2['points'],
                                'salary' => $set1['salary'] + $set2['salary']
                            ];
                            $array[] = $element;
                        }
                    }
                }
                $playerPool['PG'] = $array;

                //2SG players
                $array = [];
                $selectedArray = [];
                foreach ($playerPool['SG'] as $set1) {
                    foreach ($playerPool['SG'] as $set2) {

                        if ($this->checkPositionPlayerDuplicates([$set1['player'], $set2['player']], $selectedArray)) {

                            $selectedArray[] = [$set1['player'], $set2['player']];
                            $element = [
                                'player' => $set1['player'] . '|' . $set2['player'],
                                'points' => $set1['points'] + $set2['points'],
                                'salary' => $set1['salary'] + $set2['salary']
                            ];
                            $array[] = $element;
                        }
                    }
                }
                $playerPool['SG'] = $array;

                //2SF players
                $array = [];
                $selectedArray = [];
                foreach ($playerPool['SF'] as $set1) {
                    foreach ($playerPool['SF'] as $set2) {

                        if ($this->checkPositionPlayerDuplicates([$set1['player'], $set2['player']], $selectedArray)) {

                            $selectedArray[] = [$set1['player'], $set2['player']];
                            $element = [
                                'player' => $set1['player'] . '|' . $set2['player'],
                                'points' => $set1['points'] + $set2['points'],
                                'salary' => $set1['salary'] + $set2['salary']
                            ];
                            $array[] = $element;
                        }
                    }
                }
                $playerPool['SF'] = $array;

                //2PF players
                $array = [];
                $selectedArray = [];
                foreach ($playerPool['PF'] as $set1) {
                    foreach ($playerPool['PF'] as $set2) {

                        if ($this->checkPositionPlayerDuplicates([$set1['player'], $set2['player']], $selectedArray)) {

                            $selectedArray[] = [$set1['player'], $set2['player']];
                            $element = [
                                'player' => $set1['player'] . '|' . $set2['player'],
                                'points' => $set1['points'] + $set2['points'],
                                'salary' => $set1['salary'] + $set2['salary']
                            ];
                            $array[] = $element;
                        }
                    }
                }
                $playerPool['PF'] = $array;
                break;
        }
        return $playerPool;
    }

    /**
     * Format lineup by expanding WR and RB positions
     *
     * @param $lineUp
     * @param $playerPool
     * @param $league
     * @return array
     */
    public function formatLineupByExpandPositions($lineUp, $playerPool, $league)
    {
        //Processing for expand result.League specific development
        $formattedLineup = [];
        foreach ($lineUp as $lineupPlayer) {

            switch ($league) {
                case config('constants.leagues.fan_duel'):


                    switch ($lineupPlayer['pos']) {
                        case 'PG':
                            $players = explode('|', $lineupPlayer['player']);
                            foreach ($players as $player) {
                                $key = array_search($player, array_column($playerPool['PG'], 'player'));
                                $player = $playerPool['PG'][$key];
                                $player['pos'] = $lineupPlayer['pos'];
                                $formattedLineup[] = ['pos' => $lineupPlayer['pos'], 'player' => $player['player']];
                            }
                            break;
                        case 'SG':
                            $players = explode('|', $lineupPlayer['player']);
                            foreach ($players as $player) {
                                $key = array_search($player, array_column($playerPool['SG'], 'player'));
                                $player = $playerPool['SG'][$key];
                                $player['pos'] = $lineupPlayer['pos'];
                                $formattedLineup[] = ['pos' => $lineupPlayer['pos'], 'player' => $player['player']];
                            }
                            break;
                        case 'SF':
                            $players = explode('|', $lineupPlayer['player']);
                            foreach ($players as $player) {
                                $key = array_search($player, array_column($playerPool['SF'], 'player'));
                                $player = $playerPool['SF'][$key];
                                $player['pos'] = $lineupPlayer['pos'];
                                $formattedLineup[] = ['pos' => $lineupPlayer['pos'], 'player' => $player['player']];
                            }
                            break;
                        case 'PF':
                            $players = explode('|', $lineupPlayer['player']);
                            foreach ($players as $player) {
                                $key = array_search($player, array_column($playerPool['PF'], 'player'));
                                $player = $playerPool['PF'][$key];
                                $player['pos'] = $lineupPlayer['pos'];
                                $formattedLineup[] = ['pos' => $lineupPlayer['pos'], 'player' => $player['player']];
                            }
                            break;
                        default:
                            $formattedLineup[] = $lineupPlayer;
                            break;
                    }
                break;
                case config('constants.leagues.draft_kings'):

                    $formattedLineup[] = $lineupPlayer;
                    break;
                case config('constants.leagues.yahoo'):

                    $formattedLineup[] = $lineupPlayer;
                    break;
            }
        }

        foreach ($formattedLineup as $key  => $formattedLineupPlayer) {
            $poolKey = array_search($formattedLineupPlayer['player'], array_column($playerPool[$formattedLineupPlayer['pos']], 'player'));
            $formattedLineup[$key]['points'] = $playerPool[$formattedLineupPlayer['pos']][$poolKey]['points'];
        }
        return $formattedLineup;
    }

    /**
     * Check any duplicate values in player array
     *
     * @param $array
     * @param $selectedArray
     * @return bool
     */
    public function checkPositionPlayerDuplicates($array, $selectedArray)
    {
        try {
            if (count($array) != count(array_flip($array))) {
                return false;
            }
            foreach ($selectedArray as $wrArray) {
                if (empty(array_diff($array, $wrArray))) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Eliminate duplicated possibilities from player list
     *
     * @param $playerPool
     * @return array
     */
    public function eliminateDuplicatePossibilitiesFromList($playerPool)
    {
        foreach ($playerPool as $position => $playerList) {

            if (count($playerList) <= config('constants.min_lineup_pool_size')) {
                continue;
            }

            //Salary higher than or equal to next player, points higher than or equal to next player
            $playerList = $this->multiDimensionArraySortByColumn($playerList, 'salary');
            foreach ($playerList as $key => $player) {

                if (isset($playerList[$key + 1])) {
                    $nextPlayer = $playerList[$key + 1];
                    if (($player['salary'] >= $nextPlayer['salary']) && ($player['points'] <= $nextPlayer['points']) && (count($playerList) > config('constants.min_lineup_pool_size'))) {
                        unset($playerList[$key]);
                    }
                }
            }
            array_multisort($playerList, SORT_ASC);

            //Points less than to next player, points higher than or equal to next player
            $playerList = $this->multiDimensionArraySortByColumn($playerList, 'points', 'ASC');
            foreach ($playerList as $key => $player) {

                if (isset($playerList[$key + 1])) {
                    $nextPlayer = $playerList[$key + 1];
                    if (($player['points'] <= $nextPlayer['points']) && ($player['salary'] >= $nextPlayer['salary']) && (count($playerList) > config('constants.min_lineup_pool_size'))) {
                        unset($playerList[$key]);
                    }
                }
            }
            array_multisort($playerList, SORT_ASC);
            $playerPool[$position] = $playerList;
        }
        return $playerPool;
    }

    /**
     * Sort multi dimension array
     *
     * @param $array
     * @param $key
     * @param string $type
     * @return array
     */
    public function multiDimensionArraySortByColumn($array, $key, $type = 'DESC')
    {
        $sorter = [];
        $return = [];
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        if ($type == 'DESC') {
            arsort($sorter);
        } else {
            asort($sorter);
        }
        foreach ($sorter as $ii => $va) {
            $return[$ii] = $array[$ii];
        }
        return array_values($return);
    }
}
