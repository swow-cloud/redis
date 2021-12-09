<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/music-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\Redis\Lua;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use SwowCloud\Redis\Exception\RedisNotFoundException;

abstract class Script implements ScriptInterface
{
    /**
     * PHPRedis client or proxy client.
     *
     * @var mixed|Redis
     */
    protected mixed $redis;

    protected ?string $sha;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        if ($container->has(Redis::class)) {
            $this->redis = $container->get(Redis::class);
        }

        if ($container->has(LoggerInterface::class)) {
            $this->logger = $container->get(LoggerInterface::class);
        }
    }

    public function eval(array $arguments = [], $sha = true)
    {
        if ($this->redis === null) {
            throw new RedisNotFoundException('Redis client is not found.');
        }
        if ($sha) {
            $result = $this->redis->evalSha($this->getSha(), $arguments, $this->getKeyNumber($arguments));
            if ($result !== false) {
                return $this->format($result);
            }

            $this->sha = null;
            $this->logger && $this->logger->warning(sprintf('NOSCRIPT No matching script[%s]. Use EVAL instead.', static::class));
        }

        $result = $this->redis->eval($this->getScript(), $arguments, $this->getKeyNumber($arguments));

        return $this->format($result);
    }

    protected function getKeyNumber(array $arguments): int
    {
        return count($arguments);
    }

    protected function getSha(): string
    {
        if (!empty($this->sha)) {
            return $this->sha;
        }

        return $this->sha = $this->redis->script('load', $this->getScript());
    }
}
