<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    //use Illuminate\Hashing\BcryptHasher;
    //use Illuminate\Support\Facades\Mail;
    use App\Models\One;
    use App\Models\Directors;
    use App\Models\Options;
    use App\Extra\General;
    use App\Extra\Gsuite;
    use App\Extra\Label;
    use App\Extra\Images;
    use MongoDB\BSON\UTCDateTime;

    class OneController extends Controller{

        public function fetch(?string $id = null, Request $request)
        {

            $db = new One();

            if($id){
                $db->setId($id);
            }

            $films = $db->get();

            if($request->input('random') && !$id){
                $rand = mt_rand(0,count($films) - 1);
                $films = $films[$rand];
            }

            return response()->json([
                'db' => General::formatMongoForJson($films)
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
                    $gcp->remove($fn);
                    $gcp->remove($fnSmall);
                }
    
                $file = General::processImageURI($vars['image']);

                $blob = Images::resize($file['binary'],800,480);
                $blobSmall = Images::resize($file['binary'],160,90);
    
                $upload = $gcp->upload($blob,$fn);
                $uploadSmall = $gcp->upload($blobSmall,$fnSmall);

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

            }

            return response()->json([
                'film' => General::formatMongoForJson($film),
                'vars' => $vars
            ]);

        }

    }

?>