<?php
    require('./config/config.php');
    $db = new dbmysqli;
    $db->dbConnect();
    date_default_timezone_set('Asia/Tashkent');
    define('API_KEY', "");
    require './helpers/functions.php';
    $update = json_decode(file_get_contents('php://input'));
    if (!is_null($update)) {
        if (!is_null($update->message)) {
            $message = $update->message;
            $chat_id = $message->chat->id;
            $type = $message->chat->type;
            $miid =$message->message_id;
            $name = $message->from->first_name;
            $lname = $message->from->last_name;
            $full_name = $name . " " . $lname;
            $full_name = html($full_name);
            $user = $message->from->username ?? '';
            $fromid = $message->from->id;
            $text = html($message->text);
            $title = $message->chat->title;
            $chatuser = $message->chat->username;
            $chatuser = $chatuser ? $chatuser : "Shaxsiy Guruh!";
            $caption = $message->caption;
            $entities = $message->entities;
            $entities = $entities[0];
            $left_chat_member = $message->left_chat_member;
            $new_chat_member = $message->new_chat_member;
            $photo = $message->photo;
            $video = $message->video;
            $audio = $message->audio;
            $voice = $message->voice;
            $reply = $message->reply_markup;
            $fchat_id = $message->forward_from_chat->id;
            $fid = $message->forward_from_message_id;
        }else if(!is_null($update->callback_query)){
            $callback = $update->callback_query;
            $qid = $callback->id;
            $mes = $callback->message;
            $mid = $mes->message_id;
            $cmtx = $mes->text;
            $cid = $callback->message->chat->id;
            $ctype = $callback->message->chat->type;
            $cbid = $callback->from->id;
            $cbuser = $callback->from->username;
            $data = $callback->data;
        }
    }
    $home_keyboard = json_encode([
        'inline_keyboard'=>[
            [['text'=>"➕ GURUHGA QO'SHISH",'url'=>"https://t.me/TikTokInstabot?startgroup=new"]],
        ],
    ]);
    if (!is_null($update)) {
        if (!is_null($update->message)) {
            $user_lang = lang($fromid);
            if ($type == 'private') {
                if ($text == '/start') {
                    $myUser = myUser(['fromid','name','user','chat_type','lang','del','created_at'],[$fromid,$full_name,$user,'private','',0,strtotime('now')]);
                    // if (channel($fromid)) {
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>"<b>Assalom aleykum!👋🏻\n\nTik Tok🔥\nInstagram\nVideo yuklashim uchun menga quyidagilardan birini yuboring:\n\n• Tik Tokdan Link\n• Instagram post Link</b>",
                            'reply_markup'=>$home_keyboard,
                            'parse_mode'=>'html',
                        ]);
                    // }
                }else if (channel($fromid)) {
                    if ($text == "/lang") {
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>"🇺🇿 Tilni tanlang:",
                            'reply_markup'=>json_encode([
                                'inline_keyboard'=>[
                                    [['text'=>'🇺🇿 O\'zbekcha','callback_data'=>'lang_uz']],
                                    [['text'=>'🇺🇸 English','callback_data'=>'lang_eng']],
                                    [['text'=>'🇷🇺 Русский','callback_data'=>'lang_ru']],
                                ],
                            ]),
                        ]);
                    }else if(mb_stripos($text, "tiktok")!==false){
                            bot('sendMessage',[
                                'chat_id'=>$fromid,
                                'text'=>"⏳ Iltimos biroz kuting..."
                            ]);
                            $get = file_get_contents("https://u1775.xvest4.ru/API/TikTok/index.php?url=" . trim($text));
                            $get = json_decode($get);
                            bot('deleteMessage',[
                                'chat_id'=>$fromid,
                                'message_id'=>$miid+1
                            ]);
                            $send = bot('sendDocument',[
                                'chat_id'=>$fromid,
                                'document'=>$get->result->download,
                                'caption'=>"<b>" . $text . "\n\n📥 @TikTokInstabot</b>",
                                'parse_mode'=>'html'
                            ]);
                            if (!$send->ok) {
                                bot('sendMessage',[
                                    'chat_id'=>$fromid,
                                    'text'=>"<b>Kechirasiz, yuklab olishning imkoni bo'lmadi.\nKo'rib chiqing:</b><i>\nTo'g'ri link yuborgan ekanligingizga ishonch hosil qiling, multimedianing hajmi 50 MB yuqori bo'lmasligi zarur!</i>\n\n",
                                    'parse_mode'=>'html'
                                ]);
                            }
                    }else if((mb_stripos($text, "instagram")!==false) || (mb_stripos($text, "insta.com")!==false)){
                        bot('sendMessage',[
                            'chat_id'=>$fromid,
                            'text'=>"⏳ Iltimos biroz kuting..."
                        ]);
                        $result = getVideo(trim($text), rand(time(),10000));
                        $j = 0;
                        foreach ($result as $key => $value) {
                            if (isset($value->url)) {
                                if(isset($value->meta->title)) $url = $value->url[0]->url;
                                if(isset($value->meta->title)) $title = $value->meta->title;
                                if ($value->url[0]->type == "mp4") {
                                    $data_type = "video";
                                }else{
                                    $data_type = "photo";
                                }
                                $datas[] = ['type'=>$data_type,'media'=>trim($url)];
                                // echo "bir-nechtta media ";
                            }else{
                                $j ++;
                                if ($j > 1) break;
                                if(isset($result->url[0]->url)) $url = $result->url[0]->url;
                                if(isset($result->meta->title)) $title = $result->meta->title;
                                if ($result->url[0]->type == "mp4") {
                                    $data_type = "video";
                                }else{
                                    $data_type = "photo";
                                }
                                $datas[] = ['type'=>$data_type,'media'=>trim($url)];
                            }
                        }
                        bot('deleteMessage',[
                                'chat_id'=>$fromid,
                                'message_id'=>$miid+1
                            ]);
                        $send = bot('sendMediaGroup',[
                            'chat_id'=>$fromid,
                            'media'=>json_encode($datas),
                            'reply_to_message_id'=>$miid
                        ]);
                        $title = $title ? $title : "";
                        $title = (strlen($title) < 960 ? $title : (substr(utf8_encode($title), 0,960) . " ..."));
                        bot('editMessageCaption',[
                            'chat_id'=>$fromid,
                            'message_id'=>($miid + 2),
                            'caption'=>"<b>" . html($title) . "\n\n📥 @TikTokInstabot</b>",
                            'parse_mode'=>'html'
                        ]);
                        if ($send->ok) {
                        }else{
                            $send = bot('sendMediaGroup',[
                                'chat_id'=>$fromid,
                                'media'=>json_encode($datas),
                                'reply_to_message_id'=>$miid
                            ]);
                            
                            bot('editMessageCaption',[
                                'chat_id'=>$fromid,
                                'message_id'=>($miid + 2),
                                'caption'=>"<b>" . html($title) . "\n\n📥 @TikTokInstabot</b>",
                                'parse_mode'=>'html'
                            ]);
                            if (!$send->ok) {
                                bot('sendMessage',[
                                    'chat_id'=>$fromid,
                                    'text'=>"<b>Kechirasiz, yuklab olishning imkoni bo'lmadi.\nKo'rib chiqing:</b><i>\nTo'g'ri link yuborgan ekanligingizga ishonch hosil qiling, multimedianing hajmi 50 MB yuqori bo'lmasligi zarur!</i>\n\n",
                                    'parse_mode'=>'html'
                                ]);
                            }
                        }
                    }
                }
            }else{
                if ($text == "/start") {
                    myUser(['fromid','name','user','chat_type','lang','del','created_at'],[$chat_id,$title,$chatuser,'group','',0,strtotime('now')]);
                    bot('sendMessage',[
                        'chat_id'=>$fromid,
                        'text'=>"<b>Assalom aleykum!👋🏻\n\nTik Tok🔥\nInstagram\nVideo yuklashim uchun menga quyidagilardan birini yuboring:\n\n• Tik Tokdan Link\n• Instagram post Link</b>",
                        'reply_markup'=>$home_keyboard,
                        'parse_mode'=>'html',
                    ]);
                }else if(mb_stripos($text, "tiktok")!==false){
                            bot('sendMessage',[
                                'chat_id'=>$chat_id,
                                'text'=>"⏳ Iltimos biroz kuting..."
                            ]);
                            $get = file_get_contents("https://u1775.xvest4.ru/API/TikTok/index.php?url=" . trim($text));
                            $get = json_decode($get);
                            bot('deleteMessage',[
                                'chat_id'=>$chat_id,
                                'message_id'=>$miid+1
                            ]);
                            $send = bot('sendDocument',[
                                'chat_id'=>$chat_id,
                                'document'=>$get->result->download,
                                'caption'=>"<b>" . $text . "\n\n📥 @TikTokInstabot</b>",
                                'parse_mode'=>'html'
                            ]);
                            if (!$send->ok) {
                                bot('sendMessage',[
                                    'chat_id'=>$chat_id,
                                    'text'=>"<b>Kechirasiz, yuklab olishning imkoni bo'lmadi.\nKo'rib chiqing:</b><i>\nTo'g'ri link yuborgan ekanligingizga ishonch hosil qiling, multimedianing hajmi 50 MB yuqori bo'lmasligi zarur!</i>\n\n",
                                    'parse_mode'=>'html'
                                ]);
                            }
                    }else if((mb_stripos($text, "instagram")!==false) || (mb_stripos($text, "insta.com")!==false)){
                        bot('sendMessage',[
                            'chat_id'=>$chat_id,
                            'text'=>"⏳ Iltimos biroz kuting..."
                        ]);
                        $result = getVideo(trim($text), rand(time(),10000));
                        $j = 0;
                        foreach ($result as $key => $value) {
                            if (isset($value->url)) {
                                if(isset($value->meta->title)) $url = $value->url[0]->url;
                                if(isset($value->meta->title)) $title = $value->meta->title;
                                if ($value->url[0]->type == "mp4") {
                                    $data_type = "video";
                                }else{
                                    $data_type = "photo";
                                }
                                $datas[] = ['type'=>$data_type,'media'=>trim($url)];
                                // echo "bir-nechtta media ";
                            }else{
                                $j ++;
                                if ($j > 1) break;
                                if(isset($result->url[0]->url)) $url = $result->url[0]->url;
                                if(isset($result->meta->title)) $title = $result->meta->title;
                                if ($result->url[0]->type == "mp4") {
                                    $data_type = "video";
                                }else{
                                    $data_type = "photo";
                                }
                                $datas[] = ['type'=>$data_type,'media'=>trim($url)];
                            }
                        }
                        bot('deleteMessage',[
                            'chat_id'=>$chat_id,
                            'message_id'=>$miid+1
                        ]);
                        $send = bot('sendMediaGroup',[
                            'chat_id'=>$chat_id,
                            'media'=>json_encode($datas),
                            'reply_to_message_id'=>$miid
                        ]);
                        $title = $title ? $title : "";
                        $title = (strlen($title) < 960 ? $title : (substr(utf8_encode($title), 0,960) . " ..."));
                        bot('editMessageCaption',[
                            'chat_id'=>$chat_id,
                            'message_id'=>($miid + 2),
                            'caption'=>"<b>" . html($title) . "\n\n📥 @TikTokInstabot</b>",
                            'parse_mode'=>'html'
                        ]);
                        if ($send->ok) {
                        }else{
                            $send = bot('sendMediaGroup',[
                                'chat_id'=>$chat_id,
                                'media'=>json_encode($datas),
                                'reply_to_message_id'=>$miid
                            ]);
                            
                            bot('editMessageCaption',[
                                'chat_id'=>$chat_id,
                                'message_id'=>($miid + 2),
                                'caption'=>"<b>" . html($title) . "\n\n📥 @TikTokInstabot</b>",
                                'parse_mode'=>'html'
                            ]);
                            if (!$send->ok) {
                                bot('sendMessage',[
                                    'chat_id'=>$chat_id,
                                    'text'=>"<b>Kechirasiz, yuklab olishning imkoni bo'lmadi.\nKo'rib chiqing:</b><i>\nTo'g'ri link yuborgan ekanligingizga ishonch hosil qiling, multimedianing hajmi 50 MB yuqori bo'lmasligi zarur!</i>\n\n",
                                    'parse_mode'=>'html'
                                ]);
                            }
                        }
                    }
            }
        }else if(!is_null($update->callback_query)){
            if (channel($cbid)) {
                if ($ctype == 'private') {
                    $user_lang = lang($cbid);
                    if ($data == 'res') {
                        bot('editMessageText',[
                            'chat_id'=>$cbid,
                            'message_id'=>$mid,
                            'text'=>$user_lang->start
                        ]);
                    }
                    if ($data == 'lang_uz') {
                        bot('editMessageText',[
                            'chat_id'=>$cbid,
                            'message_id'=>$mid,
                            'text'=>"Uzbek tili tanlandi."
                        ]);
                        $db->update('users',[
                            'lang'=>"uz",
                        ],[
                            'fromid'=>$cbid,
                            'cn'=>'='
                        ]);
                    }
                    if ($data == 'lang_eng') {
                        bot('editMessageText',[
                            'chat_id'=>$cbid,
                            'message_id'=>$mid,
                            'text'=>"English langugage is selected."
                        ]);
                        $db->update('users',[
                            'lang'=>"eng",
                        ],[
                            'fromid'=>$cbid,
                            'cn'=>'='
                        ]);
                    }
                    if ($data == 'lang_ru') {
                        bot('editMessageText',[
                            'chat_id'=>$cbid,
                            'message_id'=>$mid,
                            'text'=>"Выбран русский язык."
                        ]);
                        $db->update('users',[
                            'lang'=>"ru",
                        ],[
                            'fromid'=>$cbid,
                            'cn'=>'='
                        ]);
                    }
                }
            }
        }
    }
    include 'helpers/admin/admin.php';
    include 'helpers/sendMessage.php';
?>
