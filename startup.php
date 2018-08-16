<?php
ob_start();

//Create New Singular
if (!file_exists('../../../_config')) {
    include_once 'start/include/resetSingular.php';
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    include_once 'start/include/' . ($dados ? 'create' : 'form') . '.php';
}

ob_end_flush();