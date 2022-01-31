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
    public function createMock(Request $request)
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

    /**
     * @return JsonResponse
     * @Route("/google/check")
     * @Route("/apple/check")
     */
    public function checkMock(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $response = [
            'status' => false
        ];
        if(isset($data['receipt']))
        {

            $lastNumber = substr($data['receipt'],-1);
            $lastTwo = substr($data['receipt'],-2);
            if(($lastTwo % 6) === 0){
                $response = [
                    'status' => 'wait'
                ];
            }
            if (is_numeric($lastNumber)&(!($lastNumber&1)) && ($lastTwo % 6) !== 0) {
                $randDay = rand(-10,30);
                $date = new \DateTime('now', new \DateTimeZone('America/Chicago'));
                $randDay > 0 ? $date->add(new \DateInterval('P'.$randDay.'D')) : $date->sub(new \DateInterval('P'.ltrim($randDay,'-').'D'));
                $response = [
                    'status' => $randDay >= 0,
                    'expireAt' => $date->format(DATE_ATOM)
                ];
            }
        }


        return JsonResponse::fromJsonString(json_encode($response));
    }

}