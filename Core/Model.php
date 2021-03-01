<?php

namespace Core;

use PDO;

/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class Model
{
    /******************************************************\
     * Function Name: 	connectToPdo()
     * Task: 		    Create connection to database
     *                  using PHP native PDO
     * Arguments: 		string $pdoDbType (database type)
     *                  string $pdoHost (host IP address)
     *                  string $pdoDb (database name)
     *                  string $pdoUser (database userId)
     *                  string $pdoPass (database password)
     * Globals: 		all defined in config.php
     * Returns: 		$pdoMdbLink or $pdoPgLink
    \******************************************************/
    public static function connectToPdo($pdoDbType, $pdoHost, $pdoDb, $pdoUser, $pdoPass, $pdoOptions="") {
        global $pdoMdbLink, $pdoPgLink, $pdoSqlLink;
        if ($pdoOptions != "") {
            $options = $pdoOptions;
        } else {
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
        }
        switch($pdoDbType) {
            case "mysql":
                if (!extension_loaded('pdo_mysql')) {
                    echo("ERROR: PDO extension for mySQL not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType . ":host=" . $pdoHost . ";dbname=" . $pdoDb;
                    $options = array_merge($options, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                        PDO::ATTR_PERSISTENT => true));
                    try {
                        $pdoMdbLink = new PDO($connStr, $pdoUser, $pdoPass, $options);
                    } catch (PDOException $e) {
                        echo "ERROR : " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
                        die();
                    }
                    return $pdoMdbLink;
                }
                break;
            case "oci": // Oracle
                if (!extension_loaded('pdo_oci')) {
                    echo("ERROR: PDO extension for Oracle Call Interface (OCI) not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType . ":host=" . $pdoHost . ";dbname=" . $pdoDb;
                    try {
                        $pdoOciLink = new PDO($connStr, $pdoUser, $pdoPass, $options);
                    } catch (PDOException $e) {
                        echo "ERROR : " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
                        die();
                    }
                    return $pdoOciLink;
                }
                break;
            case "odbc":
                if (!extension_loaded('pdo_odbc')) {
                    echo("ERROR: PDO extension for ODBC not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType . ":host=" . $pdoHost . ";dbname=" . $pdoDb;
                    try {
                        $pdoOdbcLink = new PDO($connStr, $pdoUser, $pdoPass, $options);
                    } catch (PDOException $e) {
                        echo "ERROR : " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
                        die();
                    }
                    return $pdoOdbcLink;
                }
                break;
            case "pgsql": // PostgreSQL
                if (!extension_loaded('pdo_pgsql')) {
                    echo("ERROR: PDO extension for PostgreSQL not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType . ":host=" . $pdoHost . ";dbname=" . $pdoDb;
                    try {
                        $pdoPgLink = new PDO($connStr, $pdoUser, $pdoPass, $options);
                    } catch (PDOException $e) {
                        echo "ERROR : " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
                        die();
                    }
                    return $pdoPgLink;
                }
                break;
            case "sqlite":
                if (!extension_loaded('pdo_sqlite')) {
                    echo("ERROR: PDO extension for SQLite not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType.":Server=".$pdoHost.";Database=".$pdoDb;
                    try {
                        $pdoSqlLink = new PDO($connStr, $pdoUser, $pdoPass);
                    } catch (PDOException $e) {
                        echo "ERROR : ".$e->getMessage()." (".$e->getCode().")<br>";
                        die();
                    }
                    return $pdoSqlLink;
                }
                break;
            case "sqlsrv":
                if (!extension_loaded('pdo_sqlsrv')) {
                    echo("ERROR: PDO extension for Microsoft SQL Server / SQL Azure not loaded!");
                    die();
                } else {
                    $connStr = $pdoDbType . ":Server=" . $pdoHost . ";Database=" . $pdoDb;
                    try {
                        $pdoSqlLink = new PDO($connStr, $pdoUser, $pdoPass);
                    } catch (PDOException $e) {
                        echo "ERROR : " . $e->getMessage() . " (" . $e->getCode() . ")<br>";
                        die();
                    }
                }
                return $pdoSqlLink;
                break;
            default:
                echo "Invalid or unsupported PDO Database Type.<br>";
                die();
        }
    } // End func connectToPdo()
}
