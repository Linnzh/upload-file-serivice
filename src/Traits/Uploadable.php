<?php

namespace App\Traits;

use App\Services\FileUploader;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait Uploadable
{
    /**
     * @Route("", name="upload")
     *
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function upload(Request $request, FileUploader $fileUploader): Response
    {
        $uploads = $request->files->all();

        if (empty($uploads)) {
            // throw new Exception('上传文件为空！'.json_encode($uploads));
            return $uploads;
        }

        if (!empty($labelName)) {
            $uploads = $request->files->get($labelName);
        }

        $fileinfo = [];
        foreach ($uploads as $upload) {
            if (is_array($upload)) {
                foreach ($upload as $file) {
                    $fileinfo[] = $fileUploader->upload($file);
                }
            } else {
                $fileinfo = $fileUploader->upload($upload);
            }
        }

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST',
        ];

        if (empty($fileinfo)) {
            return new JsonResponse([
                'msg' => '上传文件为空或者未能成功接收文件！请检查！',
                'code' => -1,
                'status' => false,
            ], 300);
        }

        return new JsonResponse($fileinfo, 200, $headers);
    }
}
