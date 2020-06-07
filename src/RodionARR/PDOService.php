<?php

namespace RodionARR;

use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

/**
 * Class for calling MySQL stored procedures with multiple data sets in response
 * @package RodionARR
 */
class PDOService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface|object
     */
    private $connection;

    /**
     * PDOService constructor.
     * @param string $connection
     */
    public function __construct(string $connection = 'mysql')
    {
        $this->connection = DB::connection($connection);
    }

    /**
     * @param $storedProcedureName string - Name of Stored Procedure that needs to be called
     * @param $parameters array - Array of Values for Stored Procedure (They need to be in the correct order)
     * @return array $resultsets
     * Example:
     * Use RodionARR\PDOService;
     * $pdoService = new PDOService('myconnection');
     * $spParameters = [12345,'string'];
     * $spName = 'sp_MyStoredProcedure';
     * $spData = $pdoService->callStoredProcedure($spName,$spParameters);
     */
    public function callStoredProcedure(string $storedProcedureName, array $parameters = [])
    {
        if ($this->_checkStoredProcedure($storedProcedureName) == 0) {
            throw new RuntimeException($storedProcedureName.' - Stored Procedure does not exits');
        }

        /**
         * @var PDO $pdo
         */
        $pdo = $this->connection->getPdo();

        $parametersString = '';
        $parameterCount = count($parameters);
        // Dynamic Parameter String
        if ($parameterCount) {
            // Loop Parameters and add ? to parametersString
            for ($i = 0; $i < $parameterCount; $i++) {
                $parametersString .= '?';
                if ($i + 1 < $parameterCount) {
                    $parametersString .= ',';
                }
            }
        }

        $callString = "CALL $storedProcedureName($parametersString)";
        $statement = $pdo->prepare($callString);
        if ($parameterCount) {
            $pIndex = 1;
            for ($i = 0; $i < $parameterCount; $i++) {
                $paramValue = $parameters[$i];
                $statement->bindValue($pIndex, $paramValue, $this->_PDODataType($paramValue));
                $pIndex++;
            }
        }
        $statement->execute();
        $pdoDataResults = [];
        do {
            $rowSet = $statement->fetchAll(PDO::FETCH_ASSOC);
            array_push($pdoDataResults, $rowSet);
        } while ($statement->nextRowset());

        return $pdoDataResults;
    }

    /**
     * Check existence of stored procedure in specified connection
     * @param $procedureName
     * @return int
     */
    private function _checkStoredProcedure($procedureName)
    {
        $check = $this->connection
            ->table("information_schema.routines")
            ->where("SPECIFIC_NAME", "=", $procedureName)
            ->select("SPECIFIC_NAME")
            ->first();
        return count((array)$check);
    }

    /**
     * Contains mapping of PHP types to PDO statements
     * @param $value
     * @return int
     */
    private function _PDODataType($value)
    {
        if (is_null($value)) {
            return PDO::PARAM_NULL;
        }

        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }

        if (is_int($value)) {
            return PDO::PARAM_INT;
        }

        if (is_object($value)) {
            return PDO::PARAM_LOB;
        }

        return PDO::PARAM_STR;
    }
}
