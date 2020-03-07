<?php

namespace PhpLab\Bundle\Tests\rest\User;

use PhpLab\Core\Enums\Http\HttpStatusCodeEnum;
use PhpLab\Test\Base\BaseRestApiTest;

class AuthControllerTest extends BaseRestApiTest
{

    protected $basePath = 'api/v1/';

    public function testAuthBadPassword()
    {
        $expectedBody = [
            [
                'field' => 'password',
                'message' => 'Bad password',
            ]
        ];
        $response = $this->getRestClient()->sendPost('auth', [
            'login' => 'user1',
            'password' => 'Wwwqqq11133333',
        ]);
        $this->getRestAssert($response)
            ->assertStatusCode(HttpStatusCodeEnum::UNPROCESSABLE_ENTITY)
            ->assertBody($expectedBody);
    }

    public function testAuthNotFoundLogin()
    {
        $expectedBody = [
            [
                'field' => 'login',
                'message' => 'User not found',
            ]
        ];
        $response = $this->getRestClient()->sendPost('auth', [
            'login' => 'qwerty',
            'password' => 'Wwwqqq111',
        ]);
        $this->getRestAssert($response)
            ->assertStatusCode(HttpStatusCodeEnum::UNPROCESSABLE_ENTITY)
            ->assertBody($expectedBody);
    }

    public function testAuth()
    {
        $expectedBody = [
            'id' => 1,
            'username' => 'user1',
            'username_canonical' => 'user1',
            'email' => 'user1@example.com',
            'email_canonical' => 'user1@example.com',
            'roles' => [
                'ROLE_USER',
                'ROLE_ADMIN',
            ],
        ];
        $response = $this->getRestClient()->sendPost('auth', [
            'login' => 'user1',
            'password' => 'Wwwqqq111',
        ]);
        $this->getRestAssert($response)
            ->assertStatusCode(HttpStatusCodeEnum::OK)
            ->assertBody($expectedBody);

        /*$body = RestHelper::getBody($response);
        $this->assertNotEmpty(preg_match('#jwt\s[\s\S]+\.[\s\S]+\.[\s\S]+#i', $body['api_token']));
        $this->assertFalse(isset($body['password']));*/
    }

}
