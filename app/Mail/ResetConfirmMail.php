<?php

    namespace App\Mail;
 
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
 
    class ResetConfirmMail extends Mailable {
    
        use Queueable, SerializesModels;

        public $username;
        public $base;

        public function __construct(string $username){

            $this->username = $username;
            $this->buildBase();

        }

        private function buildBase(){

            $this->base = env('FRONTEND_URI_BASE');

        }
    
        //build the message.
        public function build() {
            return $this->subject('Reset Password Success')
                        ->view('reset-confirm-password');
        }

    }

?>