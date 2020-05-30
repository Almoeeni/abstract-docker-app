<?php
declare(strict_types=1);

namespace App\Common\Config;

use App\Common\Config\AppConfig\CacheConfig;
use App\Common\Config\AppConfig\DbCred;
use App\Common\Exception\AppConfigException;
use Comely\Utils\Validator\Exception\InvalidValueException;
use Comely\Utils\Validator\Exception\ValidationException;
use Comely\Utils\Validator\Validator;
use Comely\Yaml\Yaml;

/**
 * Class AppConfig
 * @package App\Common\Config
 */
class AppConfig
{
    /** @var string */
    private string $adminHost;
    /** @var int */
    private int $adminPort;
    /** @var string|null */
    private ?string $adminHttpAuthUser;
    /** @var string|null */
    private ?string $adminHttpAuthPass;
    /** @var string */
    private string $apiHost;
    /** @var int */
    private int $apiPort;
    /** @var string */
    private ?string $mysqlRootPassword = null;
    /** @var array */
    private array $dbs = [];
    /** @var CacheConfig */
    private CacheConfig $cache;
    /** @var int */
    private int $timeStamp;

    /**
     * AppConfig constructor.
     * @throws AppConfigException
     * @throws \Comely\Yaml\Exception\ParserException
     */
    public function __construct()
    {
        // Read ENV vars
        try {
            /** @var string $adminHost */
            $adminHost = Validator::String($this->getEnv("ADMIN_HOST"))->validate(function (string $hostname) {
                $hostname = \App\Common\Validator::isValidHostname($hostname);
                if (!is_string($hostname)) {
                    throw new InvalidValueException();
                }

                return $hostname;
            });
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[ADMIN_HOST]: %s', get_class($e)));
        }

        $this->adminHost = $adminHost;

        try {
            /** @var int $adminPort */
            $adminPort = Validator::Integer($this->getEnv("ADMIN_PORT"))->range(1000, 0xffff)->validate();
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[ADMIN_PORT]: %s', get_class($e)));
        }

        $this->adminPort = $adminPort;

        try {
            $this->adminHttpAuthUser = Validator::String($this->getEnv("ADMIN_AUTH_USERNAME"))
                ->nullable()
                ->match('/^[\w\-\.]+$/i')
                ->validate();
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[ADMIN_AUTH_USERNAME]: %s', get_class($e)));
        }

        try {
            $this->adminHttpAuthPass = Validator::String($this->getEnv("ADMIN_AUTH_PASSWORD"))
                ->nullable()
                ->match('/^[a-z0-9@#~_\-.$^&*()]{4,32}$/i')
                ->validate();
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[ADMIN_AUTH_PASSWORD]: %s', get_class($e)));
        }

        try {
            /** @var string $apiHost */
            $apiHost = Validator::String($this->getEnv("API_HOST"))->validate(function (string $hostname) {
                $hostname = \App\Common\Validator::isValidHostname($hostname);
                if (!is_string($hostname)) {
                    throw new InvalidValueException();
                }

                return $hostname;
            });
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[API_HOST]: %s', get_class($e)));
        }

        $this->apiHost = $apiHost;

        try {
            /** @var int $apiPort */
            $apiPort = Validator::Integer($this->getEnv("API_PORT"))->range(1000, 0xffff)->validate();
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[API_PORT]: %s', get_class($e)));
        }

        $this->apiPort = $apiPort;

        // MySQL Root Password
        try {
            $this->mysqlRootPassword = Validator::String($this->getEnv("MYSQL_ROOT_PASSWORD"))
                ->nullable()
                ->match('/^[a-z0-9@#~_\-.$^&*()]{4,32}$/i')
                ->validate();
        } catch (ValidationException $e) {
            throw new AppConfigException(sprintf('ENV[MYSQL_ROOT_PASSWORD]: %s', get_class($e)));
        }

        // Read YAML files
        $configPath = "../../config/";
        $dbConfig = Yaml::Parse($configPath . "databases.yml")->generate();
        $dbIndex = -1;
        foreach ($dbConfig as $label => $args) {
            $dbIndex++;

            try {
                $label = Validator::String($label)->lowerCase()->match('/^\w{3,32}$/')->validate();
            } catch (ValidationException $e) {
                throw new AppConfigException(sprintf('DB[:%d]: [%s] Invalid label', $dbIndex, get_class($e)));
            }

            if (!is_array($args)) {
                throw new AppConfigException(sprintf('DB[%s]: Value must be of type Object', $label));
            }

            $dbCred = new DbCred($label, $args);
            $this->dbs[$label] = $dbCred;
        }

        $this->cache = new CacheConfig(Yaml::Parse($configPath . "cache.yml")->generate());

        // Timestamp
        $this->timeStamp = time();
    }

    /**
     * @return array|string[]
     */
    public function __debugInfo(): array
    {
        return ["Private AppConfig Object"];
    }

    /**
     * @param string $var
     * @return string
     */
    private function getEnv(string $var): string
    {
        return trim(strval(getenv($var)));
    }

    /**
     * @param string $label
     * @return DbCred|null
     */
    public function db(string $label): ?DbCred
    {
        return $this->dbs[strtolower($label)] ?? null;
    }

    /**
     * @return array
     */
    public function databases(): array
    {
        return $this->dbs;
    }

    /**
     * @return CacheConfig
     */
    public function cache(): CacheConfig
    {
        return $this->cache;
    }

    /**
     * @return string|null
     */
    public function mysqlRootPassword(): ?string
    {
        return $this->mysqlRootPassword;
    }

    /**
     * @return array|null
     */
    public function adminHttpAuth(): ?array
    {
        if ($this->adminHttpAuthUser && $this->adminHttpAuthPass) {
            return [$this->adminHttpAuthUser, $this->adminHttpAuthPass];
        }

        return null;
    }
}
