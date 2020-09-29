<?php

namespace App\Tests;

use Exception;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends WebTestCase
{
    private $client;
    private $filepath;
    private $uploadFile;
    private $resultFile;

    public function setup(): void
    {
        $this->filepath = tempnam(dirname(__FILE__, 2) . '/var/cache', 'upload_');
        $this->uploadFile = new UploadedFile(
            $this->filepath,
            basename($this->filepath),
            mime_content_type($this->filepath),
            null
        );
        $this->resultFile = dirname(__FILE__, 2) . '/var/result/'. date('YmdHis') . '.html';
        if(!file_exists(dirname($this->resultFile))) {
            mkdir(dirname($this->resultFile), 0777, true);
        }
    }

    public function testSomething()
    {
        $this->client = static::createClient();
        $this->client->request('POST', '/upload', [], [
            'upload[]' => $this->uploadFile,
            'upload[]' => $this->uploadFile,
        ]);

        $response = $this->client->getResponse();
        if ($response->getStatusCode() !== 200) {
            file_put_contents($this->resultFile, $response->getContent());
            throw new Exception($response->getStatusCode());
        }
        $content = $response->getContent();
        print_r($content);

        $this->assertJson($content, '返回的信息不是 JSON 数组！' . PHP_EOL . $content);
    }

    public function testByGuzzleHttp()
    {
        $client = new Client([
            'base_uri' => ''
        ]);
        $multipart = [];
        $multipart[] = [
            'name'     => 'upload',
            'contents' => fopen($this->filepath, 'r'),
            'filename' => basename($this->filepath),
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
        ];

        $this->markTestSkipped(
            '这里要求绝对路径，出错，暂时无法获取绝对路径'
        );

        // ! 这里要求绝对路径，出错，暂时无法获取绝对路径
        $response = $client->request('POST', '/upload', [
            'multipart' => $multipart
        ]);

        $response = $response->getBody()->getContents();

        $this->assertJson($response, '返回的信息不是 JSON 数组！' . PHP_EOL . $response);
    }

    public function tearDown(): void
    {
        @unlink($this->filepath);
    }
}
