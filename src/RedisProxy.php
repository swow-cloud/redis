<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/websocket-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\Redis;

use JetBrains\PhpStorm\Pure;
use SwowCloud\Redis\Pool\PoolFactory;

/**
 * @mixin \Redis
 */
class RedisProxy extends Redis
{
    protected string $poolName;

    #[Pure]
    public function __construct(PoolFactory $factory, string $pool)
    {
        parent::__construct($factory);

        $this->poolName = $pool;
    }
}
