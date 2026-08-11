<?php

namespace Tests\Builder\Auth;

/**
 * Sanity check: the AuthyTestCase boots Propel against the test DB,
 * the Authy table exists with the expected schema, and the per-test
 * transaction wrap actually rolls back.
 *
 * Failures here mean integration tests would fail for infrastructure
 * reasons — keep this test green first.
 */
final class BootstrapConnectivityTest extends AuthyTestCase
{
    public function test_propel_connects_to_test_db_not_prod(): void
    {
        $con = \Propel::getConnection(_DATA_SRC);
        $dbname = $con->query('SELECT DATABASE()')->fetchColumn();

        $this->assertSame(
            'gc_apigtbot_test',
            $dbname,
            'Tests must run against gc_apigtbot_test, not the live DB.'
        );
    }

    public function test_authy_table_exists_with_expire_column(): void
    {
        $con = \Propel::getConnection(_DATA_SRC);
        $row = $con->query("SHOW COLUMNS FROM authy LIKE 'expire'")->fetch();

        $this->assertNotEmpty($row, 'authy.expire column required for the expired-user case.');
    }

    public function test_inserted_row_is_rolled_back_in_teardown(): void
    {
        $con = \Propel::getConnection(_DATA_SRC);
        $marker = 'test-rollback-' . uniqid();

        // The test DB is provisioned schema-only (mysqldump --no-data), so no
        // authy_group row exists for the authy.id_authy_group FK. Insert the
        // parent group inside this same transaction (rolled back in tearDown
        // with everything else) so the rollback probe doesn't depend on seed
        // data. INSERT IGNORE keeps it a no-op when a project's test DB already
        // has group 1.
        $con->exec(
            "INSERT IGNORE INTO authy_group (id_authy_group, name, default_group, admin)
             VALUES (1, 'test-bootstrap-group', 0, 0)"
        );

        $con->exec(sprintf(
            "INSERT INTO authy (username, fullname, email, passwd_hash, deactivate, is_root, is_system, id_authy_group)
             VALUES (%s, 'rollback test', 'rollback@example.invalid', 'x', 0, 0, 0, 1)",
            $con->quote($marker)
        ));

        $count = (int) $con->query(sprintf(
            "SELECT COUNT(*) FROM authy WHERE username = %s",
            $con->quote($marker)
        ))->fetchColumn();

        $this->assertSame(1, $count, 'INSERT should be visible inside the transaction.');
    }
}
