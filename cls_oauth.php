<?php
/**
 * Created by PhpStorm.
 * User: cd
 * Date: 15-9-22
 * Time: 下午5:10
 */
//微信授权类
//提供基本方法
class Oauth{
    private $appid;
    private $appsecret;
    private $globalToken;
    private $dateline;
    private $db;
    //从配置数据库中取appid appsecret
    function __construct($db){
        $res = $db->getRow("select * from wechat_config ");  //表 wechat_config 存储了微信信息
        $this->appid = $res['appid'];
        $this->appsecret = $res['appsecret'];
        $this->globalToken = $res['access_token'];
        $this->dateline = $res['dateline'];
        $this->db = $db;
    }

    public function getApp(){
        $res = array();
        $res['appid'] = $this->appid;
        $res['appsecret'] = $this->appsecret;
        return $res;
    }

    //basic授权
    public function basicOauth($redirectUrl){
        $oauth = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=".urlencode($redirectUrl)."&response_type=code&scope=snsapi_base#wechat_redirect";
        header('Location: '.$oauth);
        exit;
    }

    //userinfo授权
    public function userinfoOauth($redirectUrl){
        $oauth = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=".urlencode($redirectUrl)."&response_type=code&scope=snsapi_userinfo&state=reg#wechat_redirect";
        header('Location: '.$oauth);
        exit;
    }

    //返回授权数据， basic 只能到这一步， userinfo则有权限到getUserinfo 这步，取用户信息
    public function getOauthInfo($code){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=$code&grant_type=authorization_code";
        $result = https_request($url);
        return json_decode($result, true);
    }

    //userinfo授权后取得的用户信息
    public  function getUserinfo($access_token, $openid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $result = https_request($url);
        return json_decode($result, true);
    }

   //组合方法，重载， 通过code 网页授权 取用户信息
    public function getUserinfoByCode($code){
        $res = $this->getOauthInfo($code);
        return $this->getUserinfo($res['access_token'], $res['openid']);
    }



    //全局token 和 openid 取得用户基本信息
    public function  getGlobalUserinfo($globalToken, $openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$globalToken&openid=$openid&lang=zh_CN";
        $res = https_request($url);
        $result = json_decode($res, true);
        if ($result['errcode'] == '40001'){
            $newToken = $this->getValidGlobalToken(1);
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$newToken&openid=$openid&lang=zh_CN";
            $res = https_request($url);
            $result = json_decode($res, true);
        }

        return $result;
    }

    //组合方法， 重载， 通过code取得用户基本信息
    public function getGlobalUserinfoByCode($code){
        $res = $this->getOauthInfo($code);
        $token = $this->getValidGlobalToken();
        return $this->getGlobalUserinfo($token, $res['openid']);
    }

    //组合方法， 重载， 通过openid取得用户基本信息
    public function getGlobalUserinfoByOpenid($openid){
        $token = $this->getValidGlobalToken();
        return $this->getGlobalUserinfo($token, $openid);
    }

    //得到一个有效的全局token
    public function getValidGlobalToken($update=0){
        $curTime = time();
        if ($curTime - $this->dateline >= 7200 || $update == 1){
            $globalToken = $this->getGlobalToken();
            $this->db->query("update wechat_config set access_token = '$globalToken[access_token]', dateline = $curTime");
            $this->globalToken = $globalToken['access_token'];
            $this->dateline = $curTime;
            return $globalToken['access_token'];
        }
        else{
            return $this->globalToken;
        }
    }

    //获取 全局token
    public function getGlobalToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
        $result = https_request($url);
        return json_decode($result, true);
    }
}

//curl 执行api
function https_request($url,$data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
?>