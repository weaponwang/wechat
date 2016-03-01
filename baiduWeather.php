<?php
/*
 * Created on 2014-5-6
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function getWeatherInfo($cityName)
{
    if ($cityName == "" || (strstr($cityName, "+"))){
        return "发送天气+城市，例如'天气深圳'";
    }
    $url = "http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($cityName)."&output=json&ak=CF46dc79e801fc11d34c11c68930de7d";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($output, true);
    if ($result["error"] != 0){
        return $result["status"];
    }
    $curHour = (int)date('H',time());
    $weather = $result["results"][0];
    $weatherArray[] = array("Title" =>$weather['currentCity']."天气预报", "Description" =>"", "PicUrl" =>"", "Url" =>"");
    for ($i = 0; $i < count($weather["weather_data"]); $i++) {
        $weatherArray[] = array("Title"=>
            $weather["weather_data"][$i]["date"]."\n".
            $weather["weather_data"][$i]["weather"]." ".
            $weather["weather_data"][$i]["wind"]." ".
            $weather["weather_data"][$i]["temperature"],
        "Description"=>"",
        "PicUrl"=>(($curHour >= 6) && ($curHour < 18))?$weather["weather_data"][$i]["dayPictureUrl"]:$weather["weather_data"][$i]["nightPictureUrl"], "Url"=>"");
    }
    return $weatherArray;
}
?>
