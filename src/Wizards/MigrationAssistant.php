<?php

namespace Etsy\Wizards;

use Etsy\Wizards\SettingsHandlers\MigrationAssistantSettingsHandler;
use Plenty\Modules\Wizard\Services\WizardProvider;
use Plenty\Plugin\Application;

class MigrationAssistant extends WizardProvider
{

    protected function structure(): array
    {
        return [
            'key' => 'etsy-migration-assistant',
            'title' => 'wizard.title',
            'shortDescription' => 'wizard.shortDescription',
            'translationNamespace' => 'Etsy',
            'topics' => ['omni-channel'],
            'iconPath' => $this->getIcon(),
            'settingsHandlerClass' => MigrationAssistantSettingsHandler::class,
            'steps' => [
                'step0' => [
                    'title' => 'wizard.step0Title',
                    'description' => 'wizard.step0Description',
                ],
                'step1' => [
                    'title' => 'wizard.step1Title',
                    'description' => 'wizard.step1Description',
                    "sections" => [
                        [
                            "title" => "wizard.sectionTitle1",
                            "form" => [
                                "checkbox" => [
                                    "type" => "checkbox",
                                    "options" => [
                                        "name" => "wizard.checkboxName"
                                    ]
                                ],
                            ]
                        ],
                    ],
                ],
                'step2' => [
                    'title' => 'wizard.step2Title',
                    'description' => 'wizard.step2Description'
                ]
            ]
        ];
    }

    public function getIcon()
    {
        $path = '/images/etsy_logo.png';
        /** @var Application $app */
        $app = pluginApp(Application::class);
        $icon = $app->getUrlPath('Etsy');

        $iconPath = $icon . $path;

        return $iconPath;
    }
}