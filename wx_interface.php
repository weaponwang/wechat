<?php
//装载模板文件
include_once("wx_tpl.php");

//获取微信发送数据
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

  //返回回复数据
if (!empty($postStr)){
          
    	//解析数据
          $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    	//发送消息方ID
          $fromUsername = $postObj->FromUserName;
    	//接收消息方ID
          $toUsername = $postObj->ToUserName;
   	 	//消息类型
          $form_MsgType = $postObj->MsgType;
   
  
    	//地理位置,本地天气
          if($form_MsgType=="location")
          {
            //获取地理消息信息，经纬度，地图缩放比例，地址
            $from_Location_X=$postObj->Location_X;
            $from_Location_Y=$postObj->Location_Y;
            $from_Location_Scale=$postObj->Scale;
            $from_Location_Label=$postObj->Label;
            //地址解析使用百度地图API的链接
            $map_api_url="http://api.map.baidu.com/geocoder?";
            //坐标类型
            $map_coord_type="&coord_type=wgs84";
            //建立抓取对象
            $f = new SaeFetchurl();
            //抓取百度地址解析
            $geocoder = $f->fetch($map_api_url.$map_coord_type."&location=".$from_Location_X.",".$from_Location_Y);
            //如果抓取地址解析成功
            if($f->errno() == 0)
            {
              //匹配出城市
                preg_match_all( "/\<city\>(.*?)\<\/city\>/", $geocoder, $city ); 
                $city=str_replace(array("市","县","区"),array("","",""),$city[1][0]);
              //通过新浪天气接口查询天气的链接
                $weather_api_url="http://php.weather.sina.com.cn/xml.php?password=DJOYnieT8234jlsK";
              //城市名转字符编码
                $city="&city=".urlencode(iconv("UTF-8","GBK",$city));
              //查询当天
                $day="&day=0";
              //抓取天气
                $weather = $f->fetch($weather_api_url.$city.$day);
              //如果抓取到天气
               if($f->errno() == 0 && strstr($weather,"Weather"))
              {
                //用正则表达式获取数据
                preg_match_all( "/\<city\>(.*?)\<\/city\>/", $weather, $w_city);
                preg_match_all( "/\<status2\>(.*?)\<\/status2\>/", $weather, $w_status2);
                preg_match_all( "/\<status1\>(.*?)\<\/status1\>/", $weather, $w_status1);
                preg_match_all( "/\<temperature2\>(.*?)\<\/temperature2\>/", $weather, $w_temperature2);
                preg_match_all( "/\<temperature1\>(.*?)\<\/temperature1\>/", $weather, $w_temperature1);
                preg_match_all( "/\<direction2\>(.*?)\<\/direction2\>/", $weather, $w_direction2);
                preg_match_all( "/\<power2\>(.*?)\<\/power2\>/", $weather, $w_power2);
                preg_match_all( "/\<chy_shuoming\>(.*?)\<\/chy_shuoming\>/", $weather, $w_chy_shuoming);
                preg_match_all( "/\<savedate_weather\>(.*?)\<\/savedate_weather\>/", $weather, $w_savedate_weather);
                //如果天气变化一致
                if($w_status2==$w_status1)
                {
                        $w_status=$w_status2[1][0];
                }
                else
                {
                        $w_status=$w_status2[1][0]."转".$w_status1[1][0];
                }
                //将获取到的数据拼接起来
                $weather_res=array(
                $w_city[1][0]."天气预报",
                "发布：".$w_savedate_weather[1][0],
                "气候：".$w_status,
                "气温：".$w_temperature2[1][0]."-".$w_temperature1[1][0],
                "风向：".$w_direction2[1][0],
                "风力：".$w_power2[1][0],
                "穿衣：".$w_chy_shuoming[1][0]
                );
                $weather_res=implode("\n",$weather_res);
                
               
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $weather_res);
                echo $resultStr;
              }
              else
              {
                //失败提示
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "天气获取失败");
                echo $resultStr;
              }
            }
            else
            {
              //失败提示
              $msgType = "text";
              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "无法获取地理位置");
              echo $resultStr;
            }
            exit;
          }

     	//文字消息
          if($form_MsgType=="text")
          {
              
           //获取用户发送的文字内容
            $form_Content = trim($postObj->Content);
              
              
              
	  		//如果发送内容不是空白回复用户
 	   			if(!empty($form_Content))
           		{
            
              //回复英语类别
               /* if($form_Content=="英语")
                {
                    
                  $return_str="请输入数字浏览自己要考的英语：\n\n";
                  $return_arr=array("1.英语1\n","2.英语2\n");
                  $return_str.=implode("",$return_arr);
                  $msgType = "text";
                  $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $return_str);
                  echo $resultStr;
                  exit;                                   
                
                }*/
                    //2014巴西世界杯赛程
                    if($form_Content=="世界杯赛程"){
                       $worldcup="<xml>\n
              			<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
              			<FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
              			<CreateTime>".time()."</CreateTime>\n
              			<MsgType><![CDATA[news]]></MsgType>\n
              			<ArticleCount>1</ArticleCount>\n
              			<Articles>\n"; 
                         $worldcup.="<item>\n
             			 <Title><![CDATA[2014巴西世界杯赛程]]></Title> \n
              			<Description><![CDATA[]]></Description>\n
              			<PicUrl><![CDATA[http://weapon.qiniudn.com/111304544204656.jpg]]></PicUrl>\n
              			<Url><![CDATA[http://url.cn/RInu1v]]></Url>\n
              			</item>\n";
                         $worldcup.="</Articles>\n
              			<FuncFlag>0</FuncFlag>\n
              			</xml>";
                         echo $worldcup;
                		//echo $resultStr1;
              			exit;
                    }
                    //2014巴西世界杯赛程
                    
                    //每日宜忌
                    if ($form_Content == '每日宜忌') {
                        //$keyword = trim($object->Content);
                        $url = "http://api100.duapp.com/almanac/?appkey=trialuser";
                        $output = file_get_contents($url);
                        $contentStr = json_decode($output, true);
                    
                        $msgType = "text";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        exit;
                    }
                    //每日宜忌
                    
                    //百度百科
                    $keyword6=$form_Content;
                    $baike = mb_substr($keyword6,0,2,"UTF-8");
                    $baikeContent = mb_substr($keyword6,2,220,"UTF-8");
                    if($baike=='百科'&&!empty($baikeContent)){
                        include("baike.php");
                        $contentStr = getEncyclopediaInfo($baikeContent);
                        $msgType = "text";
                        $replyContent = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $replyContent;
                        exit;
                        
                    }
                    //百度百科
                    
                   
                     //在线点歌功能
                    $keyword2=$form_Content;
                    $strmusic = mb_substr($keyword2,0,2,"UTF-8");
                    $musicName = mb_substr($keyword2,2,220,"UTF-8");
                    if($strmusic=='音乐'&&!empty($musicName)){
                        function getMusicInfo($entity) {
							if ($entity == "") {
								$music = "你还没有告诉我音乐名称呢";
							} else {
								$url = "http://box.zhangmen.baidu.com/x?op=12&count=1&title=" . $entity . "$$";
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

								$data = curl_exec($ch);
								$music = "没有找到这首歌，换首歌试试吧";
								try {
									@ $menus = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
									foreach ($menus as $menu) {
										if (isset ($menu->encode) && isset ($menu->decode) && !strpos($menu->encode, "baidu.com") && strpos($menu->decode, ".mp3")) {
											$result = substr($menu->encode, 0, strripos($menu->encode, '/') + 1) . $menu->decode;
											if (!strpos($result,"?") && !strpos($result,"xcode")) {
												$music = array (
													"Title" => $entity,
													"Description" => "考研经验倾情奉献",
													"MusicUrl" => urldecode($result),
													"HQMusicUrl" => urldecode($result));
												break;
											}
										}
									}
								}catch (Exception $e) {

									}
							}
								return $music;
							}

                        $replyMusic=getMusicInfo($musicName);
                        $msgType = "music";
                        $replyMusic = sprintf(
               		 		$musicTpl,
                         $fromUsername,
                         $toUsername,
                         $time,
                         $msgType,
                         $replyMusic['Title'],
                         $replyMusic['Description'],
                         $replyMusic['MusicUrl'],
                         $replyMusic['HQMusicUrl']);
                        echo $replyMusic;
                        exit;
                    }
                    //在线点歌功能
                    
                    //机器人功能
                    $keyword1=$form_Content;
                    $str_iden = mb_substr($keyword1,0,2,"UTF-8");
                    $chatContent = mb_substr($keyword1,2,220,"UTF-8");
                    if($str_iden=='小九'&&!empty($chatContent)){
                        function xiaojo($keyword){

        					$curlPost=array("chat"=>$keyword);
        					$ch = curl_init();//初始化curl
        					curl_setopt($ch, CURLOPT_URL,'http://www.xiaojo.com/bot/chata.php');//抓取指定网页
        					curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        					curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        					curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        					curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        					$data = curl_exec($ch);//运行curl
        					curl_close($ch);
        					if(!empty($data)&&$data!='deletedanswer'){
            					return $data;
        					}else{
            					$ran=rand(1,5);
            					switch($ran){
                					case 1:
                    					return "小九今天累了，明天再陪你聊天吧。";
                    					break;
                					case 2:
                    					return "小九睡觉喽~~";
                    					break;
                					case 3:
                    					return "呼呼~~呼呼~~";
                    					break;
                					case 4:
                    					return "你话好多啊，不跟你聊了";
                    					break;
                					case 5:
                    					return "感谢您关注【考研经验】"."\n"."微信号：hopeIcanhelpu"."\n"."考研经验，与你同行";
                    					break;
                					default:
                    					return "感谢您关注【考研经验】"."\n"."微信号：hopeIcanhelpu"."\n"."考研经验，与你同行";
                    					break;
            					}
        					}
    					}
                        $replyContent=xiaojo($chatContent);
                        $msgType = "text";
                        $replyContent = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $replyContent);
                        echo $replyContent;
                        exit;
                    }
                    //机器人功能
                   
                    //翻译功能
                    $keyword=$form_Content;
                    $str_trans = mb_substr($keyword,0,2,"UTF-8");
                    $str_valid = mb_substr($keyword,0,-2,"UTF-8");
                    if($str_trans=='翻译'&&!empty($str_valid)){
                        $word = mb_substr($keyword,2,220,"UTF-8");
                         function youdaoDic($word){

        					$keyfrom = "wangpeng";    //申请APIKEY 时所填表的网站名称的内容
        					$apikey = "377309937";  //从有道申请的APIKEY
        
        					//有道翻译-xml格式
        					$url_youdao = 'http://fanyi.youdao.com/fanyiapi.do?keyfrom='.$keyfrom.'&key='.$apikey.'&type=data&doctype=xml&version=1.1&q='.$word;
        
        					$xmlStyle = simplexml_load_file($url_youdao);
        
        					$errorCode = $xmlStyle->errorCode;

        					$paras = $xmlStyle->translation->paragraph;

        					if($errorCode == 0){
            					return $paras;
        					}else{
            					return "无法进行有效的翻译";
        					}
					}
                        
                        $contentStr = youdaoDic($word);
                        $msgType = "text";
                        $contentStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $contentStr;
                        exit;
                   }
					
                    //翻译功能
               

                    //查询火车车次
                    
              //回复英语
              	
                    //新的
                  if($form_Content=="1"){
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //英语详情数组  
                  $return_arr=array(
                  	array(
                        "考研英语1",
                        "http://weapon.qiniudn.com/20130206174851865444.png",
                        "http://www.360doc.com/content/13/1103/23/2754240_326436974.shtml"
                        ),
                  	array(
                        "410分考上，英语82分教你如何应对考研英语",
                        "http://weapon.qiniudn.com/1-1304191612424H.jpg",
                        "http://tieba.baidu.com/p/2877986433"
                        ),
                  	array(
                        "410分考上北大，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/2010112909424323617.jpg",
                        "http://www.weixin.fm"
                        ),
                  	array(
                        "410分考上人大，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/word.jpg",
                        "http://www.weixin.fm"
                        ),
                  	array(
                        "410分考上复旦，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/QQ%E6%88%AA%E5%9B%BE20140501142353.png",
                        "http://www.weixin.fm"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //新的
                    if($form_Content=="2"){
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //英语详情数组  
                  $return_arr=array(
                  	array(
                        "考研英语2",
                        "http://weapon.qiniudn.com/20130206174851865444.png",
                        "http://www.360doc.com/content/13/1103/23/2754240_326436974.shtml"
                        ),
                  	array(
                        "410分考上，英语82分教你如何应对考研英语",
                        "http://weapon.qiniudn.com/1-1304191612424H.jpg",
                        "http://tieba.baidu.com/p/2877986433"
                        ),
                  	array(
                        "410分考上北大，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/2010112909424323617.jpg",
                        "http://www.weixin.fm"
                        ),
                  	array(
                        "410分考上人大，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/word.jpg",
                        "http://www.weixin.fm"
                        ),
                  	array(
                        "410分考上复旦，英语82分学长教你如何应对考研英语",
                        "http://weapon.qiniudn.com/QQ%E6%88%AA%E5%9B%BE20140501142353.png",
                        "http://www.weixin.fm"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                    //新的
                }
                    //回复数学
                    if($form_Content=="3")
                {
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //数学详情数组  
                  $return_arr=array(
                  	array(
                        "考研数学，点击进入考研数学论坛",
                        "http://weapon.qiniudn.com/math.jpg",
                        "http://shuxue.bbs.kaoyan.com/"
                        ),
                  	array(
                        "考研数学一140+经验贴",
                        "http://weapon.qiniudn.com/math1.jpg",
                        "http://page.renren.com/601410394/note/852613367?op=next&curTime=1339597506000"
                        ),
                  	array(
                        "考研数学满分复习经验",
                        "http://weapon.qiniudn.com/math2.jpg",
                        "http://bbs.pinggu.org/thread-821900-1-1.html"
                        ),
                  	array(
                        "考研数学历年真题考点分值分布统计，后期复习可参考，做到有的放矢",
                        "http://weapon.qiniudn.com/math3.png",
                        "http://wenku.baidu.com/link?url=SSYdBhMf6o9Iwmc_9jsiu_r51uSL7qGnSI5nz798QUxhoarmi-RNNFnkgACDFtgcWlChi5260ZgL9sIir2qLKTxex0vETek38oPo-lsIpO_"
                        ),
                  	array(
                        "考研数学，要基础，更要好心态",
                        "http://weapon.qiniudn.com/lizhi.jpeg",
                        "http://yz.chsi.com.cn/kyzx/math/201007/20100723/111350001.html"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //数学
                    //数学2
                     if($form_Content=="4")
                {
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //数学详情数组  
                  $return_arr=array(
                  	array(
                        "考研数学，点击进入考研数学论坛",
                        "http://weapon.qiniudn.com/math.jpg",
                        "http://shuxue.bbs.kaoyan.com/"
                        ),
                  	array(
                        "考研数学二140+经验贴",
                        "http://weapon.qiniudn.com/math1.jpg",
                        "http://page.renren.com/601410394/note/852613367?op=next&curTime=1339597506000"
                        ),
                  	array(
                        "考研数学满分复习经验",
                        "http://weapon.qiniudn.com/math2.jpg",
                        "http://bbs.pinggu.org/thread-821900-1-1.html"
                        ),
                  	array(
                        "考研数学历年真题考点分值分布统计，后期复习可参考，做到有的放矢",
                        "http://weapon.qiniudn.com/math3.png",
                        "http://wenku.baidu.com/link?url=SSYdBhMf6o9Iwmc_9jsiu_r51uSL7qGnSI5nz798QUxhoarmi-RNNFnkgACDFtgcWlChi5260ZgL9sIir2qLKTxex0vETek38oPo-lsIpO_"
                        ),
                  	array(
                        "考研数学，要基础，更要好心态",
                        "http://weapon.qiniudn.com/lizhi.jpeg",
                        "http://yz.chsi.com.cn/kyzx/math/201007/20100723/111350001.html"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //数学2
                    //数学三
                     if($form_Content=="5")
                {
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //数学详情数组  
                  $return_arr=array(
                  	array(
                        "考研数学，点击进入考研数学论坛",
                        "http://weapon.qiniudn.com/math.jpg",
                        "http://shuxue.bbs.kaoyan.com/"
                        ),
                  	array(
                        "考研数学三140+经验贴",
                        "http://weapon.qiniudn.com/math1.jpg",
                        "http://page.renren.com/601410394/note/852613367?op=next&curTime=1339597506000"
                        ),
                  	array(
                        "考研数学满分复习经验",
                        "http://weapon.qiniudn.com/math2.jpg",
                        "http://bbs.pinggu.org/thread-821900-1-1.html"
                        ),
                  	array(
                        "考研数学历年真题考点分值分布统计，后期复习可参考，做到有的放矢",
                        "http://weapon.qiniudn.com/math3.png",
                        "http://wenku.baidu.com/link?url=SSYdBhMf6o9Iwmc_9jsiu_r51uSL7qGnSI5nz798QUxhoarmi-RNNFnkgACDFtgcWlChi5260ZgL9sIir2qLKTxex0vETek38oPo-lsIpO_"
                        ),
                  	array(
                        "考研数学，要基础，更要好心态",
                        "http://weapon.qiniudn.com/lizhi.jpeg",
                        "http://yz.chsi.com.cn/kyzx/math/201007/20100723/111350001.html"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //数学三
                    //政治
                    if($form_Content=="6")
                {
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //数学详情数组  
                  $return_arr=array(
                  	array(
                        "考研政治，点击进入考研政治论坛",
                        "http://weapon.qiniudn.com/math.jpg",
                        "http://shuxue.bbs.kaoyan.com/"
                        ),
                  	array(
                        "考研数学三140+经验贴",
                        "http://weapon.qiniudn.com/math1.jpg",
                        "http://page.renren.com/601410394/note/852613367?op=next&curTime=1339597506000"
                        ),
                  	array(
                        "考研数学满分复习经验",
                        "http://weapon.qiniudn.com/math2.jpg",
                        "http://bbs.pinggu.org/thread-821900-1-1.html"
                        ),
                  	array(
                        "考研数学历年真题考点分值分布统计，后期复习可参考，做到有的放矢",
                        "http://weapon.qiniudn.com/math3.png",
                        "http://wenku.baidu.com/link?url=SSYdBhMf6o9Iwmc_9jsiu_r51uSL7qGnSI5nz798QUxhoarmi-RNNFnkgACDFtgcWlChi5260ZgL9sIir2qLKTxex0vETek38oPo-lsIpO_"
                        ),
                  	array(
                        "考研数学，要基础，更要好心态",
                        "http://weapon.qiniudn.com/lizhi.jpeg",
                        "http://yz.chsi.com.cn/kyzx/math/201007/20100723/111350001.html"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //政治
                    //专业课
                    if($form_Content=="7")
                {
                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>5</ArticleCount>\n
                  <Articles>\n";                  
                  //数学详情数组  
                  $return_arr=array(
                  	array(
                        "找到你要考的专业课",
                        "http://weapon.qiniudn.com/math.jpg",
                        "http://shuxue.bbs.kaoyan.com/"
                        ),
                  	array(
                        "考研数学三140+经验贴",
                        "http://weapon.qiniudn.com/math1.jpg",
                        "http://page.renren.com/601410394/note/852613367?op=next&curTime=1339597506000"
                        ),
                  	array(
                        "考研数学满分复习经验",
                        "http://weapon.qiniudn.com/math2.jpg",
                        "http://bbs.pinggu.org/thread-821900-1-1.html"
                        ),
                  	array(
                        "考研数学历年真题考点分值分布统计，后期复习可参考，做到有的放矢",
                        "http://weapon.qiniudn.com/math3.png",
                        "http://wenku.baidu.com/link?url=SSYdBhMf6o9Iwmc_9jsiu_r51uSL7qGnSI5nz798QUxhoarmi-RNNFnkgACDFtgcWlChi5260ZgL9sIir2qLKTxex0vETek38oPo-lsIpO_"
                        ),
                  	array(
                        "考研数学，要基础，更要好心态",
                        "http://weapon.qiniudn.com/lizhi.jpeg",
                        "http://yz.chsi.com.cn/kyzx/math/201007/20100723/111350001.html"
                        )
                  
                  );
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value[0]."]]></Title> \n
                    <Description><![CDATA[]]></Description>\n
                    <PicUrl><![CDATA[".$value[1]."]]></PicUrl>\n
                    <Url><![CDATA[".$value[2]."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                  echo $resultStr;
                  exit;
                }
                    //专业课
                    
                   
            //用户输入表情回复音乐“最炫民族风”
               if($form_Content=="/::)")
              {
              	
	        	$msgType = "music";
                $resultStr = sprintf(
               		 $musicTpl, 
                         $fromUsername, 
                         $toUsername, 
                         $time, 
                         $msgType, 
                         "最炫民族风",
                         "凤凰传奇",
                         "http://weapon.qiniudn.com/xuan1.mp3",
                         "http://weapon.qiniudn.com/xuan.mp3");
                echo $resultStr;
                exit;
							            
              }
              //默认回复
                $return_Str="请输入相应字母获得帮助：\n\n";
                $return_Arr=array("1.英语一\n","2.英语二\n","3.数学一\n","4.数学二\n","5.数学三\n","6.政治\n","7.找到你的专业课\n\n","回复小九+你想说的话，可跟我们家的小九聊天哦，考研累，欢乐一下，小九还是不够聪明，每次聊天都要在前面先加上小九哦\n\n","回复翻译+你想翻译的内容（单词或句子），可获得翻译功能，例如“翻译我爱你”\n\n","回复自己的位置信息可查看当地天气,注意是您右下角那个加号里的位置，发送过来就好啦\n\n","回复/::)可听最炫民族风歌曲\n\n","回复百科加上你想搜索的内容可查看词条最基本的解释\n\n","拒绝迷信，欢迎娱乐，发送每日宜忌即可查看今天的宜忌\n\n","发送“世界杯赛程”五个汉字即可查看2014巴西世界杯完整赛程");
                $return_Str.=implode("",$return_Arr);
                $msgType = "text";
                $result_Str = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $return_Str);
                echo $result_Str;
                exit;                                   
            }
            //否则提示输入
            else
                
            {
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "请输入些什么吧……");
                echo $resultStr;
                exit;                                   
            }          
          }
          
    	//事件消息
          if($form_MsgType=="event")
          {
            //获取事件类型
            $form_Event = $postObj->Event;
            //订阅事件
            if($form_Event=="subscribe")
            {
    		  //回复欢迎图文菜单，这儿要自己写一个word文件传到云上，写自己的经验。
              $resultStr="<xml>\n
              <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
              <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
              <CreateTime>".time()."</CreateTime>\n
              <MsgType><![CDATA[news]]></MsgType>\n
              <ArticleCount>5</ArticleCount>\n
              <Articles>\n";
              
              //添加封面图文消息
              $resultStr.="<item>\n
              <Title><![CDATA[考研路上我们一起，我的考研之路]]></Title> \n
              <Description><![CDATA[]]></Description>\n
              <PicUrl><![CDATA[http://weapon.qiniudn.com/yiqi.jpg]]></PicUrl>\n
              <Url><![CDATA[http://weapon.qiniudn.com/ky.doc]]></Url>\n
              </item>\n";
              
              //添加4条列表图文消息
              $resultStr.="<item>\n
              <Title><![CDATA[考研要知道的基本问题]]></Title> \n
              <Description><![CDATA[]]></Description>\n
              <PicUrl><![CDATA[http://weapon.qiniudn.com/zhunbei.jpg]]></PicUrl>\n
              <Url><![CDATA[http://wenku.baidu.com/view/c699fe287375a417866f8fb1.html]]></Url>\n
              </item>\n";
              
              $resultStr.="<item>\n
              <Title><![CDATA[怎么选择你的研究生学校]]></Title> \n
              <Description><![CDATA[]]></Description>\n
              <PicUrl><![CDATA[http://weapon.qiniudn.com/choose.jpg]]></PicUrl>\n
              <Url><![CDATA[http://www.311jiaoyuxue.com/zb/2012/0605/226.html]]></Url>\n
              </item>\n";
              
              $resultStr.="<item>\n
              <Title><![CDATA[分享一些考研过程中遇到的问题，教你如何应对]]></Title> \n
              <Description><![CDATA[]]></Description>\n
              <PicUrl><![CDATA[http://weapon.qiniudn.com/solution.jpg]]></PicUrl>\n
              <Url><![CDATA[http://wenku.baidu.com/link?url=G3300Es0txlMTRgNS4hpZM95wCYe8iib8O0viHJbuBrTjocPhMliv6dCVB8edZDJKq8OaP3y0BBUA7uJ6A9S8r2RSnobPfHfcy1fCHGGTOy]]></Url>\n
              </item>\n";
              
              $resultStr.="<item>\n
              <Title><![CDATA[怎么使用考研经验]]></Title> \n
              <Description><![CDATA[]]></Description>\n
              <PicUrl><![CDATA[http://weapon.qiniudn.com/use.jpg]]></PicUrl>\n
              <Url><![CDATA[http://weapon.qiniudn.com/use1.doc]]></Url>\n
              </item>\n";
              
              $resultStr.="</Articles>\n
              <FuncFlag>0</FuncFlag>\n
              </xml>";

              
              //回复欢迎文字消息
              
                //$msgType = "text";
                //$contentStr = "感谢您关注考研经验公共号！[愉快]\n有什么问题可直接输入，竭诚为你答疑！[玫瑰]";
                //$resultStr1 = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $contentStr);
                 
              echo $resultStr;
                //echo $resultStr1;
              exit;
            }
          
          }
          
  }
  else 
  {
          echo "";
          exit;
  }

?>