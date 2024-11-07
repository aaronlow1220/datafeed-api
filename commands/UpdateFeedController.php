<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

class UpdateFeedController extends Controller
{
    public $message = "hello world";

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'message',
        ]);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'm' => 'message',
        ]);
    }

    /**
     * Summary of actionIndex
     * 
     * @param string $message
     * @return void
     */
    public function actionIndex()
    {
        echo $this->message . "\n";

        echo $this->ansiFormat("Success\n", BaseConsole::BG_GREEN);
    }

    /**
     * Summary of actionUpdate
     * 
     * @return void
     */
    public function actionUpdate()
    {
        // echo "Updating feed...\n";

        echo $this->ansiFormat("Success\n", BaseConsole::BG_GREEN);
    }
}
