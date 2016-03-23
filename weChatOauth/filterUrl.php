<?php
$curUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$curUrl = urlencode($curUrl);

//
// oauth code
//
$params = explode('&', $_SERVER['QUERY_STRING']);
$urlHost = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?';
foreach ( $params as $k => $v ){
    if ( empty($v) )  //去掉空白
        continue;
    if ( preg_match("/^code=|^state/", $v) )  //过滤 code state
        continue;
    $urlHost .= $v.'&';
}
?>