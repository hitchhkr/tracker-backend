<?php

    namespace App\Mail;
 
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
 
    class ResetMail extends Mailable {
    
        use Queueable, SerializesModels;

        public $rid;
        public $email;
        public $username;
        public $base;

        public function __construct(string $username, string $email,string $rid){

            $this->rid = $rid;
            $this->email = $email;
            $this->username = $username;
            $this->buildBase();

        }

        private function buildBase(){

            $this->base = env('FRONTEND_URI_BASE');

        }
    
        //build the message.
        public function build() {
            return $this->subject('Password Reset Request')->view('reset-password');
        }

    }

?>