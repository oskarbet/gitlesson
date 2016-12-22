<?php

function playGame(array $teamsPower){

    $flipped = false;
    //если team1power>team2power меняем их значения местами.
    if ($teamsPower[0] > $teamsPower[1])
    {
        $teamsPower = array_reverse($teamsPower);
        $flipped = true;
    }

    //	определение вероятностей для победы1, победы2, ничьи.
    $k = $teamsPower[0] / $teamsPower[1];

    $k1 = pow(exp(1), 1 / 2);
    $beginwin1 = 1 / 3 * pow($k, $k1);

    $k2 = pow(exp(1), 1 / 3);
    $begindraw = 1 / 3 * pow($k, $k2);
    $beginwin2 = 1 - $beginwin1 - $begindraw;

    //коэфициэнты на победу1, ничью, победу2

    $coefficients = [round(1/$beginwin1,2), round(1/$begindraw,2), round(1/$beginwin2,2) ];

    //начальные вероятности забить гол для каждой команды
    $scorewin1 = $beginwin1 + $begindraw * 0.33;
    $scorewin2 = $beginwin2 + $begindraw * 0.33;

    $scorewin1 = $scorewin1 + 0.15 * $scorewin2 * (1 - $k);
    $scorewin2 = $scorewin2 - 0.15 * $scorewin2 * (1 - $k);
    $scoredraw = 1 - $scorewin1 - $scorewin2;

    $scorewin1 = $scorewin1 + 0.8 * $scoredraw * (1 - $k);
    $scoredraw = $scoredraw - 0.8 * $scoredraw * (1 - $k);


    //генерация счета
    $random = 1000;
    $win1 = $scorewin1;
    $win2 = $scorewin2;
    $draw = $scoredraw;

    $score = [0,0];

    $z = 0;
    $scoreskoef = 0.8;
    while ($z == 0) {
        $i = rand(0, $random);
        if (($i / $random) < $win1)
        {
            $score[0]++;

            $win1 = $win1 * $scoreskoef;
            $win2 = $win2 * $scoreskoef;
            $draw = 1 - $win1 - $win2;

        }
        else if (($i / $random) >= $win1 && ($i / $random) <= ($win1 + $draw))
        {
            $z = 1;
        }
        else if (($i / $random) > ($win1 + $draw))
        {
            $score[1]++;

            $win1 = $win1 * $scoreskoef;
            $win2 = $win2 * $scoreskoef;
            $draw = 1 - $win1 - $win2;

        }
    }
    // адаптация к случаю когда team1power > team2power
    if ($flipped)
    {
        $score          = array_reverse($score);
        $teamsPower     = array_reverse($teamsPower);
        $coefficients   = array_reverse($coefficients);

    }

    return [$teamsPower,$score,$coefficients];
}

function parseResults(array $results){

    $parsedResults['team1']['strength']          = $results[0][0];
    $parsedResults['team1']['score']             = $results[1][0];
    $parsedResults['team1']['coefficient_win']   = $results[2][0];

    $parsedResults['team2']['strength']          = $results[0][1];
    $parsedResults['team2']['score']             = $results[1][1];
    $parsedResults['team2']['coefficient_win']   = $results[2][2];

    $parsedResults['coefficient_draw']  		 = $results[2][1];

    return $parsedResults;
}

function renderResults($team1_strength, $team2_strength){
    $results = parseResults(playGame([$team1_strength,$team2_strength]));

    $team1_str      = $results['team1']['strength'];
	global $team1_score;
	global $team2_score;
  	$team1_score	= $results['team1']['score'];
    $team1_coef     = $results['team1']['coefficient_win'];


    $team2_str      = $results['team2']['strength'];
    $team2_score    = $results['team2']['score'];
    $team2_coef     = $results['team2']['coefficient_win'];

    $team_draw     = $results['coefficient_draw'];


    echo '<table border="1"><thead><tr><th>&nbsp;</th><th>Strength</th><th>Score</th><th>Coefficient to Win</th><th>Coefficient to Draw</th></tr></thead>';
    echo "<tbody>";
    echo "<tr><td>Team1</td><td>$team1_str</td><td>$team1_score</td><td>$team1_coef</td><td rowspan='2'>$team_draw</td></tr>";
    echo "<tr><td>Team2</td><td>$team2_str</td><td>$team2_score</td><td>$team2_coef</td></tr>";
    echo "</tbody></table>";


}

renderResults(1400,500);
$duration_match = 90;
$count_of_removed = 0;
$players_reports = array();
$footballers_power_team1 = array(array('id' => 'player1', 'strength' => 0),
                                 array('id' => 'player2', 'strength' => 50),
                                 array('id' => 'player3', 'strength' => 1000),
                                 array('id' => 'player4', 'strength' => 50),
                                 array('id' => 'player5', 'strength' => 50),
                                 array('id' => 'player6', 'strength' => 5000),
                                 array('id' => 'player7', 'strength' => 50),
                                 array('id' => 'player8', 'strength' => 50),
                                 array('id' => 'player9', 'strength' => 50),
                                 array('id' => 'player10', 'strength' => 50),
                                 array('id' => 'player11', 'strength' => 1000));

for ($i = 0; $i <= count($footballers_power_team1)-1; $i++) {
    $players_reports[$i]['id'] = $footballers_power_team1[$i]['id'];
    $players_reports[$i]['power'] = $footballers_power_team1[$i]['strength'];
    $players_reports[$i]['active_time']['begin'] = 1;
    $players_reports[$i]['active_time']['end'] = $duration_match;
}


$match_changes = array(array('id_out' => 2,'id_in' => 'player1_in', 'power' => 123, 'minute_of_change' => 50),
    array('id_out' => 7,'id_in' => 'player2_in', 'power' => 234, 'minute_of_change' => 65),
    array('id_out' => 10,'id_in' => 'player3_in', 'power' => 324, 'minute_of_change' => 80));

$period_weights_team1 = array(10 ,100);
$match_report_by_minutes = array();
$count_of_changes = 0;


$players_report = array();
//внесение замен в предварительний отчет матча
/*for ($i = 0; $i <= count($match_changes)-1; $i++) {

    $x = $match_changes[$i]['minute_of_change']; //минута замены
    $y = count($match_report_by_minutes[$x]['change']); //номер замены на минуте $x;
    $match_report_by_minutes[$x]['change'][$y]['player_'id'_out'] = $match_changes[$i]['id_out'];
    $match_report_by_minutes[$x]['change'][$y]['player_id_in'] = $match_changes[$i]['id_in'];

    $z = count($players_report);
}*/



    function goalKickers(array $footballPlayersPower, $goals_count, array $period_weight) {
        global $match_report_by_minutes, $players_reports, $count_of_changes, $match_changes, $duration_match;
	$all_players_power = array_sum($footballPlayersPower);
    $goals_authors = array();
        $goals_minutes = array();
    $period_duration = 90/count($period_weight);

	echo $all_players_power."<br />";

    for ($i=0; $i<=10; $i++) {
        $players_koeficients[$i]=round(($footballPlayersPower[$i]/$all_players_power),3);
        $koeficients_sum[$i] = $koeficients_sum[$i-1]+$players_koeficients[$i]*1000;
        $sum_for_random = $sum_for_random + $players_koeficients[$i];
        echo $players_koeficients[$i]."<br />";
    }

    $sum_for_random = $sum_for_random*1000;
    echo $sum_for_random."<br />";

/*    for ($i = 1; $i<=$goals_count; $i++) {
        $goal_kicker = mt_rand(1,$sum_for_random);
        for ($j=0; $j<=10; $j++)
            if ($goal_kicker <= $koeficients_sum[$j]) {
                array_push($goals_authors,$j+1);
                break;
            }

    }
    print_r($goals_authors);
        echo "<hr />";*/

    $sum_period_weight = array_sum($period_weight);
    for ($i=0; $i<=count($period_weight)-1; $i++) {
        $period_koeficients[$i] = round(($period_weight[$i] / $sum_period_weight), 3);
        $period_sum[$i] = $period_sum[$i - 1] + $period_koeficients[$i] * 1000;
        $sum_for_random_periods = $sum_for_random_periods + $period_koeficients[$i];
        echo $period_koeficients[$i] . "<br />";

    }
        $sum_for_random_periods = $sum_for_random_periods*1000;
        echo $sum_for_random_periods."<br />";
        for ($i = 1; $i<=$goals_count; $i++) {
            $period_of_goal = mt_rand(1,$sum_for_random_periods);
            for ($j=0; $j<=count($period_weight); $j++)
                if ($period_of_goal <= $period_sum[$j]) {
                    $minute_of_goal = mt_rand(1,90/count($period_weight));
                    $minute_of_goal = $j*90/count($period_weight)+$minute_of_goal;
                    array_push($goals_minutes,$minute_of_goal);
                    //$match_report_by_minutes[$minute_of_goal]['goal'] = $match_report_by_minutes[$minute_of_goal]['goal']+1;
                     //определяем автора гола на данной минуте
                    $z = 0;
                    while ($z == 0) {
                        $goal_kicker = mt_rand(1,$sum_for_random);
                        for ($j=0; $j<=10; $j++)
                            if ($goal_kicker <= $koeficients_sum[$j]) {
                                $player_for_goal = $j+1;
                                if (($players_reports[$player_for_goal]['active_time']['begin'] <= $minute_of_goal)&
                                    ($players_reports[$player_for_goal]['active_time']['end'] >= $minute_of_goal)) {
                                    $players_reports[$player_for_goal]['match report']['goals'][count($players_reports[$player_for_goal]['match report']['goals'])] = $minute_of_goal;
                                    $match_report_by_minutes[$minute_of_goal]['goal'][count($match_report_by_minutes[$minute_of_goal]['goal'])] = $players_reports[$player_for_goal]['id'];
                                    $z = 1;

                                }

                                array_push($goals_authors,$j+1);
                                break;
                            }

                    }


                    break;
                }
        }


       /* for ($i = 0; $i <= count($goals_minutes)-1; $i++ ) {
            $match_report_by_minutes[$goals_minutes[$i]]['goal'] = $match_report_by_minutes[$goals_minutes[$i]]['goal']+1;
        }*/
        print_r($goals_minutes);
}
function changes($changes_plan = array())
{
    global $match_report_by_minutes, $duration_match;
}
function injures($injury_chance) {
    global $match_report_by_minutes, $players_reports, $count_of_changes, $match_changes, $duration_match;
       for ($i = 1; $i <= $duration_match; $i++) {
       if (mt_rand(1,10000) <= $injury_chance*100) {
           $match_report_by_minutes[$i]['injure'] = 'yes';
           $k = 0;$z = 0;
           while ($z == 0 & $k < 100)  {
               $k++;
               $player_for_injure = mt_rand(1, count($players_reports) - 1);
               if (($players_reports[$player_for_injure]['active_time']['begin'] <= $i) &
                   ($players_reports[$player_for_injure]['active_time']['end'] >= $i)
               ) {
                   $z = 1;
                   $players_reports[$player_for_injure]['active_time']['end'] = $i;
                   $players_reports[$player_for_injure]['match report']['injure'] = $i;
                   if ($count_of_changes < 3) {
                       $players_reports[$player_for_injure]['match report']['change']['minute'] = $i;
                       $players_reports[$player_for_injure]['match report']['change']['action'] = 'out';
                       $y = count($match_report_by_minutes[$i]['change']); //номер замены на минуте $x;
                       $match_report_by_minutes[$i]['change'][$y]['player_id_out'] = $players_reports[$player_for_injure]['id'];
                       $match_report_by_minutes[$i]['change'][$y]['player_id_in'] = $match_changes[$count_of_changes]['id_in'];
                       //добавляем игрока с замены в отчет по игроках
                       $q = count($players_reports);
                       $players_reports[$q]['match report']['change']['action'] = 'in';
                       $players_reports[$q]['active_time']['begin'] = $i;
                       $players_reports[$q]['active_time']['end'] = $duration_match;
                       $players_reports[$q]['id'] = $match_changes[$count_of_changes]['id_in'];

                       $count_of_changes++;

                   }
               }
           }
       }
   }

}

function yellow_cards($yellow_card_chance) {
    global $match_report_by_minutes, $duration_match, $players_reports, $count_of_removed;
    for ($i = 1; $i <= $duration_match; $i++) {
        if (mt_rand(1,10000) <= $yellow_card_chance*100) {
            $match_report_by_minutes[$i]['yellow_card'] = 'yes';
            $z = 0;$k = 0;
            while ($z == 0 & $k < 100)  {
                $k++;
                $player_for_yellow_card =mt_rand(1,count($players_reports)-1);
                if (($players_reports[$player_for_yellow_card]['active_time']['begin'] <= $i)&
                    ($players_reports[$player_for_yellow_card]['active_time']['end'] >= $i)) {
                    $z = 1;
                    if (count($players_reports[$player_for_yellow_card]['match report']['yellow cards']) == 1 &
                    $players_reports[$player_for_yellow_card]['match report']['red card'] =='' &
                        count($players_reports[$player_for_yellow_card]['match report']['injure']) == 0 &
                        $count_of_removed < 3) {
                        $players_reports[$player_for_yellow_card]['active_time']['end'] = $i;
                        //$y = count($players_reports[$player_for_red_card]['match report']);
                        $players_reports[$player_for_yellow_card]['match report']['yellow cards'][1] = $i;
                        $count_of_removed++;
                    }
                    if (count($players_reports[$player_for_yellow_card]['match report']['yellow cards']) == 0) {
                        $players_reports[$player_for_yellow_card]['match report']['yellow cards'][0] = $i;
                    }
                    if (count($players_reports[$player_for_yellow_card]['match report']['yellow cards']) == 1 &
                        $players_reports[$player_for_yellow_card]['match report']['red card'] !='') $z = 0;

                }

            }
        }
    }
}

function red_cards($red_card_chance) {
    global $match_report_by_minutes, $duration_match,$players_reports, $count_of_removed;
        for ($i = 1; $i <= $duration_match; $i++) {
            if (mt_rand(1,10000) <= $red_card_chance*100) {
                $match_report_by_minutes[$i]['red_card'] = 'yes';
                $k = 0;$z = 0;
                if ($count_of_removed < 3) {
                    while ($z == 0 & $k < 100) {
                        $k++;
                        $player_for_red_card = mt_rand(1, count($players_reports) - 1);
                        if (($players_reports[$player_for_red_card]['active_time']['begin'] <= $i) &
                            ($players_reports[$player_for_red_card]['active_time']['end'] >= $i) &
                            (count($players_reports[$player_for_red_card]['match report']['injure']) == 0)
                        ) {
                            $players_reports[$player_for_red_card]['active_time']['end'] = $i;
                            //$y = count($players_reports[$player_for_red_card]['match report']);
                            $players_reports[$player_for_red_card]['match report']['red card'] = $i;
                            $z = 1;
                        }
                    }
                    $count_of_removed++;
                }
            }
        }


}
function changes{
    global $max_count_of_change, $match_report_by_minutes, $players_reports, $count_of_changes, $match_changes, $duration_match;

    for ($i = $count_of_changes; $i < $max_count_of_change; $i++)
    {
        $players_reports[count($players_reports)]['match report']['changeout']['minute']    =   $i;

        $y = count($match_report_by_minutes[$i]['change']); //номер замены на минуте $x;

        $match_report_by_minutes[$i]['change'][$y]['player_id_out']     =   $players_reports[$player_for_injure]['id'];
        $match_report_by_minutes[$i]['change'][$y]['player_id_in']      =   $match_changes[$count_of_changes]['id_in'];

        //добавляем игрока с замены в отчет по игроках

        $q = count($players_reports);

        $players_reports[$q]['match report']['changein']            =   $i;
        $players_reports[$q]['active_time']['begin']                =   $i;
        $players_reports[$q]['active_time']['end']                  =   $duration_match;
        $players_reports[$q]['id']                                  =   $match_changes[$count_of_changes]['id_in'];
        $players_reports[$q]['power']                               =   $match_changes[$count_of_changes]['power'];

        $count_of_changes++;
    }
}


//function match_report(){



//goalKickers($footballers_power_team1, $team1_score, $period_weights_team1);
injures(3);

red_cards(1);
yellow_cards(80);

//for ($i = 1; $i <= $duration_match; $i++) {
echo '<pre>';  print_r( $players_reports); echo '</pre>';
echo '<pre>';  print_r( $match_changes); echo '</pre>';
   echo '<pre>';  print_r( $match_report_by_minutes); echo '</pre>';

//}

//match_report();

?>