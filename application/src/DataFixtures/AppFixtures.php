<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Platform;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $application = [
            [
                'title' => 'App 1',
                'appCode' => 'app_1'
            ],
            [
                'title' => 'App 2',
                'appCode' => 'app_2'
            ],
        ];
        $appRepo = $manager->getRepository(Application::class);
        $appResponse = $appRepo->insertBulk($application);
        $platform = [];
        $type = ['ios','android'];
        foreach ($appResponse->getResponse() as $application)
        {
            if($application instanceof Application)
            {
                foreach ($type as $platformType)
                {
                    $platform[] = [
                        'title' => $application->getTitle().' - '.$platformType,
                        'app' => $application,
                        'code' => $application->getAppCode().'_'.$platformType,
                        'settings' => [],
                        'apiKey' => hash('sha256', $application->getAppCode().'_'.$platformType)
                    ];
                }
            }
        }
        $platformRepo = $manager->getRepository(Platform::class);
        $platformRepo->insertBulk($platform);
        $manager->flush();
    }
}
