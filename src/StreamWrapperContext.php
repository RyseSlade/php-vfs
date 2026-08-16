<?php

declare(strict_types=1);

namespace Aedon\VFS;

final class StreamWrapperContext
{
    public StreamWrapper $streamWrapper;
    public string $mode;
    public Node $node;
    public int $position = 0;
    public Lock|false $lock = false;

    public function __construct(StreamWrapper $streamWrapper, Node $node, string $mode)
    {
        $this->streamWrapper = $streamWrapper;
        $this->node = $node;
        $this->mode = $mode;
    }
}
