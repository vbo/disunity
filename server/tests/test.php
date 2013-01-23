<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$test = new Test_PlanningPhase();
$test->execute();
$test = new Test_TurnedPlanningPhase();
$test->execute();
