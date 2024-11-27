<?php

namespace app\jobs;

use Yii;
use yii\queue\JobInterface;

class JobFactory
{
    /**
     * @param array<string, mixed> $params
     * @return JobInterface
     */
    public static function createDatafeedJob(array $params): JobInterface
    {
        return self::create(FileProcessJob::class, $params);
    }

    /**
     * create.
     *
     * @param string $class
     * @param array<string, mixed> $params
     * @return JobInterface
     */
    public static function create(string $class, array $params): JobInterface
    {
        $args = array_merge(['class' => $class], $params);

        return Yii::createObject($args);
    }

    /**
     * push to queue by particular name.
     *
     * @param JobInterface $job
     * @param string $queueName default queue
     * @return int
     */
    public static function push(JobInterface $job, string $queueName = 'queue'): int
    {
        return Yii::$app->get($queueName)->push($job);
    }
}
