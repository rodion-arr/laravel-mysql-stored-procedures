<?php

namespace RodionARR\Tests;

use RodionARR\PDOService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;

class PDOServiceTest extends TestCase
{
    public function test_checks_stored_procedure_existence()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('non_existed_sp - Stored Procedure does not exits');

        $qbMock = $this->getQueryBuilderMock();
        $qbMock->method('first')
            ->willReturn([]);

        DB::shouldReceive('connection')
            ->with('test_db_connection_name')
            ->andReturn($qbMock);

        $service = new PDOService('test_db_connection_name');
        $service->callStoredProcedure('non_existed_sp', []);
    }

    public function test_call_stored_procedure_positive()
    {
        $statementMock = $this->getMockBuilder(stdClass::class)
            ->addMethods([
                'bindValue',
                'execute',
                'fetchAll',
                'nextRowset',
            ])
            ->getMock();
        $statementMock->expects($this->exactly(5))
            ->method('bindValue');
        $statementMock->expects($this->once())
            ->method('execute');
        $statementMock->expects($this->atLeastOnce())
            ->method('fetchAll')
            ->willReturn(['data']);
        $statementMock->expects($this->atLeastOnce())
            ->method('nextRowset');

        $pdoMock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['prepare'])
            ->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('CALL stored_procedure(?,?,?,?,?)')
            ->willReturn($statementMock);

        $qbMock = $this->getQueryBuilderMock();
        $qbMock->method('first')
            ->willReturn(['sp_found']);
        $qbMock->method('getPdo')
            ->willReturn($pdoMock);
        DB::shouldReceive('connection')
            ->with('test_db_connection_name')
            ->andReturn($qbMock);

        $service = new PDOService('test_db_connection_name');
        $dbResult = $service->callStoredProcedure(
            'stored_procedure',
            [null, true, 1, (new stdClass()), 'param']
        );

        $this->assertEquals([['data']], $dbResult);
    }
}
