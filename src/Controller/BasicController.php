<?php

namespace App\Controller;

use App\Traits\Uploadable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BasicController extends AbstractController
{
    use Uploadable;

    /**
     * @Route("/basic", name="basic")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BasicController.php',
        ]);
    }
}
