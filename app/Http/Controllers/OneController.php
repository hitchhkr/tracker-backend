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

    class OneController extends Controller{

        public function fetch(?string $id = null, Request $request)
        {

            $db = new One();

            if($id){
                $db->setId($id);
            }

            return response()->json([
                'db' => General::formatMongoForJson($db->get())
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

            $gcp = new Gsuite();
            $gcp->setBucket(env('GCLOUD_11111_BUCKET'));

            $oneId = $film['_label'];
            $fn = $oneId . '/full.jpg';

            $vars = $request->all();

            $file = General::processImageURI($vars['full']);

            $imagick = new \Imagick();
            $imagick->readImageBlob($file['binary']);
            $imagick->resizeImage(800,450,\Imagick::FILTER_QUADRATIC,0.5);
            $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality(25);

            $upload = $gcp->upload($imagick->getImageBlob(),$fn);

            return response()->json([
                'film' => General::formatMongoForJson($film)
            ]);

        }

    }

?>