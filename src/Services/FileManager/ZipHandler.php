<?php

namespace App\Services\FileManager;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ZipHandler
{
    const PROCESS_DIRECTORY = 'src/Services/FileManager/process';

    /**
     * @var \ZipArchive
     */
    private $zipArchive;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->zipArchive = new \ZipArchive();
        $this->parameterBag = $parameterBag;
    }

    /**
     * @param string $file
     * @param string $folder
     */
    public function extract(string $file, string $folder)
    {
        $this->zipArchive->open($file);
        $this->zipArchive->extractTo(sprintf('%s', $folder));
        unlink($file);
    }

    /**
     * @param string $folder
     * @param string $destination
     *
     * @return string $location
     */
    public function archive(string $folder, string $destination): string
    {
        if (is_dir($folder)) {
            $files = scandir($folder);
            //remove . and .. paths
            array_shift($files);
            array_shift($files);

            foreach ($files as $key => $fileName) {
                $files[$key] = sprintf('%s/%s', $folder, $fileName);
            }
        } else {
            $files = [$folder];
        }

        // create empty file
        fopen($destination, 'w');
        $this->zipArchive->open($destination);

        foreach ($files as $file) {
            $this->zipArchive->addFile($file, $file);
        }

        $this->zipArchive->close();
        $this->deleteDir($folder);

        return $destination;
    }

    /**
     * @param string $dirPath
     */
    private function deleteDir(string $dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }

        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dirPath);
    }

    public function getProcessDirectory()
    {
        return sprintf('%s/%s', $this->parameterBag->get('kernel.project_dir'), ZipHandler::PROCESS_DIRECTORY);
    }
}
