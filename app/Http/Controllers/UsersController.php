<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use Illuminate\Hashing\BcryptHasher;
    use Illuminate\Support\Facades\Mail;
    use App\Models\Users;
    use App\Extra\General;
    use App\Mail\TestMail;
    use App\Mail\ResetMail;
    use App\Mail\ResetConfirmMail;

    class UsersController extends Controller{

        public function create(Request $request){

            $db = new Users();

            $db->set('username',$request->input('username'))
                ->set('password',(new BcryptHasher)->make($request->input('password')));

            return response()->json([
                'db' => $db->create()
            ]);

        }

        public function createPrefs(string $type ,string $id, Request $request)
        {

            $db = new Users();
            $resp = $db->setId($id)->updatePreferences($type,$request->all());

            return response()->json([
                'type' => $type,
                'db' => $resp
                //'id' => $id,
                //'vars' => $request->all()
            ]);

        }

        public function fetch(?string $id = null){

            $db = new Users();
            $result = $db->fetch($id);

            unset($result['password']);
            unset($result['passwordHistory']);
            unset($result['resetRequests']);

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

        public function reset(string $type,Request $request)
        {

            $rid = 'testing';
            $allowed = false;
            $users = new Users();

            switch($type){

                case 'email':

                    $email = $request->input('email');
                    $users->set('email',$email);
                    $user = $users->getUserByEmail();
        
                    if($user){
                        //We need to send out an email and mark the user as being reset
                        $rid = $users->reset();
                        Mail::to($email)->send(new ResetMail($user['username'],$email,$rid));
                    }

                break;

                case 'verify':

                    $email = $request->input('email');
                    $token = $request->input('token');

                    $allowed = (new BcryptHasher)->check($email,$token);

                    return response()->json([
                        'allowed' => $allowed
                    ]);


                break;

                case 'password':

                    $email = $request->input('email');
                    $pwd = $request->input('password');

                    $users->set('email',$email);

                    $valid = $users->checkValidToken($email);
                    $db = null;

                    if($valid == true){

                        $user = $users->getUserByEmail();
                        $db = $users->updatePassword((new BcryptHasher)->make(trim($pwd)));
                        Mail::to($email)->send(new ResetConfirmMail($user['username']));

                    }

                    return response()->json([
                        'valid' => $valid,
                        'db' => $db
                    ]);

                break;

            }

            return response()->json([
                //'type' => $type,
                // 'rid' => $rid
                // 'email' => $email,
                // 'users' => $user,
                //'allowed' => $allowed
            ]);

        }

    }

?>