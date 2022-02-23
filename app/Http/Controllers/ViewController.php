<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\View;

    class ViewController extends Controller{

        public function create(Request $request){

            $resp = [];

            $view = new View();
            $view->set('type',$request->input('type'))
                ->set('ip',$_SERVER['REMOTE_ADDR'])
                ->set('agent',$request->input('agent'))
                ->set('user',$request->input('user'))
                ->set('film',$request->input('film') ? $request->input('film') : null)
                ->setFilmId($request->input('film_id'));

            $resp['db'] = $view->create();

            return response()->json($resp,200);

        }

    }