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

            $resp = [];

            $type = $request->input('type') ? $request->input('type') : 'all';

            switch($type){

                case 'all':

                    $result = $tracker->getSummary();

                    if($result){

                        foreach($result AS $r){
                            $resp[$r['_id']] = $r['values'];
                        }

                    }

                break;

                case 'tokens':

                    $tracker->setType($type);
                    $date = null;
                    if($request->input('date')){
                        $date = new \DateTime($request->input('date'));
                    }
                    $result = $tracker->getType($date);

                    if($result){
                        $resp = $result;
                    }

                    // $resp = [$date->format('c')];

                break;

            }

            $resp = [
                'id' => $id,
                'result' => General::formatMongoForJson($resp),
                'var' => $request->all()
            ];

            return response()->json($resp,200);

        }

    }

?>