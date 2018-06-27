<?php

function (): ?int {
    return 1;
    return 'foo';
    return null;
};

function (): iterable {
    return [];
    return 'foo';
    return new \ArrayIterator([]);
};

function (): void {
    return;
};
