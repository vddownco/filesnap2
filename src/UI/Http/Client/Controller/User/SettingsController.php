<?php
declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User;

use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/u/settings',
    name: 'client_user_settings',
    methods: Request::METHOD_GET
)]
final class SettingsController extends FilesnapAbstractController
{
    public function __invoke(): Response
    {
        return $this->render();
    }
}