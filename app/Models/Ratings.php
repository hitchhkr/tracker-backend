<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use App\Models\Rating;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Ratings extends Mongo {

        private ObjectId $id;
        public ?ObjectId $_film_id = null;
        public ?ObjectId $_user_id = null;
        public Rating $rating;
        public UTCDateTime $created;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));
            $this->setCreated();

        }

        public function set($prop,$val){
            $this->{$prop} = $val;
            return $this;
        }

        public function setId(string $id)
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function setFilmId(string $val)
        {
            $this->_film_id = $this->convertMongoId($val);
            return $this;
        }

        public function setUserId(string $val)
        {
            $this->_user_id = $this->convertMongoId($val);
            return $this;
        }

        public function setRating(Rating $val)
        {
            $this->rating = $val;
            return $this;
        }

        public function setCreated(){

            $this->created = new UTCDateTime();

            return $this;

        }

        public function get(){

            if($this->_film_id && !$this->_user_id){

                $match = [
                    '$match' => [
                        '_film_id' => $this->_film_id
                    ]
                ];

                $group = [
                    '$group' => [
                        '_id' => '$_film_id',
                        'quality' => [
                            '$avg' => '$rating.quality'
                        ],
                        'enjoyment' => [
                            '$avg' => '$rating.enjoyment'
                        ],
                        'difficulty' => [
                            '$avg' => '$rating.difficulty'
                        ],
                        'overall' => [
                            '$avg' => [
                                '$add' => [
                                    '$rating.quality',
                                    '$rating.enjoyment'
                                ]
                            ]
                        ],
                        'total' => [
                            '$sum' => 1
                        ]
                    ]
                ];

                $agg = [
                    $match,
                    $group
                ];

                $res = $this->dbagg($agg);
                $res = $res ? $res[0] : null;

                return $res;

            }

            if($this->_film_id && $this->_user_id){

                $match = [
                    '$match' => [
                        '_film_id' => $this->_film_id,
                        '_user_id' => $this->_user_id
                    ]
                ];

                $group = [
                    '$group' => [
                        '_id' => '$_id',
                        'quality' => [
                            '$avg' => '$rating.quality'
                        ],
                        'enjoyment' => [
                            '$avg' => '$rating.enjoyment'
                        ],
                        'difficulty' => [
                            '$avg' => '$rating.difficulty'
                        ],
                        'overall' => [
                            '$avg' => [
                                '$add' => [
                                    '$rating.quality',
                                    '$rating.enjoyment'
                                ]
                            ]
                        ],
                        'total' => [
                            '$sum' => 1
                        ]
                    ]
                ];

                $agg = [
                    $match,
                    $group
                ];

                $res = $this->dbagg($agg);
                $res = $res ? $res[0] : null;

                return $res;

            }

        }

        public function getRatingSummary($order = -1, $limit = 10)
        {

            $group = [
                '$group' => [
                    '_id' => '$_film_id',
                    'quality' => [
                        '$avg' => '$rating.quality'
                    ],
                    'enjoyment' => [
                        '$avg' => '$rating.enjoyment'
                    ],
                    'difficulty' => [
                        '$avg' => '$rating.difficulty'
                    ],
                    'overall' => [
                        '$avg' => [
                            '$add' => [
                                '$rating.quality',
                                '$rating.enjoyment'
                            ]
                        ]
                    ],
                    'total' => [
                        '$sum' => 1
                    ]
                ]
            ];

            $sort = [
                '$sort' => [
                    'overall' => $order
                ]
            ];

            $limit = [
                '$limit' => $limit
            ];

            $lookup = [
                '$lookup' => [
                    'from' => 'one',
                    'localField' => '_id',
                    'foreignField' => '_id',
                    'as' => 'film'
                ]
            ];

            $unwind = [
                '$unwind' => [
                    'path' => '$film'
                ]
            ];

            $agg = [
                $group,
                $sort,
                $limit,
                $lookup,
                $unwind
            ];

            return $this->dbagg($agg);

        }

        public function create()
        {

            if(!$this->rating){
                throw new Exception('No rating has been set');
            }

            if(!$this->_film_id){
                throw new Exception('No film id has been attached');
            }

            if(!$this->_user_id){
                throw new Exception('No user id has been set');
            }

            return $this->dbinsert($this);

        }

        public function update()
        {

            if(!$this->rating){
                throw new Exception('No rating has been set');
            }

            if(!$this->_film_id){
                throw new Exception('No film id has been attached');
            }

            if(!$this->_user_id){
                throw new Exception('No user id has been set');
            }

            $q = [
                '_film_id' => $this->_film_id,
                '_user_id' => $this->_user_id
            ];

            $update = [
                '$set' => [
                    'rating' => $this->rating,
                    'updated' => new UTCDateTime()
                ],
                '$inc' => [
                    'version' => 1
                ]
            ];

            return $this->dbupdate($q,$update);

        }

    }

?>