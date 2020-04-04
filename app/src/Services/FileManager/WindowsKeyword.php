<?php

namespace App\Services\FileManager;

use lsolesen\pel\PelException;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelInvalidArgumentException;
use lsolesen\pel\PelInvalidDataException;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelFormat;
use lsolesen\pel\PelDataWindow;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelUnexpectedFormatException;
use lsolesen\pel\PelWrongComponentCountException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class WindowsKeyword
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param string $file
     * @param bool $addFileNameAsMeta
     * @param string $keyword
     * @throws PelException
     */
    public function addMeta(string $file, $addFileNameAsMeta = false, $keyword = '')
    {
        if (!$addFileNameAsMeta && $keyword === '') {
            return;
        }

        if (is_dir($file)) {
            $files = scandir($file);
            array_shift($files);
            array_shift($files);

            foreach ($files as $key => $fileName) {
                if ($addFileNameAsMeta) {
                    $keyword = preg_replace('/\\.[^.\\s]{3,4}$/', '', $fileName);
                }

                $files[$key] = [
                    'path' => sprintf('%s/%s', $file, $fileName),
                    'fileName' => $fileName,
                    'keyword' => $keyword
                ];
            }
        } else {
            throw new \InvalidArgumentException('Add meta on simple file is not implemented yet');
        }

        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $pelJpeg = new PelJpeg($file['path']);

            $exif = $pelJpeg->getExif();
            if (empty($exif)) {
                $pelJpeg->setExif(new PelExif());
                $exif = $pelJpeg->getExif();
            }

            $tiff = $exif->getTiff();
            if (empty($tiff)) {
                $exif->setTiff(new PelTiff());
                $tiff = $exif->getTiff();
            }

            $ifd = $tiff->getIfd();
            if (empty($ifd)) {
                $tiff->setIfd(new PelIfd(PelIfd::IFD0));
                $ifd = $tiff->getIfd();
            }

            $keywordsValue = $ifd->getEntry(PelTag::XP_KEYWORDS);
            if (empty($keywordsValue)) {
                $newEntry = $ifd->newEntryFromData(PelTag::XP_KEYWORDS, PelFormat::BYTE, null, new PelDataWindow());
                $newEntry->setValue($file['keyword']);
                $exif->getTiff()->getIfd()->addEntry($newEntry);
            } else {
                $keywordsValue->setValue($keywordsValue->getValue().';'.$file['keyword']);
            }

            $pelJpeg->saveFile($file['path']);
        }
    }
}
