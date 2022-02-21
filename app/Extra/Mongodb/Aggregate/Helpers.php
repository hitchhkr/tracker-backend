<?php

    namespace App\Extra\Mongodb\Aggregate;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\Mongodb\Aggregate;
    use App\Extra\General;

    class Helpers extends Aggregate
    {

        public $type;
        public $output;

        public function __construct()
        {

            $this->setType(strtolower(General::get_class_name(get_class($this))));

        }

        public function buildOneUserDisplay(ObjectId $uid = null, array $agg, string $filmField = '_id'):object
        {

            if($uid)
            {
                $lookup2 = [
                    '$lookup' => [
                        'from' => 'found',
                        'let' => [
                            'fid' => '$' . $filmField
                        ],
                        'as' => 'foundcheck',
                        'pipeline' => [
                            [
                                '$match' => [
                                    '$expr' => [
                                        '$and' => [
                                            [
                                                '$eq' => ['$_user_id',$uid]
                                            ],
                                            [
                                                '$eq' => ['$_film_id','$$fid']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                array_push($agg,$lookup2);
                $unwind2 = [
                    '$unwind' => [
                        'path' => '$foundcheck',
                        'preserveNullAndEmptyArrays' => true
                    ]
                ];
                array_push($agg,$unwind2);
                $addFields = [
                    '$addFields' => [
                        'show' => [
                            'display' => [
                                '$cond' => [
                                    // [
                                        ['$not' => ['$foundcheck']],
                                        false,
                                        true
                                    // ]
                                ]
                            ]
                        ]
                    ]
                ];
                array_push($agg,$addFields); 

            }else
            {
                $addFields = [
                    '$addFields' => [
                        'show' => [
                            'display' => false
                        ]
                    ]
                ];
                array_push($agg,$addFields); 
            }

            $this->output = $agg;
            return $this;

        }

        public function get():array
        {

            return $this->output;

        }

    }