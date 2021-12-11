<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Quiz;
    use App\Models\Round;
    use App\Extra\General;

    class QuizController extends Controller{


        public function fetch(?string $id = null,Request $request){

            $db = new Quiz();
            if($request->input('username')){
                $db->setTeamIdByUsername($request->input('username'));
            }else{
                $db->set('active',true);
                    //->set('draft',false);
                if($id){
                    $db->setId($id);
                }
            }
            $result = $db->get();

            return response()->json([
                'id' => $id,
                'quizzes' => General::formatMongoForJson($result)
            ]);

        }

        public function fetchRounds($id)
        {

            $db = new Quiz();

        }

        public function create(Request $request)
        {

            $start = new \DateTime($request->input('start'));

            $db = new Quiz();
            $db->set('title',trim($request->input('title')))
                ->set('startdate',$start)
                ->setTeamIdByUsername($request->input('username'));

            return response()->json([
                'created' => General::formatMongoForJson($db->create())
            ]);

        }

        public function remove(string $id,Request $request)
        {

            $db = new Quiz();
            $db->setId($id);

            return response()->json([
                'db' => $db->remove()
            ]);

        }

        public function update(string $id,Request $request){

            $db = new Quiz();
            $db->setId($id);

            $vars = $request->all();
            
            return response()->json([
                'db' => $db->update($vars),
            ]);

        }

        public function createRound($id, Request $request)
        {

            $round = new Round();
            $quiz = new Quiz();
            $input = $request->all();

            foreach($input AS $k => $v){
                if($v){
                    $func = 'set' . ucfirst($k);
                    $round->{$func}($v);
                }
            }

            $update = $quiz->setId($id)->addRound($round);

            return response()->json([
                'round' => $round,
                'input' => $input,
                'db' => $update
            ]);

        }

    }

?>