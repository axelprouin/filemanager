<?php

namespace App\Services\FileManager;

use lsolesen\pel\PelExif;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelJpegComment;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelFormat;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelEntry;
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
        $keywordsValue = $exif->getTiff()->getIfd()->getEntry(PelTag::XP_KEYWORDS);

        if (empty($_GET['keyword'])) {
            return;
        }
        
        if (empty($keywordsValue)) {
            $newEntry = $exif->getTiff()->getIfd()->newEntryFromData(PelTag::XP_KEYWORDS, PelFormat::BYTE, null, new PelDataWindow());
            $newEntry->setValue($_GET['keyword']);
            $exif->getTiff()->getIfd()->addEntry($newEntry);
        } else {
            $keywordsValue->setValue($keywordsValue->getValue().';'.$_GET['keyword']);
        }

        $this->pelJpeg->saveFile(sprintf('%s/src/Services/FileManager/process/image.jpg', $this->parameterBag->get('kernel.project_dir')));
    }
}
