<?php

namespace App\Domains\Deploy;

use Ahc\Env\Loader;

class Config
{

    private $build_path;
    private $db = __DIR__;

    public function __construct()
    {
        $this->build_path =  $_SERVER["PWD"]."/.admin/";
        (new Loader)->load($_SERVER["PWD"] . '/.env');
        $this->db = new \MysqliDb (env('MY_DB_HOST'), env('MY_DB_USER'), env('MY_DB_PASSWORD'), env('MY_DB_NAME'));

        if ($this->db) {
            echo "\033[32mDatabase found\r\n";
        } else {
            echo "\033[31mDatabase error, please check your .env file (MY_DB_...)\r\n";
        }

        $this->runCustomSql();
        $this->setAdminUser();
    }

    private function run($cmd, $label)
    {
        exec($cmd . " 2>&1", $outputRs, $return_var);

        if ($return_var) {
            echo "\033[31m$label: NOT OK\r\n";
        } else {
            echo "\033[32m$label: OK\r\n";
        }
    }

    public function runCustomSql()
    {
        // SECURITY (review S4): pass the DB password via MYSQL_PWD in the process
        // environment (inherited by exec) instead of --password= on the command
        // line, which is visible to any local user via `ps`. finally unsets it.
        putenv('MYSQL_PWD=' . (string) env('MY_DB_PASSWORD'));
        try {
            if (file_exists($this->build_path . 'config/Built/basedata.sql') && !$this->checkBaseData()) {
                $restore = "/usr/bin/mysql -f -u " . env('MY_DB_USER') . " " . env('MY_DB_NAME') . " < " . $this->build_path . "config/Built/basedata.sql 2>&1";
                return $this->run($restore, "Additionnal SQL (base)");
            }

            if (file_exists($this->build_path . '/tmp/')) {
                if ($handle = opendir($this->build_path . '/tmp/')) {
                    while (false !== ($filename = readdir($handle))) {
                        if (strstr($filename, 'custom') && substr($filename, strrpos($filename, '.')) == '.sql') {

                            echo "\033[32mFound custom SQL : " . $filename."\r\n";
                            $restore = "/usr/bin/mysql -f -u " . env('MY_DB_USER') . " " . env('MY_DB_NAME') . " < " . $this->build_path . "tmp/" . $filename . " 2>&1";
                            return $this->run($restore, "Additionnal SQL");
                        }
                    }
                }
            }
        } finally {
            putenv('MYSQL_PWD');
        }
    }

    public function setAdminUser($password=null){
        $this->db->where ("is_system", '1');
        $admin = $this->db->getOne('authy');

        if (empty($admin)) {
            if ($this->db->insert ('authy', [
                'username' => 'apigoat',
                'passwd_hash' => password_hash((string)$password, PASSWORD_BCRYPT),
                'email' => 'info@apigoat.com',
                'is_root' => '0',
                'deactivate' => '1',
                'id_authy_group' => '2',
                'is_system' => '1',
                'id_creation' => null,
                'id_modification' => null,
                'date_creation' => date('Y-m-d H:i:s'),
            ]))
                echo "\033[32mCreate Admin user: OK\r\n";
            else
                echo "\033[31mCreate Admin user: NOT OK (" . $this->db->getLastError().")\r\n";
        } else {
            echo "\033[32mCreate Admin user: OK\r\n";
        }
    }

    private function checkBaseData()
    {
        $this->db->where ("config", 'app_name');
        $app_name = $this->db->getOne('config');
        if (!empty($app_name)) {
            return true;
        }
        return false;

    }

}