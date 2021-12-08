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

    }

?>