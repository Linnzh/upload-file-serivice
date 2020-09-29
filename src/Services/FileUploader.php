<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;
    private $targetPath;
    private $finalHost;
    private $finalUrl;

    public function __construct(string $targetDirectory, ?string $targetHost)
    {
        // TODO: 如果配置了 OSS 文件服务，需要给予 创建目录+写入+读取 的权限
        // DONE: 需要根据配置文件的 文件上传域名 判断该上传至哪个文件夹（在同一个服务器，但与代码分开存放
        if (empty($targetDirectory)) {
            $targetDirectory = dirname(__FILE__, 3) . '/public/upload';
        }

        $targetDirectory = $targetDirectory . '/' . date('Y') . '/' . date('m') . '/';
        $targetDirectory = preg_replace('#\/{2,}#', '/', $targetDirectory);
        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }
        $this->targetDirectory = $targetDirectory;

        $this->finalHost = str_replace(dirname(__FILE__, 3) . '/public', '',  $this->targetDirectory);
        if (!empty($targetHost)) {
            $this->finalHost = $targetHost . '/' . $this->finalHost;
        }
    }

    /**
     * 文件上传
     *
     * @param UploadedFile $file
     * @return array
     */
    public function upload(UploadedFile $file): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $filename = $originalFilename . '.' . $file->guessExtension();
        $safeFileName = md5_file($file) . '.' . $file->guessExtension();

        try {
            $this->targetPath = $this->targetDirectory . $safeFileName;

            if (!file_exists($this->targetPath)) {
                $file->move($this->getTargetDirectory(), $safeFileName);
            }

            $this->finalUrl = preg_replace('#\/{2,}#', '/', $this->finalHost . '/' . $safeFileName);
            $this->finalUrl = str_replace(':/', '://', $this->finalUrl);

            $fileSize = round(filesize($this->targetPath) / 1024, 2);
            $mimeType = mime_content_type($this->targetPath);

            $info = [
                'file_name' => $filename,
                'file_path' => $this->targetPath,
                'file_url' => $this->finalUrl,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
            ];

            $imageSize = getimagesize($this->targetPath);
            if ($imageSize !== false) {
                $info['width'] = $imageSize[0];
                $info['height'] = $imageSize[1];
            }
        } catch (FileException $e) {
            throw new FileException($e->getMessage());
        }

        return $info;
    }

    /**
     * 获取-上传文件目录
     *
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
