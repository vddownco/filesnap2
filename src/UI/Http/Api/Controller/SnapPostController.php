<?php

declare(strict_types=1);

namespace App\UI\Http\Api\Controller;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Snap\Exception\UnsupportedFileTypeException;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Application\UseCase\Snap\Create\CreateSnapUseCase;
use App\Infrastructure\Symfony\Message\ConversionMessage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
     * @throws \Exception
     * @throws ExceptionInterface
     */
    public function __invoke(
        CreateSnapUseCase $createSnapUseCase,
        MessageBusInterface $bus,
        UrlGeneratorInterface $router,
        Request $request
    ): JsonResponse {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile instanceof UploadedFile === false) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Missing file in body request');
        }

        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        if ($mimeType === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Unable to determine file mimetype');
        }

        if ($size === false) {
            throw new \RuntimeException('Unable to determine file size');
        }

        $useCaseResponse = $createSnapUseCase(new CreateSnapRequest(
            $this->getAuthenticatedUser()->getId(),
            $uploadedFile->getClientOriginalName(),
            $mimeType,
            $uploadedFile->getPathname(),
            $size
        ));

        $snap = $useCaseResponse->getSnap();
        $parameters = ['id' => $snap->getId()->toBase58()];

        $formats = [
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
            $bus->dispatch(new ConversionMessage($snap->getId(), ConvertFormat::Webp));

            $formats['webp'] = $router->generate(
                'client_snap_file_webp',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $formats['preferred'] = $formats['webp'];
        }

        if ($snap->isVideo() === true) {
            $bus->dispatch(new ConversionMessage($snap->getId(), ConvertFormat::Webm));

            $formats['webm'] = $router->generate(
                'client_snap_file_webm',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $formats['preferred'] = $formats['webm'];
        }

        return $this->json([
            'id' => $snap->getId()->toBase58(),
            'formats' => $formats,
        ]);
    }
}
