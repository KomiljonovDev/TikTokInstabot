<?php
	if ($_REQUEST['sendMessage']) {
		$sendConfirm = file_get_contents('helpers/send_confirm.txt');
		if ($sendConfirm == 'send') {
			$start = file_get_contents('helpers/send_start.txt');
			$sendMessageCheck_type = json_decode(file_get_contents('config/json/check_type.json'));
			$extra = " AND (";

			if($sendMessageCheck_type->uz == true){
				$extra .= "(lang='uz') OR";
			}

			if ($sendMessageCheck_type->eng == true) {
			 	$extra .= " (lang='eng') OR";
		 	}

			if($sendMessageCheck_type->ru == true) {
				$extra .= " (lang='ru') OR";
			}
			
			if ($sendMessageCheck_type->not_selected == true) {
				$extra .= " (lang='') OR";
			}

			if ($sendMessageCheck_type->group == true) {
			 	$extra .= " (chat_type='group')";
			}
			$extra .= ")";
			$end = $start + '60';
			$users = $db->selectWhere('users',[
				[
					'id'=>$start,
					'cn'=>'>='
				]
			], " AND id<='" . $end . "'" . $extra);
			$sendMessagetype = json_decode(file_get_contents('config/json/sendMessage.json'));
			if ($users->num_rows) {
				if ($sendMessagetype->message_id) {
					if (!is_null($sendMessagetype->reply_markup)) {
						foreach ($users as $key => $user) {
							bot('copyMessage',[
			    				'chat_id'=>$user['fromid'],
			    				'from_chat_id'=>$sendMessagetype->from_chat_id,
			    				'message_id'=>$sendMessagetype->message_id,
			    				'reply_markup'=>json_encode($sendMessagetype->reply_markup)
			    			]);
						}
					}else{
						foreach ($users as $key => $user) {
							bot('copyMessage',[
			    				'chat_id'=>$user['fromid'],
			    				'from_chat_id'=>$sendMessagetype->from_chat_id,
			    				'message_id'=>$sendMessagetype->message_id,
			    			]);
						}
					}
				}
				file_put_contents('helpers/send_start.txt', $end);
			}else{
				$sendAdsById = json_decode(file_get_contents('config/json/sendAdsById.json'));
				$users = $db->selectWhere('users',[
	                'id'=>0,
	                'cn'=>'>'
	            ]);
	            $only_users = 0;
	            $active_users = 0;
	            $only_groups = 0;
	            $active_groups = 0;
	            foreach ($users as $key => $value) {
	                if ($value['chat_type'] == 'private') {
	                    $only_users+=1;
	                    if ($value['del']=='0') {
	                        $active_users+=1;
	                    }
	                }else{
	                    $only_groups+=1;
	                    if ($value['del']=='0') {
	                        $active_groups+=1;
	                    }
	                }
	            }
				bot('sendMessage',[
					'chat_id'=>$sendMessagetype->from_chat_id,
					'text'=>'<b><a href="tg://user?id=' . $sendAdsById->fromid . '">Admin</a> tomonidan ' . date('Y-m-d H:i') . ' da yuburilgan reklama yakunlandi.</b>',
					'parse_mode'=>'html'
				]);
	            bot('sendMessage',[
	                'chat_id'=>$fromid,
	                'text'=>"Bot statistikasi:\n\nGuruh va userlar: " . $users->num_rows  . "ta\nBarcha userlar: " . $only_users . "ta\nActive userlar: " . $active_users . "ta\nBarcha Guruhlar: " . $only_groups . "ta\nActive Guruhlar: " . $active_groups . "ta",
	                'reply_markup'=>$home_keyboard,
	            ]);
				$sendMessageCheck_type->uz = true;
				$sendMessageCheck_type->ru = true;
				$sendMessageCheck_type->eng = true;
				$sendMessageCheck_type->not_selected = true;
				$sendMessageCheck_type->group = true;

				file_put_contents('config/json/sendAdsById.json', '');
				file_put_contents('helpers/admin/json/' . $sendAdsById->fromid . '.json', '');
				file_put_contents('helpers/send_start.txt', '0');
				file_put_contents('config/json/sendMessage.json', '');
				file_put_contents('config/json/check_type.json', json_encode($sendMessageCheck_type));
			}
		}
	}
?>