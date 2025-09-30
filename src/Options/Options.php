<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Options;

use InvalidArgumentException;
use Laminas\Stdlib\AbstractOptions;

use function array_is_list;
use function is_array;
use function is_int;

/**
 * @template TValue
 * @extends AbstractOptions<TValue>
 */
class Options extends AbstractOptions
{
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable

    protected bool $enableUserState     = false;
    protected int $defaultUserState     = 1;
    protected array $allowedLoginStates = [null, 1];
    protected int $passwordCost         = 14;

    /** @var string[]  */
    protected array $authIdentityFields = ['email'];

    /*
        protected string $userEntityClass = User::class;

        protected string $tableName = 'user';

        protected string $idFieldName = 'id';

        protected string $rolesDelimiter = ',';
    */
    /** @var array<ChainableAdapterConfig> */
    protected array $authAdapters = [];

/*
    public function setUserEntityClass(string $userEntityClass): Options
    {
        Assert::classExists($userEntityClass);
        Assert::implementsInterface($userEntityClass, UserInterface::class);
        $this->userEntityClass = $userEntityClass;
        return $this;
    }

    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    public function setTableName(string $tableName): Options
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }
*/
    public function getEnableUserState(): bool
    {
        return $this->enableUserState;
    }

    public function setEnableUserState(bool $enableUserState): self
    {
        $this->enableUserState = $enableUserState;
        return $this;
    }

    public function getDefaultUserState(): int
    {
        return $this->defaultUserState;
    }

    public function setDefaultUserState(int $defaultUserState): self
    {
        $this->defaultUserState = $defaultUserState;
        return $this;
    }

    public function getAllowedLoginStates(): array
    {
        return $this->allowedLoginStates;
    }

    public function setAllowedLoginStates(array $allowedLoginStates): self
    {
        $this->allowedLoginStates = $allowedLoginStates;
        return $this;
    }

    public function getAuthIdentityFields(): array
    {
        return $this->authIdentityFields;
    }

    public function setAuthIdentityFields(array $authIdentityFields): self
    {
        $this->authIdentityFields = $authIdentityFields;
        return $this;
    }

    public function getPasswordCost(): int
    {
        return $this->passwordCost;
    }

    public function setPasswordCost(int $passwordCost): self
    {
        $this->passwordCost = $passwordCost;
        return $this;
    }
    /**
     * @param array<array-key, mixed> $authAdaptersConfig
     * @return $this
     */
    public function setAuthAdapters(iterable $authAdaptersConfig): Options
    {
        if (array_is_list($authAdaptersConfig)) {
            throw new InvalidArgumentException('Authentication adapter configuration cannot be a list array');
        }

        /**
         * @var ?int $priority
         * @var  string|array<string,mixed> $authAdapterConfigOrName
         */
        foreach ($authAdaptersConfig as $priority => $authAdapterConfigOrName) {
            if (! is_int($priority)) {
                throw new InvalidArgumentException('Authentication adapter priority is not an integer');
            }
            $authAdapterConfig = [];
            if (! is_array($authAdapterConfigOrName)) {
                $authAdapterConfigOrName = ['name' => $authAdapterConfigOrName];
            }
            if (! isset($authAdapterConfigOrName['name'])) {
                throw new InvalidArgumentException('Authentication adapter configuration key "name" is missing');
            }
            $authAdapterConfig['name']     = $authAdapterConfigOrName['name'];
            $authAdapterConfig['priority'] = $priority ?? ChainableAdapterConfig::DEFAULT_PRIORITY;
            $authAdapterConfig['options']  = $authAdapterConfigOrName['options'] ?? [];
            $this->authAdapters[]          = new ChainableAdapterConfig($authAdapterConfig);
        }
        return $this;
    }

    /**
     * @return array<ChainableAdapterConfig>
     */
    public function getAuthAdapters(): array
    {
        return $this->authAdapters;
    }

    public function findAuthAdapterByName(string $name): ?ChainableAdapterConfig
    {
        foreach ($this->authAdapters as $authAdapterConfig) {
            if ($authAdapterConfig->getName() === $name) {
                return $authAdapterConfig;
            }
        }
        return null;
    }

    /*
    public function getIdFieldName(): string
    {
        return $this->idFieldName;
    }

    public function setIdFieldName(string $idFieldName): self
    {
        $this->idFieldName = $idFieldName;
        return $this;
    }

    public function getRolesDelimiter(): string
    {
        return $this->rolesDelimiter;
    }

    public function setRolesDelimiter(string $rolesDelimiter): self
    {
        if (strlen($rolesDelimiter) > 0) {
            $this->rolesDelimiter = $rolesDelimiter;
        }
        return $this;
    }
*/
}
