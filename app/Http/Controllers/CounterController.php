<?php
// +----------------------------------------------------------------------
// | 文件: index.php
// +----------------------------------------------------------------------
// | 功能: 提供count api接口
// +----------------------------------------------------------------------
// | 时间: 2021-12-12 10:20
// +----------------------------------------------------------------------
// | 作者: rangangwei<gangweiran@tencent.com>
// +----------------------------------------------------------------------

namespace App\Http\Controllers;

use Error;
use Exception;
use App\Counters;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class CounterController extends Controller
{
    function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }
    public function getwx(){


        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx35427d448aa7791d&secret=e8720b20a5ff6cf11cab571a0f3fabeb' ;
        $res1 = $this->geturl($url) ;

        $access_token = isset($res1['access_token'])?$res1['access_token']:'' ;
        //echo 'access_token'.$access_token ;echo "<br>" ;echo '<br>' ;
        $url2 = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';

        $res2 = $this->geturl($url2) ;
        //var_dump($res2);echo '<br>' ;echo '<br>' ;
        $ticket = isset($res2['ticket'])?$res2['ticket']:'' ;

        $stringarr = array(
            'jsapi_ticket'  => $ticket,
            'noncestr'      => 'Wm3WZYTPz0wzccnW' ,
            'timestamp'     => time() ,
            'url'           => 'http://mp.weixin.qq.com?params=value'
        );
        //var_dump($stringarr);
        //echo "<br>" ;echo "<br>" ;
        $string1 = '' ;
        foreach ($stringarr as $key => $value) {
            if ($key == 'jsapi_ticket') {
                $string1 = $string1.$key.'='.$value ;
            }else {
                $string1 = $string1."&".$key.'='.$value ;
            }
        }

       // var_dump('$string1: <br>'.$string1);echo '<br>' ;echo '<br>' ;
//$string1='jsapi_ticket=O3SMpm8bG7kJnF36aXbe84xSqh4Bqpw-3Tz_LQQXura4wIF878meBbEgNap7iSoFW0TP4Qt08dTvWJPgBobPFA&noncestr=Wm3WZYTPz0wzccnW&timestamp=1677209742&url=http://mp.weixin.qq.com?params=value' ;
        $signature = sha1($string1) ;
        $stringarr['signature']=$signature ;
        //echo '$signature:<br>'.$signature ; echo '<br>' ; echo '<br>' ;

        return view('counter1',$stringarr);
    }

    /**
     * 获取todo list
     * @return Json
     */
    public function getCount()
    {
        try {
            $data = (new Counters)->find(1);
            if ($data == null) {
                $count = 0;
            }else {
                $count = $data["count"];
            }
            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::info('getCount rsp: '.json_encode($res));
            return response()->json($res);
        } catch (Error $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("查询计数异常" . $e->getMessage())
            ];
            Log::info('getCount rsp: '.json_encode($res));
            return response()->json($res);
        }
    }


    /**
     * 根据id查询todo数据
     * @param $action `string` 类型，枚举值，等于 `"inc"` 时，表示计数加一；等于 `"reset"` 时，表示计数重置（清零）
     * @return Json
     */
    public function updateCount()
    {
        try {
            $action = request()->input('action');
            if ($action == "inc") {
                $data = (new Counters)->find(1);
                if ($data == null) {
                    $count = 1;
                }else {
                    $count = $data["count"] + 1;
                }
    
                $counters = new Counters;
                $counters->updateOrCreate(['id' => 1], ["count" => $count]);
            }else if ($action == "clear") {
                Counters::destroy(1);
                $count = 0;
            }else {
                //throw '参数action错误';
            }

            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::info('updateCount rsp: '.json_encode($res));
            return response()->json($res);
        } catch (Exception $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("更新计数异常" . $e->getMessage())
            ];
            Log::info('updateCount rsp: '.json_encode($res));
            return response()->json($res);
        }
    }
}
