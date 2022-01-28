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

                case 'weight':

                    $limit = $request->input('limit') ? (int)$request->input('limit') : null;
                    $format = $request->input('format') ? $request->input('format') : null;
                    $target = $request->input('target') ? (float)$request->input('target') : 0;
                    $unit = $request->input('unit') ? $request->input('unit') : 'kg';

                    $tracker->setType($type);
                    $result = $tracker->getType(null,$limit);

                    if($format == 'chart'){

                        if($result){

                            $result = array_reverse($result);

                            $labels = [];
                            $data = [];
                            $datasets = [];
                            $background = [];
                            $backgroundTrg = [];
                            $trg = [];
                            $max = 0;
                            $min = 0;

                            foreach($result AS $r){
                                $labels[] = $r['values']['date']->toDateTime()->format('j M y');
                                // $labels[] = $r['values']['date'];
                                if($unit != 'kg'){
                                    $data[] = General::kgToLbs($r['values']['reading'],true);
                                    $trg[] = General::kgToLbs($target,true);
                                    if(General::kgToLbs($r['values']['reading'],true) > $max){
                                        $max = General::kgToLbs($r['values']['reading'],true);
                                    }
                                }else{
                                    $data[] = $r['values']['reading'];
                                    $trg[] = $target;
                                    if($r['values']['reading'] > $max){
                                        $max = $r['values']['reading'];
                                    }
                                }
                                $background[] = '#FF5733';
                                $backgroundTrg[] = '#20AB00';
                            }

                            $resp = [
                                'chart' => [
                                    'labels' => $labels,
                                    'datasets' => [
                                        [
                                            'label' => 'Weight',
                                            'data' => $data,
                                            'backgroundColor' => $background,
                                            'borderColor' => '#FF5733'
                                        ],
                                        [
                                            'label' => 'Target',
                                            'data' => $trg,
                                            'backgroundColor' => '#20AB00',
                                            'borderColor' => '#20AB00'
                                        ]
                                    ]
                                ],
                                'options' => [
                                    'scales' => [
                                        'y' => [
                                            'max' => $max + 3,
                                            'min' => $unit == 'kg' ? $target - 3 : General::kgToLbs($target,true) - 3,
                                            'ticks' => [
                                                'stepSize' => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ];

                        }

                    }else{

                        if($result){
                            $resp = $result;
                        }

                    }

                break;

                case 'tokens':

                    $limit = $request->input('limit') ? (int)$request->input('limit') : null;
                    $format = $request->input('format') ? $request->input('format') : null;
                    $target = $request->input('target') ? (float)$request->input('target') : 0;

                    $tracker->setType($type);

                    if($format == 'chart'){

                        $result = $tracker->getTokenSummary($limit);

                        if($result){
                            $result = array_reverse($result);

                            $labels = [];
                            $data = [];
                            $dataRemain = [];
                            $datasets = [];

                            foreach($result AS $r){
                                $labels[] = $r['_id']->toDateTime()->format('j M y');
                                // $labels[] = $r['values']['date'];
                                $data[] = $r['tokens'];
                                if($r['tokens'] < $target){
                                    $dataRemain[] = $target - $r['tokens'];
                                }else{
                                    $dataRemain[] = 0;
                                }
                            }

                            $resp = [
                                'chart' => [
                                    'labels' => $labels,
                                    'datasets' => [
                                        [
                                            'label' => 'Tokens used',
                                            'data' => $data,
                                            'backgroundColor' => '#FF5733',
                                            'borderColor' => '#FF5733'
                                        ],
                                        [
                                            'label' => 'Tokens Remaining',
                                            'data' => $dataRemain,
                                            'backgroundColor' => '#20AB00',
                                            'borderColor' => '#20AB00'
                                        ],
                                    ]
                                ],
                                'options' => [
                                    'responsive' => true,
                                    'scales' => [
                                        'x' => [
                                            'stacked' => true
                                        ],
                                        'y' => [
                                            'stacked' => true
                                        ]
                                    ]
                                ]
                            ];
                        }

                    }else{

                        $date = null;
                        if($request->input('date')){
                            $date = new \DateTime($request->input('date'));
                        }
                        $result = $tracker->getType($date);

                        if($result){
                            $resp = $result;
                        }

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