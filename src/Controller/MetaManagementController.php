<?php

namespace App\Controller;

use App\Services\FileManager\WindowsKeyword;
use App\Services\FileManager\ZipHandler;
use lsolesen\pel\PelException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MetaManagementController extends AbstractController
{
    /**
     * @var WindowsKeyword
     */
    private $windowsKeyword;

    /**
     * @var ZipHandler
     */
    private $zipHandler;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(WindowsKeyword $windowsKeyword, ZipHandler $zipHandler, ParameterBagInterface $parameterBag)
    {
        $this->windowsKeyword = $windowsKeyword;
        $this->zipHandler = $zipHandler;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/metamanagement", name="metamanagement")
     *
     * @param Request $request
     *
     * @return Response
     * @throws PelException
     */
    public function metaManagement(Request $request)
    {
        $uploadedFile = ($request->files->get('file'));

        if (empty($uploadedFile)) {
            return $this->render('meta.html.twig');
        }

        $zipName = 'process.zip';
        $extractFolderName = uniqid();
        $uploadedFile->move($this->zipHandler->getProcessDirectory(), $zipName);
        $this->zipHandler->extract(
            sprintf('%s/%s', $this->zipHandler->getProcessDirectory(), $zipName),
            sprintf('%s/%s', $this->zipHandler->getProcessDirectory(), $extractFolderName)
        );

        $this->windowsKeyword->addMeta(sprintf('%s/%s', $this->zipHandler->getProcessDirectory(), $extractFolderName), true);

        $this->zipHandler->archive(
            sprintf('%s/%s', $this->zipHandler->getProcessDirectory(), $extractFolderName),
            sprintf('%s/%s.%s', $this->zipHandler->getProcessDirectory(), $extractFolderName, 'zip')
        );

        return $this->forward('App\Controller\MetaManagementController::download', [
            'key' => $extractFolderName
        ]);
    }

    /**
     * @Route("/download/{key}", name="download")
     * @param string $key
     *
     * @return mixed
     */
    public function download(string $key)
    {
        $fileName = sprintf('%s/%s.zip', $this->zipHandler->getProcessDirectory(), $key);
        if (!file_exists($fileName)) {
            return new JsonResponse(null, 404);
        }

        rename(
            sprintf('%s/%s.zip', $this->zipHandler->getProcessDirectory(), $key),
            sprintf('/tmp/%s.zip', $key)
        );

        return $this->file(sprintf('/tmp/%s.zip', $key));
    }
}
