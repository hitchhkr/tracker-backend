<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Audit;
    use Illuminate\Hashing\BcryptHasher;

    class AuditController extends Controller{

        public function audit(Request $request){

            $db = new Audit();
            $db->set('type',$request->input('type'))
                ->set('agent',$request->input('agent'));


            $resp = [
                'added' => $db->create()
            ];

            return response()->json($resp,200);

        }

    }

?>