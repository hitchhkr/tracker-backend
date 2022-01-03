<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use Laravel\Lumen\Application;
    // use Illuminate\Hashing\BcryptHasher;
    // use Illuminate\Support\Facades\Mail;
    use App\Models\Word;
    use App\Extra\General;
    // use App\Mail\TestMail;
    // use App\Mail\ResetMail;
    // use App\Mail\ResetConfirmMail;

    class DictionaryController extends Controller{

        public function find($action,$word, Request $request)
        {

            $exists = false;
            $info = null;
            $words = new Word($word);
            $search = $words->get();

            switch($action){

                case 'find':

                    if($search){
                        $exists = true;
                        $info = General::formatMongoForJson($search);
                    }
                
                break;

                case 'wordsin':

                    $min = $request->input('min') ? (int)$request->input('min') : 1;
                    $max = $request->input('max') ? (int)$request->input('max') : $words->length;

                    $list = $words->getByLength($min,$max);

                    $in = [];

                    foreach($list AS $l){
                        $srcArray = str_split($word);
                        $matchArray = str_split($l['word']);
                        $hasMatch = true;
                        foreach($matchArray AS $m){
                            $match = array_search($m,$srcArray);
                            //echo var_dump($match);
                            if($match !== false){
                                unset($srcArray[$match]);
                            }else{
                                $hasMatch = false;
                                break;
                            }
                        }
                        if($hasMatch){
                            $in[] = $l['word'];
                        }
                    }

                    if($search){
                        $exists = true;
                        //$info = General::formatMongoForJson($search);
                        //$info['vars'] = $request->all();
                        //$info['list'] = count($list);
                        //$info['min'] = $min;
                        //$info['max'] = $max;
                        $info['matches'] = count($in);
                        $info['in'] = $in;
                    }

                break;

            }

            return response()->json([
                'word' => $word,
                'exists' => $exists,
                'info' => $info
            ]);

        }

        public function create($type,Request $request)
        {

            $data = null;

            switch($type){

                case 'build':

                    $file = (new Application)->basePath() . '/tmp/words_dictionary.json';
                    if(file_exists($file)){
                        $data['file_exists'] = true;
                        $contents = file_get_contents($file);
                        $contents = json_decode($contents);
                        //$data['example'] = $contents;

                        $words = 0;

                        $word = new Word();

                        foreach($contents AS $k => $c){
                            $word->setWord($k)->add();
                            $words++;
                        }

                        $data['words'] = $words;
                        $data['example'] = $word;

                    }else{
                        break;
                    }


                    break;

            }

            return response()->json([
                'type' => $type,
                'data' => $data
            ]);

        }

    }

?>