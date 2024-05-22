<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Controllers;

use DI\Attribute\Inject;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SevereHeadache\AuthService\Application\Core\AuthService;
use Slim\Exception\HttpBadRequestException;

class AuthController extends Controller
{
    #[Inject]
    protected AuthService $authService;

    public function index(Request $request, Response $response): Response
    {
        $accessToken = $request->getHeader('Authorization');
        if (empty($accessToken)) {
            return $this->form($request, $response->withStatus(401));
        }
        if (!$this->authService->verifyAccessToken(reset($accessToken))) {
            return $this->form($request, $response->withStatus(403));
        }

        return $response;
    }

    public function form(Request $request, Response $response): Response
    {
        ob_start();
        $title = env('APP_NAME');
        include(self::VIEWS_PATH . '/form.php');
        $content = ob_get_contents();
        ob_end_clean();

        $body = $response->getBody();
        $body->write(json_encode([
            'html' => $content,
        ]));

        return $response;
    }

    public function authenticate(Request $request, Response $response): Response
    {
        $params = (array) $request->getParsedBody();
        if (!isset($params['name']) || !isset($params['password'])) {
            throw new HttpBadRequestException($request);
        }

        if ($this->authService->authenticate($params['name'], $params['password'])) {
            $accessToken = $this->authService->issueAccessToken();
            $body = $response->getBody();
            $body->write(json_encode(['access_token' => $accessToken]));

            return $response;
        } else {
            return $this->form($request, $response->withStatus(403));
        }
    }
}
