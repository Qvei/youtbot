<?php
namespace App\MyClass;
use App;
use Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Actions;
use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;

class YoutubeClass {


    public $words;
    public $api_key;

    public function __construct($words, $api_key){
        $this->words = $words;
        $this->api_key = $api_key;
    }


    public function get_videos(){

                    $url = "https://youtube.googleapis.com/youtube/v3/search?part=snippet&q=".str_replace(" ", "%20", $this->words)."&order=date&type=video&key=".$this->api_key."&maxResults=25";
                     $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET"    
                        ));
                        $respon = curl_exec($curl);
                        curl_close($curl);
                        $respon = json_decode($respon, true);
                        return $this->make_butt($respon);

    }

    
    public function make_butt($ite){
                        $buttons = Keyboard::make()->inline();
                        foreach ($ite['items'] as $it => $item) {
                            $title = $item['snippet']['title'];
                            $shrt_title = preg_replace('/^([ ]+)|([ ]){2,}/m', '$2', $title);
                            $shrt_title = mb_substr($shrt_title, 0, 30);
                            $buttons->row(Keyboard::inlineButton(['text' => $shrt_title, 'callback_data' => $item['id']['videoId'].'||youtube']));
                        }
            return  $buttons;
    }
    

}