<?php

declare(strict_types=1);

namespace App\UI\Http\Api\Controller;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Snap\Exception\UnsupportedFileTypeException;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Infrastructure\UseCase\Snap\CreateSnapUseCaseDispatcher;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(
    path: '/api/snap',
    name: 'api_snap_post',
    methods: Request::METHOD_POST,
    format: 'json'
)]
final class SnapPostController extends FilesnapAbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    /**
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     * @throws FileNotFoundException
     * @throws \Exception
     * @throws ExceptionInterface
     */
    public function __invoke(
        CreateSnapUseCaseDispatcher $createSnapUseCase,
        UrlGeneratorInterface $router,
        Request $request
    ): JsonResponse {
        $uploadedFile = $request->files->get('file');
        $url = $request->request->get('url');

        if ($uploadedFile !== null && $url !== null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'You can\'t upload a file and from an url at the same time.');
        }

        $file = match (true) {
            $uploadedFile instanceof UploadedFile => $uploadedFile,
            is_string($url) => $this->getFileFromUrl($url),
            default => throw new HttpException(Response::HTTP_BAD_REQUEST, 'Missing file or url in body request')
        };

        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        if ($mimeType === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Unable to determine file mimetype');
        }

        if ($size === false) {
            throw new \RuntimeException('Unable to determine file size');
        }

        $useCaseResponse = $createSnapUseCase(new CreateSnapRequest(
            $this->getAuthenticatedUser()->getId(),
            $file->getClientOriginalName(),
            $mimeType,
            $file->getPathname(),
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
            $formats['webp'] = $router->generate(
                'client_snap_file_webp',
                $parameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $formats['preferred'] = $formats['webp'];
        }

        if ($snap->isVideo() === true) {
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

    private function getFileFromUrl(string $url): UploadedFile
    {
        try {
            $response = $this->httpClient->request(Request::METHOD_GET, $url);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw $this->imageRetrievingException();
            }
        } catch (\Throwable) {
            throw $this->imageRetrievingException();
        }

        try {
            $content = $response->getContent();
            $headers = $response->getHeaders();
        } catch (\Throwable) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error trying to get data from response.');
        }

        $tempFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), 'upload_from_url_');
        $this->filesystem->appendToFile($tempFilePath, $content);
        $tempFile = new File($tempFilePath);
        $originalName = sprintf('%s.%s', $tempFile->getBasename(), $tempFile->guessExtension());
        $contentDispositionHeader = $headers['content-disposition'][0] ?? null;

        if ($contentDispositionHeader !== null) {
            preg_match('/filename="([^"]+)"/', $contentDispositionHeader, $matches);
            $originalName = $matches[1] ?? $originalName;
        }

        return new UploadedFile($tempFilePath, $originalName);
    }

    private function imageRetrievingException(): HttpException
    {
        return new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error trying to get image from url.');
    }
}
