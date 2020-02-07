<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserModel;
use Illuminate\Support\Facades\Redis;
class TestController extends Controller{
    function reg(Request $request){
        //echo '<pre>';print_r($_POST);echo '</pre>';
        $pass1=request()->input('pass1');
        $pass2=request()->input('pass2');
        //验证两次输入的密码
        if($pass1!=$pass2){
            echo "两次输入的密码不一致";die;
        }
        $user_name=request()->input('user_name');
        $user_email=request()->input('user_email');
        //验证 用户名 email 是否已被注册
        $u=UserModel::where(['user_name'=>$user_name])->first();
        if($u){
            $response = [
                'error' => 500002,
                'msg' => '用户名已被使用'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        //验证email
        $u=UserModel::where(['user_email'=>$user_email])->first();
        if($u){
            $response = [
                'error' => 500003,
                'msg' => 'Email已被使用'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE)); 
        }
        //生成密码
        $user_pwd=password_hash($pass1,PASSWORD_BCRYPT);
        //入库
        $user_info = [
            'user_email' => $user_email,
            'user_name' => $user_name,
            'user_pwd' => $user_pwd
        ];
        $uid = UserModel::insertGetId($user_info);
        if($uid){
            $response = [
                'error' => 0,
                'msg' => 'ok'
            ];
        }else{
            $response = [
                'error' => 500001,
                'msg' => '服务器内部错误,请稍后再试'
            ];
        }
        die(json_encode($response));
    }

    function login(Request $request){
        $value=request()->input('user_name');
        $user_pwd=request()->input('user_pwd');
        //按name找记录
        $u1=UserModel::where(['user_name'=>$value])->first();
        $u2=UserModel::where(['user_email'=>$value])->first();
        
        if($u1==NULL&&$u2==NULL){
            $response = [
                'error' => 400004,
                'msg' => '用户不存在'
            ];
            return $response;
        }
        if($u1){//使用用户名登陆
            if(password_verify($user_pwd,$u1->user_pwd)){
                $user_id=$u1->user_id; 
           }else{
                $response = [
                     'error' => 400003,
                     'msg' => 'password wrong'
                ];
                return $response;
           }
        }
        if($u2){//使用email登陆
            if(password_verify($user_pwd,$u2->user_pwd)){
                $user_id=$u2->user_id; 
           }else{
                $response = [
                     'error' => 400003,
                     'msg' => 'password wrong'
                ];
                return $response;
           }
        }
        $token=$this->getToken($user_id);//生成token
        $redis_token_key='str:user:token: '.$user_id;
        echo $redis_token_key;
        Redis::set($redis_token_key,$token,86400);//生成token 设置过期时间

        $response = [
            'error' => 0,
            'msg' => 'ok',
            'data' => [
                'user_id' => $user_id,
                'token' => $token
            ]
        ];
        return $response;
    }
    //生成用户token
    protected function getToken($uid){
    $token=md5(time().mt_rand(11111,99999).$uid);
    return substr($token,5,20);
}
    //获取用户信息接口
    function showTime(){
        if(empty($_SERVER['HTTP_TOKEN'])||empty($_SERVER['HTTP_UID'])){
            $response=[
                'error'=>40003,
                'msg'=>'Token Not Valid!'
            ];
            return $response;
        }
        //获取客户端的token
        $token=$_SERVER['HTTP_TOKEN'];
        $user_id=$_SERVER['HTTP_UID'];
        $redis_token_key='str:user:token: '.$user_id;
        //验证token是否有效
        $cache_token=Redis::get($redis_token_key);
        if($token==$cache_token){//token有效
            $data=date("Y-m-d H:i:s");
            $response=[
                'error'=>0,
                'msg'=>'ok',
                'data'=>$data
            ];
        }else{
            $response=[
                'error'=>40003,
                'msg'=>'Token Not Valid!'
            ];
        }
        return $response;
        
    }
    public function auth(){
        $uid=$_POST['uid'];
        $token=$_POST['token'];
        if(empty($_POST['uid'])||empty($_POST['token'])){
            $response=[
                'error'=>40003,
                'msg'=>'Need token or uid'
            ];
            return $response;
        }
        $redis_token_key='str:user:token: '.$uid;
        //echo 'admin: ' . $redis_token_key;echo "</br>";;
        //验证token是否有效
        $cache_token=Redis::get($redis_token_key);
        //var_dump($cache_token);die;
        if($token==$cache_token){//token有效

            $response=[
                'error'=>0,
                'msg'=>'ok'
            ];
        }else{
            $response=[
                'error'=>40003,
                'msg'=>'Token Not Valid!'
            ];
        }
        return $response;
    }


//    签名
    public function check(){
        echo "接收端>>>>>";echo "</br>";
        echo '<pre>';print_r($_GET);echo '</pre>';

        $key="1905";//计算签名的key 与发送端保持一致

        //验签
        $data=$_GET['data'];//接收到的数据
        $signature=$_GET['signature'];//发送端的数据
        echo "接收到的签名:".$signature;echo "</br>";
        //计算签名
        $s=md5($data.$key);
        echo "接收端计算的签名:".$s;echo "</br>";

        //与接收到的签名 比对
        if($s==$signature){
            echo "验证通过";
        }else{
            echo "验证失败";
        }

        echo "1111";
    }

}
 