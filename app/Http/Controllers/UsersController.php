<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use Illuminate\Hashing\BcryptHasher;
    use Illuminate\Support\Facades\Mail;
    use App\Models\Users;
    use App\Extra\General;
    use App\Mail\TestMail;

    class UsersController extends Controller{

        public function create(Request $request){

            $db = new Users();

            $db->set('username',$request->input('username'))
                ->set('password',(new BcryptHasher)->make($request->input('password')));

            return response()->json([
                'db' => $db->create()
            ]);

        }

        public function fetch(?string $id = null){

            $db = new Users();
            $result = $db->fetch($id);

            return response()->json([
                'id' => $id,
                'users' => General::formatMongoForJson($result)
            ]);

        }

        public function remove(string $id){

            $db = new Users();
            $db->setId($id);

            return response()->json([
                'id' => $id,
                'result' => $db->remove()
            ]);

        }

        public function signup(Request $request){

            $db = new Users();

            $db->set('username',$request->input('username'))
                ->set('email',strtolower($request->input('email')))
                ->set('password',(new BcryptHasher)->make($request->input('password')));

            if($request->input('name')){
                $db->set('name',$request->input('name'));
            }else{
                $db->set('name',null);
            }

            $create = $db->create();

            // Mail::to('hitchhkr@gmail.com')->send(new TestMail());

            return response()->json([
                'result' => $create ? General::formatMongoForJson($create) : false
            ]);

        }

    }

?>