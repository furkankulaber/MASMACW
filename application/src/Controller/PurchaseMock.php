<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/mock")
 */
class PurchaseMock extends AbstractController
{

    /**
     * @return JsonResponse
     * @Route("/google")
     * @Route("/apple")
     */
    public function googleMock(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $response = [
            'status' => false
        ];
        if(isset($data['receipt']))
        {

            $lastNumber = substr($data['receipt'],-1);
            if (is_numeric($lastNumber)&(!($lastNumber&1))) {
                $randDay = rand(1,30);
                $date = new \DateTime('now', new \DateTimeZone('America/Chicago'));
                $date->add(new \DateInterval('P'.$randDay.'D'));
                $response = [
                    'status' => true,
                    'expireAt' => $date->format(DATE_ATOM)
                ];
            }
        }


        return JsonResponse::fromJsonString(json_encode($response));
    }

}