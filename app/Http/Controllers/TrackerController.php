<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Users;
    use App\Models\Tracker;
    use Illuminate\Hashing\BcryptHasher;
    use App\Extra\General;

    class TrackerController extends Controller{

        public function create(string $type, string $id, Request $request){

            $tracker = new Tracker();
            $tracker->setType($type)->setUid($id);

            $resp = [
                'type' => $type,
                'db' => $tracker->create($request->all())
            ];

            return response()->json($resp,200);

        }

        public function fetch(string $id, Request $request)
        {

            $tracker = new Tracker();
            $tracker->setUid($id);

            $result = $tracker->getSummary();
            $resp = [];

            if($result){

                foreach($result AS $r){
                    $resp[$r['_id']] = $r['values'];
                }

            }

            $resp = [
                'id' => $id,
                'result' => General::formatMongoForJson($resp)
            ];

            return response()->json($resp,200);

        }

    }

?>