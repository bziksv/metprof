<?php
namespace Prime\Cleaner;

class Cleaner
{
    private ImageFinder $curImages;
    private CSVParser $dataFile;

    public function __construct(ImageFinder $curImages, CSVParser $dataFile)
    {
        $this->curImages = $curImages;
        $this->dataFile = $dataFile;
    }

    public function execute(): int
    {
        $usedFiles = $this->prepareDataFile();

        $count = 0;

        foreach ($this->curImages->getPathname() as $img) {
            $url = strtolower(PathParser::normalizeUrlPath($img));

            if (!array_key_exists($url, $usedFiles)) {
                if (\Bitrix\Main\IO\File::deleteFile($img)) {
                    $count += 1;
                }
            }
        }

        return $count;
    }

    private function prepareDataFile(): array
    {
        $paths = [];

        foreach ($this->dataFile->getImagesPath() as $path) {
            $relativePath = strtolower(URLParser::getRelativePath($path));
            $paths[$relativePath] = true;
        }

        return $paths;
    }
}
