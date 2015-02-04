<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include 'marvelheroes_config.php';
try {
    $position_grid = "";
    if (isset($_GET['position_grid'])) {
        $position_grid = $_GET['position_grid'];
    }

    $level_grid = "";
    if (isset($_GET['level_grid'])) {
        $level_grid = $_GET['level_grid'];
    }

    $costume_grid = "";
    if (isset($_GET['costume_grid'])) {
        $costume_grid = $_GET['costume_grid'];
    }

    $flair_grid = "";
    if (isset($_GET['flair_grid'])) {
        $flair_grid = $_GET['flair_grid'];
    }

    $url = "http://www.seanwmcginnis.com/marvelheroes/generatesig.php?position_grid=" . $position_grid . "&level_grid=" . $level_grid . "&costume_grid=" . $costume_grid . "&flair_grid=" . $flair_grid . "&version=2";
    if (isset($_GET['font'])) {
        $url = $url . "&font=" . $_GET['font'];
    }
    if (isset($_GET['view_mode'])) {
        $url = $url . "&view_mode=" . $_GET['view_mode'];
    }
    if (isset($_GET['border_type'])) {
        $url = $url . "&border_type=" . $_GET['border_type'];
    }
    if (isset($_GET['border_color'])) {
        $url = $url . "&border_color=" . $_GET['border_color'];
    }
    if (isset($_GET['half_grids'])) {
        $url = $url . "&half_grids=" . $_GET['half_grids'];
    }

    $client_id = IMGUR_CLIENTID;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID e583384de73c9c9'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $url));

    $reply = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if (strlen($error) > 0) {
        $ret = array();
        $ret["error"] = $error;
        $ret["val"] = $reply;
        $ret["location"] = "UPLOAD";
        echo json_encode($ret);
    } else {
        echo $reply;
    }
} catch (Exception $e) {
    $ret = array();
    $ret["error"] = $e->getMessage();
    $ret["val"] = $reply;
    $ret["location"] = "EXCEPTION";
    echo json_encode($ret);
}
?>