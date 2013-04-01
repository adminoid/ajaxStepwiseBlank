<?php
/**
 * User: Petja
 * Date: 31.03.13
 * Time: 14:15
 */

header("Cache-Control: no-store, no-cache, must-revalidate");

// Пропустить только ajax запрос
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) or ($_SERVER['HTTP_X_REQUESTED_WITH']) != 'XMLHttpRequest'){
    die('Это не ajax запрос!');
}

// Пропустить только допустимые action:
$availableActions = array('DbRecreateWithQueue', 'DbShowData', 'DbDeleteAll', 'ProcessQueue');
if(!in_array($action = $_POST['action'], $availableActions)){
    die('Нет такого действия!');
}

// Подключить функционал
include "stepWise.class.php";
$sw = new stepWise;

// Запустить действие
echo json_encode($sw->$action());