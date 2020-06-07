<?php
declare(strict_types=1);

namespace bin;

use App\Common\Kernel\AbstractCLIScript;
use Comely\Database\Database;
use Comely\Database\Schema;

/**
 * Class deploy_db
 * @package bin
 */
class deploy_db extends AbstractCLIScript
{
    public const DISPLAY_HEADER = false;
    public const DISPLAY_LOADED_NAME = true;

    /**
     * @throws \App\Common\Exception\AppConfigException
     * @throws \Comely\Database\Exception\DbConnectionException
     * @throws \Comely\Database\Exception\QueryBuildException
     * @throws \Comely\Database\Exception\QueryExecuteException
     * @throws \Comely\Database\Exception\SchemaTableException
     */
    public function exec(): void
    {
        // Primary Database
        $this->inline("Getting {invert}{yellow} primary {/} database ... ");
        $primary = $this->app->db()->primary();
        $this->print("{green}OK{/}");

        $this->createDbTables($primary, [
            'Database\Primary\Administrators',
        ]);
    }

    /**
     * @param Database $db
     * @param array $tables
     * @throws \Comely\Database\Exception\QueryBuildException
     * @throws \Comely\Database\Exception\QueryExecuteException
     * @throws \Comely\Database\Exception\SchemaTableException
     */
    private function createDbTables(Database $db, array $tables): void
    {
        $tablePrefix = 'App\Common';
        $loaded = [];

        $this->print("{grey}Fetching tables... {/}");
        foreach ($tables as $table) {
            $this->inline(sprintf('{cyan}%s{/} ... ', $table));
            $tableClass = $tablePrefix . '\\' . $table;
            $tableName = constant(sprintf('%s::NAME', $tableClass));
            Schema::Bind($db, $tableClass);
            $loaded[] = $tableName;
            $this->print("{green}OK{/}");
        }


        $this->print("");
        $this->print("Building database tables...");
        foreach ($loaded as $table) {
            $this->inline(sprintf('CREATE' . ' TABLE `{cyan}%s{/}` ... ', $table));
            $migration = Schema::Migration($table)->createIfNotExists()->createTable();

            $db->exec($migration);
            $this->print('{green}SUCCESS{/}');
        }
    }
}
