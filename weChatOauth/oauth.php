<?php

require_once(dirname(__FILE__). '../cls_oauth.php'); //引入cls
$Oauth = new Oauth($db);            // 实例化微信授权类

$code = htmlspecialchars($_GET['code'])? htmlspecialchars($_GET['code']):'';
$state = htmlspecialchars($_GET['state'])? htmlspecialchars($_GET['state']):'';
// $curUrl ：当前url
$userinfo = array();
// 场景1
{
    //basic授权 获取openid
    if (!$code){
        $Oauth->basicOauth($curUrl);
    }

    $userinfo = $Oauth->getGlobalUserinfoByCode($code);  // 全局token取用户基本信息， 可以取到是否用户关注公众号

//未关注公众号， 不能得到 用户其他信息
    if ( $userinfo['subscribe'] != 1){
        //todo code
    }

    //已关注公众号
    //todo code
}

// 场景2
{
    if ( !$code )
        $Oauth->userinfoOauth($curUrl);

    $userinfo = $Oauth->getUserinfoByCode($code);  //网页授权取用户信息
}

// 场景3
{
    if (!$code && $state != 'reg'){
        $Oauth->basicOauth($curUrl);
    }
    $userinfo = $Oauth->getOauthInfo($code);  //取到 openid

    if ( check($userinfo['openid']) ){
        // 根据 openid 查询， 如果SESSION 或者 数据库 中有 该openid， 直接登录
        //todo code
    }
    else{
    // 该用户信息不存在
    // 处理-- 可以提示  -- 也可以再次授权取到信息并插入数据
    // $code需要新申请， 需要state变量避免陷入死循环
    	if ( !$code && $state == 'reg')  // state 可以自己设置，cls.php 文件设置的为 'reg'
        	$Oauth->userinfoOauth($curUrl);

    	$userinfo = $Oauth->getUserinfoByCode($code);
	}
}
?>