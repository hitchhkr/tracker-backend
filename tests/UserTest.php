<?php

class UserTest extends TestCase
{

    public function testfetchUsers()
    {

        $resp = $this->json('GET','/api/user')
            ->seeJson([
                'id' => null
            ]);

        $content = $resp->response->getContent();
        $json = json_decode($content,true);

        if(is_array($json['users'])){

            $this->assertArrayNotHasKey('password', $json['users'][0]);

        }

    }

    public function testCreateDeleteUser()
    {

        $id = $this->createUser();

        $this->assertNotEquals($id,null);

        $this->removeUser($id);

    }

    public function testCreateDuplicateUsernameNotAllowed()
    {

        $id = $this->createUser();

        $this->assertNotEquals($id,null);

        $did = $this->createUser(true);

        $this->assertEquals($did,null);

        $this->removeUser($id);

    }

    private function removeUser($id)
    {

        $url = 'api/user/' . $id;

        $resp = $this->call('DELETE',$url);

        $resp->assertStatus(200);

    }

    private function createUser($duplicate = false)
    {

        $userData = [
            'username' => 'unittesting',
            'password' => '12345678',
            'password2' => '12345678',
            'email' => 'unit@testing.com',
        ];

        if($duplicate == true){

            $respData = [
                'db' => null
            ];

        }else{

            $respData = [
                'num' => 1
            ];

        }

        $resp = $this->json('POST','api/user',$userData)
        ->seeJson($respData);

        $content = $resp->response->getContent();
        $json = json_decode($content,true);
        $id = $duplicate == true ? $json['db'] : $json['db']['id'];

        return $id;

    }

}