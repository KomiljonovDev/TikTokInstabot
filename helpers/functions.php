<?php
    function bot($method, $datas=[]){
        $url = "https://api.telegram.org/bot".API_KEY."/".$method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }else{
            return json_decode($res);
        }
    }
    function html($tx){
        return str_replace(['<','>'],['&#60;','&#62;'],$tx);
    }
    function myUser($dbColumns=[],$myUser=[]) {
        global $db;
        $user = $db->selectWhere('users',[
            [
                'fromid'=>$myUser[0],
                'cn'=>'='
            ],
        ]);
        if ($user->num_rows) {
            if (mysqli_fetch_assoc($user)['del'] == '1') {
                $db->update('users',[
                    'del'=>0,
                ],[
                    'fromid'=>$myUser[0],
                    'cn'=>'='
                ]);
            }
            return 1;
        }
        $dbInsert = [];
        foreach ($dbColumns as $key => $value) {
            $dbInsert[$dbColumns[$key]] = $myUser[$key];
        }
        $db->insertInto('users',$dbInsert);
    }
    function lang($fromid) {
        global $db;
        $lang_json = json_decode(file_get_contents('config/json/lang.json'));
        $user = $db->selectWhere('users',[
            [
                'fromid'=>$fromid,
                'cn'=>'='
            ],
        ]);
        if ($user->num_rows) {
            $user_data = mysqli_fetch_assoc($user);
            switch ($user_data[lang]) {
                case 'uz':
                    return $lang_json->uz;
                    break;
                case 'eng':
                    return $lang_json->eng;
                    break;
                case 'ru':
                    return $lang_json->ru;
                    break;
            }
            return $lang_json->eng;
        }else{
            return $lang_json->eng;
        }
    }
    function channel($fromid) {
        global $db,$text,$data,$admin,$mid,$miid;
        $status = mysqli_fetch_assoc($db->selectWhere('channels',[
            [
                'name'=>"status",
                'cn'=>'='
            ],
        ]));
        if ($status["object"] == "on") {
            $channels = $db->selectWhere('channels',[
                [
                    'name'=>"channel",
                    'cn'=>'='
                ],
            ]);
            if ($num = $channels->num_rows) {
                $res = 0;
                foreach ($channels as $key => $value) {
                    $id = $value['object'];
                    $getchatadmin = bot('getChatMember',[
                        'chat_id'=>$id,
                        'user_id'=>$fromid
                    ]);
                    $status = $getchatadmin->result->status;
                    if ($status == "administrator" or $status == "creator" or $status == "member") {
                        $res++;
                    }
                }
                if ($res == $num) {
                    return true;
                }else{
                    foreach ($channels as $key => $value) {
                        $id = $value['object'];
                        $getchat = bot('getChat',[
                            'chat_id'=>$id
                        ]);
                        $title = $getchat->result->title;
                        $link = $getchat->result->invite_link;
                        if (!empty($link)) {
                            $keyy[]=['text'=>"➕ Obuna boʻlish", 'url'=>"$link"];
                        }
                    }
                    $keyy[] = ['callback_data'=>"res", 'text'=>"✅ Tasdiqlash"];
                    $key = array_chunk($keyy, 1);
                    $admins = json_decode(file_get_contents('config/json/admins.json'));
                    if (!in_array($fromid, $admins)) {
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>"<b>❗️Botdan foydalanishni davom ettirish uchun quyidagi kanalimizga obuna bo'ling👇🏼</b>",
                            'parse_mode'=>'html',
                            'reply_to_message_id'=>$miid,
                            'reply_markup'=>json_encode([
                                'inline_keyboard'=>$key
                            ]),
                        ]);
                    }
                    if (!is_null($mid)) {
                        bot('editMessageText',[
                            'chat_id'=>$fromid,
                            'text'=>"<b>❗️Botdan foydalanishni davom ettirish uchun quyidagi kanalimizga obuna bo'ling👇🏼</b>",
                            'parse_mode'=>'html',
                            'message_id'=>$mid,
                            'reply_markup'=>json_encode([
                                'inline_keyboard'=>$key
                            ]),
                        ]);
                    }
                    return false;
                }
            }
        }
        return true;
    }
    function getVideo($url,$rand=null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://okdeveloper.uz//okdeveloper/API/alldownloader/down.php?url=" . $url . "&fileName=" . $rand);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }
    function tiktok($url) {
        header('Content-Type: application/json; charset=utf-8');
        $ch = curl_init();
        $header = array();
        $header[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36';
        $header[] = 'referer: https://lovetik.com/';
        $header[] = 'origin: https://lovetik.com';
        curl_setopt($ch, CURLOPT_URL, 'https://lovetik.com/api/ajax/search');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('query' => $url, "lang"=>"en"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
?>