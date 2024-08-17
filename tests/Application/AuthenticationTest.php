<?php

declare(strict_types=1);

namespace Tests\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use SevereHeadache\AuthService\Application\Core\AuthService;
use SevereHeadache\AuthService\Domain\Client;
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
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $client = new Client();
        $client->setName('test');
        $entityManager->persist($client);
        $user = new User();
        $user->register('test', 'test');
        $entityManager->persist($user);
        $user->setAccesses(new ArrayCollection([$client]));
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
        $this->assertTrue($authService->verifyAccessToken($accessToken, 'test'));
    }

    public function testJWT()
    {
        $app = $this->getAppInstance();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        $authService = $container->get(AuthService::class);
        $authService->authenticate('test', 'test');

        $request = $this->createRequest(
            'GET',
            '/',
            [
                'Authorization' => $authService->issueAccessToken(),
                'X-Client' => 'test',
            ],
        );
        $response = $app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
