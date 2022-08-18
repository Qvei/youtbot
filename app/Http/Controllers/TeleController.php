<?php

namespace App\Http\Controllers;
use App\MyClass\YoutubeClass;
use App\MyClass\NewClass;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Telegram;
use Session;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class TeleController extends Controller
{

    public function get_data_from_tg(Request $request){

        $content = Telegram::getWebhookUpdates();
        $dat = json_decode($content, true);
        
        $send_data = $this->check_query($dat);
        $buttons = $this->add_start_buttn();
        
            switch ($send_data['message']){
                case '/start':
                    $send_data['text'] = "🌧 Погода і рівень забруднення за місцем знаходження \n 🎞️ Пошук відео в ютубі (введіть назву відео)";
                    break;
                case 'location':
                    $btn = Keyboard::button(['text' => 'Підтвердіть відправку', 'request_location' => true]);
                    $buttons = Keyboard::make(['keyboard' => [[$btn]], 'resize_keyboard' => true, 'one_time_keyboard' => true, 'hide_keyboard' => true]);
                    $send_data['text'] = 'Очікую підтвердження..'.iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F447));
                    break;
                case 'getlocation':
                    $api_answers = new NewClass($send_data['latitude'], $send_data['longitude'], env('WEATHER_KEY'));
                    $ans = $api_answers->addAqiAnsver();
                    $wear_ans = $api_answers->addWeatherAnswer();
                    $send_data['text'] = $ans."\n\n".$wear_ans;
                    break;
                case 'test':
                    $send_data['text'] = 'https://epic.gsfc.nasa.gov/archive/natural/2022/06/21/png/epic_1b_20220621102538.png';
                    break;
                case 'youtube':
                    $send_data['text'] = "\n\nhttps://www.youtube.com/watch?v=".$send_data['youtube'];
                    break;
                default:
                    $get_buttn = new YoutubeClass($send_data['message'], env('YOUTUBE_API_KEY'));
                    $buttons = $get_buttn->get_videos();
                    $send_data['text'] = 'Що є по '.$send_data['message'];
                    break;
            }
    
            return $this->sendTelegram($send_data,$buttons);
    }

   private function check_query($dat){
        if(isset($dat['callback_query'])){
            $mess = $dat['callback_query']['data'];
            if(strpos($mess, '||') !== false){
                $explod = explode('||', $mess);
                $mess = $explod[1];
                $youtube = $explod[0];
            }
            $chat_id = $dat['callback_query']['from']['id'];
            $name = $dat['callback_query']['from']['first_name'] ?? '';
        }elseif(isset($dat['message']['text'])){
            $chat_id = $dat['message']['chat']['id'];
            $mess = $dat['message']['text'];
            $name = $dat['message']['chat']['first_name'] ?? '';
        }elseif($dat['message']['location'] !== false){
            $mess = 'getlocation';
            $chat_id = $dat['message']['chat']['id'];
            $name = $dat['message']['chat']['first_name'] ?? '';
            $latitude = $dat['message']['location']['latitude'];
            $longitude = $dat['message']['location']['longitude'];
        }

        if($mess !== '/start'){
            $message = preg_replace("/[^а-яА-ЯёЁіІїЇєЄa-zA-Z0-9\s]/iu", "", $mess);
        }else{
            $message = $mess;
        }

        return array('message' => $message, 'chat_id' => $chat_id, 'youtube' => $youtube ?? '', 'latitude' => $latitude ?? '', 'longitude' => $longitude ?? '', 'name' => $name);

    }

    private function add_start_buttn(){
        $buttons = Keyboard::make()->inline();
        return $buttons->row(Keyboard::inlineButton(['text' => 'Погода і забруднення '.iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F447)), 'callback_data' => "location"]),
                      Keyboard::inlineButton(['text' => 'test '.iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F447)), 'callback_data' => "test"]),
                      Keyboard::inlineButton(['text' => 'На сайт 🌍', 'url' => "https://info-misto.com/"]));
    }

    private function tolover($text){
        return mb_strtolower($text, 'utf-8');
    }

    private function sendTelegram($data,$buttons){
            return Telegram::sendMessage([
                   'chat_id' => $data['chat_id'],
                    'text' => $data['text'],
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode($buttons)
            ]);
    }

}



// namespace App\Http\Controllers;
// use App\MyClass\YoutubeClass;
// use App\MyClass\NewClass;
// use Illuminate\Http\Request;
// use Telegram\Bot\Api;
// use Telegram;
// use Session;
// use Telegram\Bot\Actions;
// use Telegram\Bot\Commands\Command;
// use Telegram\Bot\FileUpload\InputFile;
// use Telegram\Bot\Keyboard\Keyboard;
// use DB;

// class TeleController extends Controller
// {

//     public function get_data_from_tg(Request $request){
        

//         $content = Telegram::getWebhookUpdates();
//         $dat = json_decode($content, true);
//         $inboxess = 'inboxess';
//         $youtube = 'youtube';

//         if(isset($dat['callback_query'])){

//         	$updates = $dat['callback_query'];
//             $updfromid = $updates['from']['id'];
//             $upd_data = $updates['data'];
//             $button = ['1' => 'youtube 0','2' => 'youtube 5','3' => 'youtube 10','4' => 'youtube 15','5' => 'youtube 20','6' => 'youtube 25']; // This should be in DB something like ($buttons = //DB::table('youtube_btns')->where(button, '!=', $upd_data)->get()->toArray())
//             $inbox = '';//DB::table($inboxess)->where('upd_from_id', $updfromid)->first(); 
//             $message_num = $inbox->message_num;

//             if($_SESSION['upd_from_id'] )->where('upd_from_id', $updfromid)->doesntExist()){  
            
//                 //DB::table($inboxess)->insertOrIgnore(['upd_from_id' => $updfromid]);
//             }

//             switch ($upd_data) {
//             case ($upd_data === 'xx')://DB::table($youtube)->where('videoId', $upd_data)->exists()):
//                 $keyword = '';//DB::table($inboxess)->where('upd_from_id', $updfromid)->first();
//                 $cutlastw = preg_replace('=\s\S+$=', "", $keyword->key1);
//                 $keyboard = Keyboard::make()->inline();
//                 if($cutlastw === $youtube){
//                     $yout = '';//DB::table($youtube)->where('videoId', $upd_data)->select('videoId','publishedAt','title')->latest('publishedAt')->get();
//                 }else{
//                     $keyboard->row(Keyboard::inlineButton([
//                             'text'          => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' back',
//                             'callback_data' => 'Menu']));
//                     $ans = "Ups, something went wrong!";
//                     $send = $this->editMess($updfromid,$message_num,$ans,$keyboard);
//                     exit;

//                 }
//                 foreach ($yout as $val) {
                    
//                         $l = $val->publishedAt;
//                         $p = $val->videoId;
                    
//                 }
                
//                 $keyboard->row(Keyboard::inlineButton([
//                             'text'          => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' back',
//                             'callback_data' => $keyword->key1]));
//                 $ans = "Published: ".Carbon::parse($l)->diffForHumans()."\n\nhttps://www.youtube.com/watch?v=".$p;
//                 return $this->editMess($updfromid,$message_num,$ans,$keyboard);
            
// //  Start Menu ---------------------------------------------------------------------------------------------------------------------

//             case ($upd_data === "Menu"):
                
//                 $keyboard = Keyboard::make()->inline();
//                 if($updfromid === $_ENV['YOUR_MESSAGE_ID']){
//                     $keyboard->row(Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x2757)).' Delete youtube', 'callback_data' => "yout_dell"]));
//                 }
//                 $keyboard->row(Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x2757)).' How to use', 'callback_data' => "info"]));
//                 $ans = "<b>Menu</b>";
//                 return $this->editMess($updfromid,$message_num,$ans,$keyboard);

//  //  Delete youtube table and info ------------------------------------------------------------------------------------------------

//         case ($upd_data === "yout_dell"||$upd_data === "info"):
//             if($upd_data === 'yout_dell'){
//                     $ans = 'Youtube table deleted';
//                     //DB::table($youtube)->truncate();
//             }else{
//                     $ans = "Just type: <b>Youtube madonna</b> or whatever you are looking for..\n\nTo reboot type: /start";
//             }
//                 $keyboard = Keyboard::make()->inline()->row(Keyboard::inlineButton(['text' => 'Menu', 'callback_data' => 'Menu']));
//                 return $this->editMess($updfromid,$message_num,$ans,$keyboard);

// //  Get Next 5 Youtube videos from Database  --------------------------------------------------------------------------------------

//          case (Arr::except($button, $upd_data) !== false):
//                 $search_val = '';//DB::table($inboxess)->where('upd_from_id', $updfromid)->first();
//                 //DB::table($inboxess)->where('upd_from_id', $updfromid)->update(['key1' => $upd_data]);
//                 $last_word_start = strrpos($upd_data, " ") + 1;
//                 $last_word = substr($upd_data, $last_word_start);
//                 $cutlastw = preg_replace('=\s\S+$=', "", $upd_data);
//                 $obr = intval($last_word);
//                 $search = str_replace("%20", " ", $search_val->keyword);
//                 $yout = '';//DB::table($cutlastw)->where('title','like','%'.$search.'%')->orWhere('channelTitle','like','%'.$search.'%')->skip($obr)->take(5)->latest('publishedAt')->get();
//                 $ans = 'Found for your request '.$search;
//                 $buttons = Arr::except($button, [$upd_data]);
//                 $keyboard = $this->addButton($buttons);
                
//                     foreach($yout as $val){
                        
//                             $title = $val->title;
//                             $title = strval($title);
//                             $video_id = $val->videoId;
//                             $short_title = mb_substr($title, 0, 30);
//                             $keyboard->row(Keyboard::inlineButton([
//                                 'text'          => $title,
//                                 'callback_data' => $video_id]));

                         
//                     }
                
//                     $keyboard->row(
//                         Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => 'Menu']));
//                         return $this->editMess($updfromid,$message_num,$ans,$keyboard);

//             case (strpos($upd_data, $youtube) !== false):
//                 $keyword = '';//DB::table($inboxess)->where('upd_from_id', $updfromid)->first();
//                 $key1 = str_replace("%20", " ", $keyword->key1);
//                 $yout = '';//DB::table($youtube)->where('title','like','%'.$key1.'%')->orWhere('channelTitle','like','%'.$key1.'%')->skip(0)->take(5)->latest('publishedAt')->get();
//                 $buttons = Arr::except($button, [array_shift($button)]);
//                 $keyboard = $this->addButton($buttons);
//                         foreach ($yout as $val) {
//                             $chantit = $val->channelTitle;
//                             $title = mb_substr($val->title, 0, 30);
//                             $keyboard->row(Keyboard::inlineButton(['text' => $title, 'callback_data' => $val->videoId]));
//                         }
//                         $keyboard->row(
//                                 Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//                                 $ans = 'founded by title '.$key1;
//                                 return $this->editMess($updfromid,$message_num,$ans,$keyboard);

//         }  
    // }elseif(isset($dat['message']['text'])){

    //     $chat_id    = $dat['message']['chat']['id'];
    //     $updfromid  = $dat['message']['from']['id'];
    //     $text       = $dat['message']['text'];
    //     //$text = $this->userText($text);
    //     $repl_1_word = substr(strstr($text," "), 1);
    //     $repl_1_word = preg_replace("/[^а-яА-ЯёЁіІїЇєЄa-zA-Z0-9\s]/iu", '', $repl_1_word);
    //     //$next_message = $updates['message']['message_id'];
    //     //DB::table($inboxess)->update(['message_num' => $next_message+1]);
    //     $button = ['1' => 'youtube 0','2' => 'youtube 5','3' => 'youtube 10','4' => 'youtube 15','5' => 'youtube 20','6' => 'youtube 25'];  // This should be in DB something like ($buttons = //DB::table('youtube_btns')->where(button, '!=', $upd_data)->get()->toArray())
        // if(//DB::table($inboxess)->where('upd_from_id', $updfromid)->doesntExist()){  
        
        //     //DB::table($inboxess)->insertOrIgnore(['upd_from_id' => $updfromid]);
        // }
//  ---------------------------------------------------------------------------------------------------------------------------------
//         if($text === ''||$text === ' '){

//             $keyboard = Keyboard::make()->inline()->row(Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//             $ans = "Please write <b>youtube</b>... and what you are looking for";
//             $this->sendMess($chat_id,$ans,$keyboard);
//             exit;

//         }
//         switch($text){
                

//                 case (strpos($text, '/start') !== false):
//                 $ans = "what we are searching?";
//                 $keyboard = Keyboard::make()->inline()->row(Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//                 $send = $this->sendMess($chat_id,$ans,$keyboard);
//                 exit;
//                     if(str_word_count($text) > 1){
//                         $word1 = str_replace(" ", "%20", $repl_1_word);
//                         $url = "https://youtube.googleapis.com/youtube/v3/search?part=snippet&q=".$word1."&type=video&key=".$_ENV['YOUTUBE_API_KEY']."&maxResults=25";
//                     }elseif((strpos($text, $youtube) !== false) && (str_word_count($text) === 1)){
//                          $keyboard = Keyboard::make()->inline()->row(Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//                         $ans = "what we are searching?";
//                         $send = $this->sendMess($chat_id,$ans,$keyboard);
//                     exit;
//                     }

//                     //DB::table($inboxess)->where('upd_from_id', $updfromid)->update(['keyword' => $word1, 'key1' => 'youtube 0']);
//                         $curl = curl_init();
//                         curl_setopt_array($curl, array(
//                             CURLOPT_URL => $url,
//                             CURLOPT_RETURNTRANSFER => true,
//                             CURLOPT_FOLLOWLOCATION => true,
//                             CURLOPT_ENCODING => "",
//                             CURLOPT_MAXREDIRS => 10,
//                             CURLOPT_TIMEOUT => 30,
//                             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                             CURLOPT_CUSTOMREQUEST => "GET"    
//                         ));

//                         $respon = curl_exec($curl);
//                         $respon = json_decode($respon, true);
//                         $keyboard = Keyboard::make()->inline();
//                         if (curl_errno($curl)) {
                            
//                             $keyboard->row(
//                                 Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//                             $ans = 'Nothing...';
//                             $send = $this->sendMess($chat_id,$ans,$keyboard);
//                             exit;
//                         } 

//                         curl_close($curl);

//                         foreach ($respon['items'] as $items => $item) {
//                             $title = $item['snippet']['title'];
//                             $chanel_title = $item['snippet']['channelTitle'];
//                             $shrt_title = preg_replace("/[^а-яА-ЯёЁіІїЇєЄa-zA-Z0-9\s]/iu", "", $title);
//                             $shrt_title = preg_replace('/^([ ]+)|([ ]){2,}/m', '$2', $shrt_title);
//                             $shrt_title = mb_substr($shrt_title, 0, 30);
//                             // if(//DB::table($youtube)->where('videoId',$item['id']['videoId'])->doesntExist()){
//                             //     //DB::table($youtube)->insertOrIgnore(['title' => $title,
//                             //                                       'channelTitle' => $chanel_title,
//                             //                                       'videoId' => $item['id']['videoId'],
//                             //                                       'channelId' => $item['snippet']['channelId'],
//                             //                                       'publishedAt' => $item['snippet']['publishedAt']]);
//                             // } 
//                         }
//                         $buttons = Arr::except($button, [array_shift($button)]);
//                         $keyboard = $this->addButton($buttons);
//                         $db_youtube = '';//DB::table($youtube)->where('title','like','%'.$repl_1_word.'%')->orWhere('channelTitle','like','%'.$repl_1_word.'%')->skip(0)->take(5)->latest('publishedAt')->get();

//                         foreach ($db_youtube as $yout) {
//                             $keyboard->row(Keyboard::inlineButton(['text' => $yout->title, 'callback_data' => $yout->videoId]));
//                         }
                        
//                         $keyboard->row(
//                                 Keyboard::inlineButton(['text' => iconv('UCS-4LE', 'UTF-8', pack('V', 0x1F519)).' Back', 'callback_data' => "Menu"]));
//                                 $ans = 'founded by title '.$repl_1_word;
//                                 return $this->sendMess($chat_id,$ans,$keyboard);
//                     break;
                         
//         }
                              
//     }


//     private function editMess($updfrid,$message_num,$ans,$keyboard){
//         return Telegram::editMessageText([
//                 'chat_id' => $updfrid,
//                 'message_id' => $message_num,
//                 'text' => $ans,
//                 'parse_mode' => 'HTML',
//                 'reply_markup' => $keyboard   
//             ]);
//     }

//     private function addButton($buttons){
//             $buttons = array_map(function($name,$val){
//                     return Keyboard::inlineButton(['text' => $name,'callback_data' => $val]);
//                 },array_keys($buttons),array_values($buttons));
//                 $inline   = Keyboard::make()->inline();
//             return call_user_func_array([$inline, 'row'], $buttons);
//     }

//     private function sendMess($chat_id,$ans,$keyboard){
//         return Telegram::sendMessage([
//                 'chat_id' => $chat_id,
//                 'text' => $ans,
//                 'parse_mode' => 'HTML',
//                 'reply_markup' => $keyboard   
//             ]);
//     }
        
// }

