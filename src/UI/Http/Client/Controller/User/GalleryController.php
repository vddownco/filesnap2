<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User;

use App\Application\UseCase\Snap\FindByUser\FindSnapsByUserRequest;
use App\Application\UseCase\Snap\FindByUser\FindSnapsByUserUseCase;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/u/gallery',
    name: 'client_user_gallery',
    methods: Request::METHOD_GET
)]
final class GalleryController extends FilesnapAbstractController
{
    private const int MAX_SNAPS_BY_PAGE = 45;

    public function __invoke(
        FindSnapsByUserUseCase $findSnapsByUserUseCase,
        #[MapQueryParameter] ?int $page
    ): Response {
        if ($page === null) {
            $page = 1;
        } elseif ($page < 1) {
            throw $this->createNotFoundException();
        }

        $useCaseResponse = $findSnapsByUserUseCase(new FindSnapsByUserRequest(
            $this->getAuthenticatedUser()->getId(),
            self::MAX_SNAPS_BY_PAGE * ($page - 1),
            self::MAX_SNAPS_BY_PAGE
        ));

        $snaps = $useCaseResponse->getSnaps();
        $snapsCount = $useCaseResponse->getTotalCount();

        if (
            ($snaps === [] && $snapsCount > 0)
            || ($page > 1 && $snapsCount === 0)
        ) {
            throw $this->createNotFoundException();
        }

        $pageCount = (int) ceil($snapsCount / self::MAX_SNAPS_BY_PAGE);
        $nextPage = $page === $pageCount || $snapsCount === 0 ? null : $page + 1;
        $previousPage = $page === 1 ? null : $page - 1;
        $emptySpaceCount = count($snaps) < self::MAX_SNAPS_BY_PAGE ? self::MAX_SNAPS_BY_PAGE - count($snaps) : 0;

        return $this->view([
            'snaps' => $snaps,
            'page' => $page,
            'next_page' => $nextPage,
            'previous_page' => $previousPage,
            'empty_space_count' => $emptySpaceCount,
        ]);
    }
}
