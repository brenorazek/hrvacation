<?php

include('../../../inc/includes.php');

use GlpiPlugin\Hrvacation\Period;

$period = new Period();

if (isset($_POST['add'])) {
    $period->check(-1, CREATE, $_POST);
    $newid = $period->add($_POST);
    Html::redirect($period->getFormURLWithID($newid));
} elseif (isset($_POST['update'])) {
    $period->check($_POST['id'], UPDATE);
    $period->update($_POST);
    Html::back();
} elseif (isset($_POST['delete'])) {
    $period->check($_POST['id'], DELETE);
    $period->delete($_POST);
    $period->redirectToList();
} elseif (isset($_POST['purge'])) {
    $period->check($_POST['id'], PURGE);
    $period->delete($_POST, 1);
    $period->redirectToList();
} elseif (isset($_POST['restore'])) {
    $period->check($_POST['id'], DELETE);
    $period->restore($_POST);
    Html::back();
} else {
    // GET: exibe o formulário (novo período se id=-1, ou edição se id informado).
    Session::checkRight('plugin_hrvacation_period', READ);

    Html::header(
        Period::getTypeName(1),
        $_SERVER['PHP_SELF'],
        'tools',
        Period::class
    );

    $period->display([
        'id' => isset($_GET['id']) ? (int) $_GET['id'] : -1,
    ]);

    Html::footer();
}
