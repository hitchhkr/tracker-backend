<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use Laravel\Lumen\Application;
    use App\Models\Attempts;
    use App\Extra\General;

    class AttemptsController extends Controller{

        public function fetch(string $filmid, string $userid = null, Request $request)
        {

            $resp = [];

            $attempts = new Attempts();
            $attempts->setFilmId($filmid)->setUserId($userid);

            $resp['db'] = General::formatMongoForJson($attempts->getSummary());

            return response()->json($resp,200);

        }

    }