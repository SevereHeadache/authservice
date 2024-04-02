<?php

declare(strict_types=1);

namespace Tests\Application;

use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Application\Core\AuthService;
use SevereHeadache\AuthService\Domain\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testForm()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET', '/authenticate');
        $response = $app->handle($request);

        $actual = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('html', $actual);
    }

    public function testAuthentication()
    {
        $app = $this->getAppInstance();
        /** @var \DI\Container $container */
        $container = $app->getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $user->register('test', 'test');
        $entityManager->persist($user);
        $entityManager->flush();

        $request = $this->createRequest(
            'POST',
            '/authenticate',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
        );
        $request->getBody()->write('name=test&password=test');
        $response = $app->handle($request);

        $actual = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $actual);

        $accessToken = $actual['access_token'];
        $authService = $container->get(AuthService::class);
        $this->assertTrue($authService->verifyAccessToken($accessToken));
    }

    public function testJWT()
    {
        $app = $this->getAppInstance();
        /** @var \DI\Container $container */
        $container = $app->getContainer();

        $authService = $container->get(AuthService::class);
        $authService->authenticate('test', 'test');

        $request = $this->createRequest(
            'GET',
            '/',
            ['Authorization' => $authService->issueAccessToken()],
        );
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
