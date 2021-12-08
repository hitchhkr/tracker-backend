<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;

    class InfoController extends Controller{

        public function getInfo(){

            return response()->json('some info');

        }

    }

?>