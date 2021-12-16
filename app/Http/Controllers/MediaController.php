<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Questions;
    use App\Extra\General;
    use App\Extra\Label;
    use App\Extra\Gsuite;
    use Illuminate\Http\Response;

    class MediaController extends Controller{


        public function fetchQuestion(?string $id, string $ext){

            $db = new Questions();
            $gc = new Gsuite();
            $db->setId($id);
            $result = $db->get();

            return response($gc->getImage($result['gcloud']['uri']), 200)
                  ->header('Content-Type', $result['gcloud']['contentType']);

        }

        public function fetchApplication(?string $id, string $ext)
        {
            $gc = new Gsuite();
            //$gc->setBucket(env('GCLOUD_APP_BUCKET'));

            $uri = 'gs://';
            $uri .= env('GCLOUD_APP_BUCKET') . '/' . $id . '.' . $ext;

            $contetType = null;

            switch(strtolower($ext)){

                case 'jpg':
                    $contetType = 'image/jpg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                case 'mp3':
                    $contentType = 'media/mp3';
                    break;

            }

            return response($gc->getImage($uri), 200)
                  ->header('Content-Type', $contetType);

        }

        public function fetchOne(string $id, string $type, string $ext)
        {

            $gc = new Gsuite();

            $uri = 'gs://';
            $uri .= env('GCLOUD_11111_BUCKET') . '/' . $id . '/' . $type . '.' . $ext;

            $contetType = null;

            switch(strtolower($ext)){

                case 'jpg':
                    $contetType = 'image/jpg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                case 'mp3':
                    $contentType = 'media/mp3';
                    break;

            }

            return response($gc->getImage($uri), 200)
                  ->header('Content-Type', $contetType);

        }

    }

?>