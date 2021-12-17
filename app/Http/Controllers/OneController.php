<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    //use Illuminate\Hashing\BcryptHasher;
    //use Illuminate\Support\Facades\Mail;
    use App\Models\One;
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

            return response()->json([
                'film' => General::formatMongoForJson($film)
            ]);

        }

    }

?>