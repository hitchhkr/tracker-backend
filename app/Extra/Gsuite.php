<?php

    namespace App\Extra;

    use Google\Cloud\Storage\StorageClient;

    class Gsuite
    {

        public $keypath;
        protected $storage;
        protected $bucketName;
        protected $bucket;

        public function __construct(){

            $this->setKeyPath();

            $this->storage = new StorageClient([
                'keyFilePath' => $this->keypath
            ]);

            $this->setBucket(env('GCLOUD_BUCKET'));

        }

        public function getObjectName(string $path):string
        {

            $base = 'gs://' . $this->bucketName . '/';

            return str_replace($base,'',$path);

        }

        public function setBucket($bucket)
        {

            $this->bucketName = $bucket;

            $this->bucket = $this->storage->bucket($this->bucketName);

            return $this;

        }

        private function setKeyPath(){

            $this->keypath = __DIR__ . '/../../auth/' . env('GCLOUD_AUTH');

            if(!file_exists($this->keypath)){
                throw new \Exception('Auth file is missing');
            }

            return $this;

        }

        public function getImage(string $path)
        {

            $this->storage->registerStreamWrapper();
        
            return file_get_contents($path);

        }

        public function remove(string $name)
        {

            $object = $this->bucket->object($name);
            return $object->delete();

        }

        public function upload($file,$name)
        {

            return $this->bucket->upload($file,[
                'name' => $name
            ]);

        }
    }

?>