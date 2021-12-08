<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Users;
    use Illuminate\Hashing\BcryptHasher;

    class AuthController extends Controller{

        public function auth(Request $request){

            $db = new Users();
            $db->set('username',$request->input('username'));

            $user = $db->getUserByUsername();

            $resp = [
                'allow' => $user ? (new BcryptHasher)->check($request->input('password'),$user['password']) : false,
                'username' => $request->input('username'),
                'info' => [
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ];

            return response()->json($resp,200);

        }

    }

?>