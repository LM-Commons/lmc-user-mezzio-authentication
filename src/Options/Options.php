<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Options;

use InvalidArgumentException;
use Laminas\Stdlib\AbstractOptions;
use Lmc\User\Repository\UserInterface;

use function array_is_list;
use function is_array;
use function is_int;

/**
 * @template TValue
 * @extends AbstractOptions<TValue>
 */
final class Options extends AbstractOptions
{
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable

    protected bool $enableUserState        = false;
    protected int|string $defaultUserState = UserInterface::STATE_ACTIVE;

    /** @var list<string|null|int> */
    protected array $allowedLoginStates = [UserInterface::STATE_ACTIVE];

    /** @var string[]  */
    protected array $authIdentityFields = ['email'];

    /** @var array<ChainableAdapterConfig> */
    protected array $authAdapters = [];

    public function getEnableUserState(): bool
    {
        return $this->enableUserState;
    }

    public function setEnableUserState(bool $enableUserState): self
    {
        $this->enableUserState = $enableUserState;
        return $this;
    }

    public function getDefaultUserState(): int|string
    {
        return $this->defaultUserState;
    }

    public function setDefaultUserState(int|string $defaultUserState): self
    {
        $this->defaultUserState = $defaultUserState;
        return $this;
    }

    /**
     * @return list<int|null|string>
     */
    public function getAllowedLoginStates(): array
    {
        return $this->allowedLoginStates;
    }

    /**
     * @param list<string|int|null> $allowedLoginStates
     */
    public function setAllowedLoginStates(array $allowedLoginStates): self
    {
        $this->allowedLoginStates = $allowedLoginStates;
        return $this;
    }

    public function getAuthIdentityFields(): array
    {
        return $this->authIdentityFields;
    }

    /**
     * @param string[] $authIdentityFields
     */
    public function setAuthIdentityFields(array $authIdentityFields): self
    {
        $this->authIdentityFields = $authIdentityFields;
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
            $authAdapterConfig['priority'] = $priority;
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
}
