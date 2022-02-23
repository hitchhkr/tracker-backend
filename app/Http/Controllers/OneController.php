<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    //use Illuminate\Hashing\BcryptHasher;
    //use Illuminate\Support\Facades\Mail;
    use App\Models\One;
    use App\Models\Directors;
    use App\Models\Options;
    use App\Models\Ratings;
    use App\Models\Rating;
    use App\Models\Attempts;
    use App\Models\Found;
    use App\Extra\General;
    use App\Extra\Gsuite;
    use App\Extra\Label;
    use App\Extra\Images;
    use MongoDB\BSON\UTCDateTime;

    class OneController extends Controller{

        public function fetch(?string $id = null, Request $request)
        {

            $random = $request->input('random') ? $request->input('random') : false;
            $limit = $request->input('limit') ? $request->input('limit') : 1;
            $uid = $request->input('userid') ? $request->input('userid') : null;

            $db = new One();
            $db->setUserId($uid);

            if($id){
                $db->setId($id);
            }

            $films = $db->get($random,$limit);

            return response()->json([
                'db' => General::formatMongoForJson(count($films) == 1 ? $films[0] : $films),
                'total' => count($db->get())
            ]);

        }

        public function fetchDirector(?string $id = null, Request $request)
        {

            $db = new Directors();

            if($id){
                $db->setId($id);
            }

            $directors = $db->get();

            return response()->json([
                'db' => General::formatMongoForJson($directors)
            ]);

        }

        public function fetchRating(?string $id = null, Request $request)
        {

            $db = new Ratings();
            if($id){
                $db->setFilmId($id);
            }

            $resp = [];
            $resp['avg'] = $db->get();

            if($request->input('user_id')){
                $resp['user'] = $db->setUserId($request->input('user_id'))->get();
            }

            return response()->json($resp,200);

        }

        public function fetchSection(string $type, ?string $subtype = null, Request $request)
        {

            $resp = [];

            switch($type){

                case 'rating':

                    $ratings = new Ratings();
                    $uid = $request->input('userid') ? $request->input('userid') : null;
                    $ratings->setUserId($uid);
                    $resp['db'] = $ratings->getRatingSummary();

                    break;

                case 'director':

                    $uid = $request->input('userid') ? $request->input('userid') : null;

                    $db = new One();
                    $db->setUserId($uid);
                    $director = $db->getByDirector($subtype);

                    $ratings = new Ratings();

                    foreach($director AS $k => $d){
                        $director[$k]['ratings'] = $ratings->setFilmId((string)$d['_id'])->get();
                    }

                    $resp['db'] = General::formatMongoForJson($director);

                    break;

                case 'decade':

                    $random = $request->input('random') ? $request->input('random') : false;
                    $limit = $request->input('limit') ? $request->input('limit') : 10;
                    $uid = $request->input('userid') ? $request->input('userid') : null;

                    $db = new One();
                    $db->setUserId($uid);
                    $data = $db->getByDecade($subtype,$random,$limit);

                    $ratings = new Ratings();

                    if(!$data){
                        $resp['db'] = null;
                        break;
                    }

                    foreach($data AS $k => $d){
                        $data[$k]['ratings'] = $ratings->setFilmId((string)$d['_id'])->get();
                    }

                    $resp['db'] = General::formatMongoForJson($data);

                    break;

                case 'genres':

                        $random = $request->input('random') ? $request->input('random') : false;
                        $limit = $request->input('limit') ? $request->input('limit') : 10;
                        $uid = $request->input('userid') ? $request->input('userid') : null;
    
                        $db = new One();
                        $db->setUserId($uid);
                        $data = $db->getByGenre($subtype,$random,$limit);
    
                        $ratings = new Ratings();
    
                        if(!$data){
                            $resp['db'] = null;
                            break;
                        }
    
                        foreach($data AS $k => $d){
                            $data[$k]['ratings'] = $ratings->setFilmId((string)$d['_id'])->get();
                        }
    
                        $resp['db'] = General::formatMongoForJson($data);
    
                        break;

                    case 'focus':

                        $not = $request->input('not') ? $request->input('not') : null;

                        $db = new One();
                        $focus = $db->getFocus($subtype,$not);

                        $resp['focus'] = $focus ? General::formatMongoForJson($focus[0]) : null;
                        $resp['text'] = 'Can you guess';

                        break;

                    case 'similar':

                        $limit = $request->input('limit') ? $request->input('limit') : 3;
                        $uid = $request->input('userid') ? $request->input('userid') : null;

                        $film = new One();
                        $film->setUserId($uid);
                        $similar = $film->getSimilar($subtype, $limit);

                        $resp[$type] = $similar ? General::formatMongoForJson($similar) : null;

                        break;

                    case 'recent':

                        $limit = $request->input('limit') ? $request->input('limit') : 3;
                        $uid = $request->input('userid') ? $request->input('userid') : null;

                        $film = new One();
                        $film->setUserId($uid);
                        $recent = $film->getRecent($subtype, $limit);

                        $resp[$type] = $recent ? General::formatMongoForJson($recent) : null;

                        break;

                default:

                    $resp['error'] = 'The type of request ' . $type . ' is not currently supported'; 

                break;

            }

            return response()->json(General::formatMongoForJson($resp),200);

        }

        public function create(Request $request){

            $db = new One();
            $gcp = new Gsuite();
            $gcp->setBucket(env('GCLOUD_11111_BUCKET'));

            $oneId = Label::create();
            $fn = $oneId . '/original.jpg';

            $vars = $request->all();

            $file = General::processImageURI($vars['imageurl']);
            $upload = $gcp->upload($file['binary'],$fn);

            $db->setLabel($oneId)
                ->set('title',$vars['title']);

            return response()->json([
                //'vars' => $vars,
                // 'id' => $oneId,
                // 'fn' => $fn
                'db' => $db->create()
            ]);

        }

        public function createRating(Request $request)
        {

            $vars = $request->all();

            $rating = new Rating();
            $ratings = new Ratings();
            $rating->setAll($vars['ratings']);

            $ratings->setFilmId($vars['film_id'])
                ->setUserId($vars['user_id'])
                ->setRating($rating);

            try{
                $create = $ratings->create();
                $code = 201;
            }catch(Exception $e){
                $create = $e->getMessage();
                $code = 400;
            }

            return response()->json([
                'db' => General::formatMongoForJson($create)
            ],$code);

        }

        public function fetchSearch(string $type,string $q,Request $request)
        {

            $resp = [];
            $q = urldecode($q);

            $resp['query'] = $q;
            $db = new One();

            $fid = $request->input('originalid') ? $request->input('originalid') : null;
            $uid = $request->input('userid') ? $request->input('userid') : null;

            switch($type){

                case 'title':

                    if(!$uid){
                        $resp['error'] = 'No user id was sent over';
                        break;
                    }

                    $result = $db->searchTitle($q);
                    $match = null;

                    foreach($result AS $k => $r){
                        $rel = similar_text(General::removeWhiteSpace($r['title'],true),General::removeWhiteSpace($q,true),$perc);
                        $result[$k]['rel'] = $rel; 
                        $result[$k]['perc'] = $perc;
                        $result[$k]['match'] = $perc >= 95 ? true : false;
                        if((string)$r['_id'] == $fid){
                            $match = $result[$k];
                        }
                    }

                    $attempt = new Attempts();
                    $attempt->setFilmId($fid)
                        ->setUserId($uid)
                        ->setSearch($q);

                    if($match){
                        $attempt->setSuccess($match['match']);
                        switch(true){
                            case $match['perc'] < 50:
                                $resp['msg'] = 'Sorry but you have guessed incorrectly, please try again.';
                                break;
                            case $match['perc'] < 80;
                                $resp['msg'] = 'Sorry but you have guessed incorrectly, but it looks like you are on the right lines. Why not have another go.';
                                break;
                            case $match['perc'] < 95;
                                $resp['msg'] = 'Sorry but you have guessed incorrectly, but it looks like you are very close. Why not have another go, just try and amend the wording a little.';
                                break;
                            default:
                                $resp['msg'] = 'Nice one! You are correct. The correct answer is ' . $match['title'] . '. This guess will be saved for future visists to the site.';
                            break;
                        }
                        if($match['match'] == true){
                            $found = new Found();
                            $found->setFilmId($fid)
                                ->setUserId($uid)
                                ->setGuess($q)
                                ->add();
                        }
                    }else{
                        $resp['msg'] = 'Your guess is incorrect, please have another try.';
                    }

                    $resp['result'] = $result;
                    $resp['match'] = $match;
                    $resp['attempt'] = General::formatMongoForJson($attempt->create());

                    break;

                default:

                    $resp['error'] = $type . ' is not supported';

            }

            return response()->json(General::formatMongoForJson($resp),200);

        }

        public function updateRating(Request $request)
        {

            $vars = $request->all();

            $rating = new Rating();
            $ratings = new Ratings();
            $rating->setAll($vars['ratings']);

            $ratings->setFilmId($vars['film_id'])
                ->setUserId($vars['user_id'])
                ->setRating($rating);

            try{
                $create = $ratings->update();
                $code = 201;
            }catch(Exception $e){
                $create = $e->getMessage();
                $code = 400;
            }

            return response()->json([
                'func' => 'update',
                'db' => General::formatMongoForJson($create)
            ],$code);

        }

        public function update(string $id, Request $request)
        {

            $db = new One();
            $db->setId($id);
            $film = $db->get();

            $vars = $request->all();

            if(isset($vars['image'])){

                $gcp = new Gsuite();
                $gcp->setBucket(env('GCLOUD_11111_BUCKET'));
    
                $oneId = $film['_label'];
                $fn = $oneId . '/full.jpg';
                $fnSmall = $oneId . '/small.jpg';

            //  Remove any existing
                if(isset($film['cropped'])){
                    try{
                        $gcp->remove($fn);
                        $gcp->remove($fnSmall);
                    }catch(Exception $e){

                    }
                }
    
                $file = General::processImageURI($vars['image']);

                $blob = Images::resize($file['binary'],800,480);
                $blobSmall = Images::resize($file['binary'],160,90);
    
                $upload = $gcp->upload($blob,$fn);
                $uploadSmall = $gcp->upload($blobSmall,$fnSmall);

                $fn = $oneId . '_full.jpg';
                $fnSmall = $oneId . '_small.jpg';

                $db->update([
                    'cropped' => [
                        'hasBeenCropped' => true,
                        'lastcropped' => new UTCDateTime(),
                        'files' => [
                            'full' => $fn,
                            'small' => $fnSmall
                        ]
                    ]
                    ]);

            }

            if(isset($vars['title'])){

                $directors = new Directors();
                $options = new Options();
                $options->setType('11111_genres');

                foreach($vars['director'] AS $k => $d)
                {
                    $check = $directors->setName($d)->get();
                    if($check){
                        $vars['director'][$k] = $check;
                    }else{
                        $directors->create();
                        $vars['director'][$k] = $directors->get();
                    }
                }

                foreach($vars['genres'] AS $k => $d)
                {
                    $check = $options->setValue($d)->get();
                    if($check){
                        $vars['genres'][$k] = $check;
                    }else{
                        $options->create();
                        $vars['genres'][$k] = $options->get();
                    }
                }

                if($vars['edition']){
                    $options->setType('11111_editions');
                    $check = $options->setValue($vars['edition'])->get();
                    if($check){
                        $vars['edition'] = $check;
                    }else{
                        $options->create();
                        $vars['edition'] = $options->get();
                    }
                }

                if($vars['year'] > 1919)
                {
                    $vars['decade'] = General::getDecade($vars['year']);
                }

                $db->update($vars);

            }

            return response()->json([
                'film' => General::formatMongoForJson($film),
                //'vars' => $vars
            ]);

        }

    }

?>