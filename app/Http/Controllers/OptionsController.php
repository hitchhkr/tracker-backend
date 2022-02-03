<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Options;
    use App\Extra\General;

    class OptionsController extends Controller{

        public function fetch(string $type, string $value = null, Request $request){

            $db = new Options();
            $db->setType($type);

            $resp = [
                'db' => General::formatMongoForJson($db->get()),
                //'raw' => $db->get(),
                'type' => $type
            ];

            return response()->json($resp,200);

        }

    }

?>