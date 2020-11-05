<?php


namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class FileService
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    const UPLOADS_REPOSITORY = 'uploads';
    const GAME_REPOSITORY = 'game';

    protected $repositoriesToInit = [
        self::GAME_REPOSITORY,
    ];

    public function __construct(KernelInterface $kernel){
        $this->kernel = $kernel;
        $this->initUploadRepository();
    }

    /**
     * Create uploads repositories if they don't exists
     */
    private function initUploadRepository(){
        if(!empty($this->repositoriesToInit)){
            foreach($this->repositoriesToInit as $repository){
                if(!file_exists($this->getUploadDir() . DIRECTORY_SEPARATOR . $repository)){
                    mkdir($this->getUploadDir() . DIRECTORY_SEPARATOR . $repository,0777,true);
                }
            }
        }
    }

    /**
     * Get uploads directory path
     * @return string
     */
    public function getUploadDir(): string{
        return $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'var'. DIRECTORY_SEPARATOR . self::UPLOADS_REPOSITORY;
    }

    /**
     * Save a file to the filer in specified sub or uploads repository
     * and return
     * @param string $base64file
     * @return string
     */
    public function saveFileToFiler(string $base64file, string $subRepository = null): string{
        $extension = explode('/', mime_content_type($base64file))[1];
        $fileData = str_replace('data:image/' . $extension .';base64,', '', $base64file);
        $fileData = base64_decode($fileData);
        $filename = $this->getUploadDir() . DIRECTORY_SEPARATOR;
        if($subRepository) {
            $filename .= $subRepository . DIRECTORY_SEPARATOR;
        }
        $filename .= rand(100000,500000) . '.' . $extension;
        if(!file_put_contents($filename, $fileData)){
            throw new \DomainException('The file ' . $filename . ' was not properly saved.');
        }

        return (empty($subRepository)) ? DIRECTORY_SEPARATOR . basename($filename) : DIRECTORY_SEPARATOR . $subRepository . DIRECTORY_SEPARATOR . basename($filename);
    }

    /**
     * Get base64 file datas
     * @param string $path
     * @return string|null
     */
    public function getBase64FileDatas(string $path) {
        $filename = $this->getUploadDir() . $path;
        if(file_exists($filename)){
            $extension = pathinfo($filename,PATHINFO_EXTENSION);
            $file = file_get_contents($filename);
            if($file){
                return 'data:image/' . $extension . ';base64,'  . base64_encode($file);
            }
            return null;
        }

        return null;
    }

    /**
     * Delete a file from the filer
     * @param string $path
     * @return bool
     */
    public function deleteFileToFiler(string $path): bool{
        $path = $this->getUploadDir() . $path;
        if(file_exists($path)){
            return unlink($path,stream_context_get_default());
        }
        return false;
    }
}