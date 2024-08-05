<?php

declare(strict_types=1);

namespace App\UI\Http\Api\Controller;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Application\UseCase\Snap\Create\CreateSnapUseCase;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(
    path: '/api/snap',
    name: 'api_snap_post',
    methods: Request::METHOD_POST,
    format: 'json'
)]
final class SnapPostController extends FilesnapAbstractController
{
    /**
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     * @throws FileNotFoundException
     */
    public function __invoke(
        CreateSnapUseCase $createSnapUseCase,
        UrlGeneratorInterface $router,
        Request $request
    ): JsonResponse {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile instanceof UploadedFile === false) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Missing file in body request');
        }

        $useCaseResponse = $createSnapUseCase(new CreateSnapRequest(
            $this->getAuthenticatedUser()->getId(),
            $uploadedFile->getClientOriginalName(),
            $uploadedFile->getMimeType(),
            $uploadedFile->getPathname(),
            $uploadedFile->getSize()
        ));

        $snap = $useCaseResponse->getSnap();
        $parameters = ['id' => $snap->getId()->toBase58()];

        $urls = [
            'original' => $router->generate(
                'client_snap_file_original',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'thumbnail' => $router->generate(
                'client_snap_file_thumbnail',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        if ($snap->isImage() === true) {
            $urls['webp'] = $router->generate(
                'client_snap_file_webp',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if ($snap->isVideo() === true) {
            $urls['webm'] = $router->generate(
                'client_snap_file_webm',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->json($urls);
    }
}
