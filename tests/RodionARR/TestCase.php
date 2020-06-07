<?php

namespace RodionARR\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class TestCase extends BaseTestCase
{
    public function getQueryBuilderMock(): MockObject
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods([
                'table',
                'select',
                'where',
                'get',
                'toArray',
                'first',
                'getPdo'
            ])
            ->getMock();

        $mock->method('table')
            ->willReturnSelf();
        $mock->method('select')
            ->willReturnSelf();
        $mock->method('where')
            ->willReturnSelf();
        $mock->method('get')
            ->willReturnSelf();
        $mock->method('toArray')
            ->willReturn([]);

        return $mock;
    }
}
