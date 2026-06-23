<?php

include('../../../inc/includes.php');

use GlpiPlugin\Hrvacation\Period;

Session::checkRight('plugin_hrvacation_period', READ);

Html::header(
    __('Calendário de afastamentos', 'hrvacation'),
    $_SERVER['PHP_SELF'],
    'tools',
    Period::class
);

$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

Period::showCalendar($year, $month);

Html::footer();
