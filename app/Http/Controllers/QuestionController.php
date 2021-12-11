<?php

    namespace App\Http\Controllers;

    use App\Author;
    use Illuminate\Http\Request;
    use App\Models\Questions;
    use App\Extra\General;
    use App\Extra\Label;
    use App\Extra\Gsuite;

    class QuestionController extends Controller{

        public function fetch(?string $id = null, Request $request){

            $db = new Questions();
            $label = $request->input('round');
            if($label){
                $db->setLabel($label);
            }
            $result = $db->get();

            return response()->json([
                'id' => $id,
                'label' => $label,
                'questions' => General::formatMongoForJson($result)
            ]);

        }

        public function remove(string $id)
        {

            $db = new Questions();
            $gc = new Gsuite();
            $db->setId($id);

            $question = $db->get();
            $file = null;

            if(isset($question['gcloud'])){
                $file = $gc->getObjectName($question['gcloud']['uri']);
                $gc->remove($file);
            }

            return response()->json([
                'db' => $db->remove(),
                'file' => $file
            ]); 

        }

        public function create(string $id, Request $request)
        {

            $db = new Questions();
            $gcp = new Gsuite();
            $vars = $request->all();

            $fn = Label::create() . '_' . $vars['imagename'];

            $file = General::processImageURI($vars['imageurl']);
            $upload = $gcp->upload($file['binary'],$fn);

            $db->setQuizId($id)
                ->setLabel($vars['round'])
                ->set('type',$vars['type'])
                ->set('question',$vars['question'])
                ->set('answer',$vars['answer'])
                ->set('points',$vars['points'])
                ->set('gcloud',[
                    'uri' => $upload->gcsUri(),
                    'contentType' => $file['contentType']
                ]);

            return response()->json([
                'result' => $db->create()
            ]);

        }

    }

?>