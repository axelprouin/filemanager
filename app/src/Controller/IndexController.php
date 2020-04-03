<?php

namespace App\Controller;

use App\Services\FileManager\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @var FileManager
     */
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/meta", name="meta_manager")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function metaManager(Request $request)
    {
        $this->fileManager->addMeta();

        return $this->render('meta.html.twig');
    }
}
