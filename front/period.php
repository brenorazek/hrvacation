<?php

include('../../../inc/includes.php');

use GlpiPlugin\Hrvacation\Period;

Session::checkRight('plugin_hrvacation_period', READ);

Html::header(
    Period::getTypeName(Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    'tools',
    Period::class
);

// Listagem (motor de busca) dos afastamentos.
Search::show(Period::class);

Html::footer();
