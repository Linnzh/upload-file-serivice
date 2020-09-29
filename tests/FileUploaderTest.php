<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends WebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();

        $filepath = dirname(__FILE__, 2).'/.phpunit.result.cache';
        $file = new UploadedFile(
            $filepath,
            basename($filepath),
            mime_content_type($filepath),
            null
        );
        $client->request('POST', '/upload', [], [
            'upload[]'=>$file,
            'upload[]'=>$file,
        ]);

        $response = $client->getResponse()->getContent();
        print_r($response);

        $this->assertJson($response, '返回的信息不是数组！'.$response);
    }
}
