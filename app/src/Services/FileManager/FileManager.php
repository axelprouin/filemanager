<?php

namespace App\Services\FileManager;

use lsolesen\pel\PelExif;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelJpegComment;
use lsolesen\pel\PelTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileManager
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var PelJpeg
     */
    private $pelJpeg;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->pelJpeg = new PelJpeg(sprintf('%s/src/Services/FileManager/process/image.jpg', $this->parameterBag->get('kernel.project_dir')));
    }

    public function extractDirectory(): array
    {
        return [];
    }

    public function addMeta()
    {
        $exif = $this->pelJpeg->getExif();
        $tiff = $exif->getTiff();
        $ifd = $tiff->getIfd();
        $keyword = $ifd->getEntry(PelTag::)

        dump($this->pelJpeg);
        dump(iptcparse(sprintf('%s/src/Services/FileManager/process/image.jpg', $this->parameterBag->get('kernel.project_dir'))));die;
    }
}
