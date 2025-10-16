<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\Authentication\Result as AuthenticationResult;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Lmc\User\Authentication\ConfigProvider;
use Lmc\User\Authentication\Options\Options;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Mezzio\Session\RetrieveSession;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;

use function array_shift;
use function assert;
use function count;
use function explode;
use function in_array;
use function is_object;
use function password_hash;
use function password_verify;

use const PASSWORD_BCRYPT;

class Db extends AbstractChainableAdapter implements ListenerAggregateInterface
{
    /** @var callable|null  */
    protected $credentialPreprocessor;

    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly Options $options,
    ) {
    }

    /**
     * Called when user id logged out
     */
    public function logout(AdapterChainEvent $event): void
    {
        $request = $event->getRequest();
        assert($request instanceof ServerRequestInterface);
        $session = RetrieveSession::fromRequest($request);
        $this->setSession($session);
        $this->clearStorage($session);
    }

    /**
     * Called when authentication adapter is reset
     */
    public function reset(AdapterChainEvent $event): void
    {
//        $this->getStorage()->clear();
    }

    public function authenticate(AdapterChainEvent $event): bool
    {
        $request = $event->getRequest();
        assert($request instanceof ServerRequestInterface);
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $this->setSession($session);

        if ($this->isSatisfied()) {
            /** @var array $storage */
            $storage = $this->session->get(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE);
            $event->setIdentity($storage['identity'])
                ->setCode(AuthenticationResult::SUCCESS)
                ->setMessages(['Authentication successful.']);
            return true;
        }

        $params     = $request->getParsedBody();
        /** @var ?string $identity */
        $identity   = $params['identity'] ?? null;
        /** @var ?string $credential */
        $credential = $params['credential'] ?? null;

        if (null === $credential || null === $identity) {
            $event->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
                ->setMessages(['Invalid username or password']);
            $this->setSatisfied(false);
            return false;
        }
        $credential = $this->preProcessCredential($credential);

        /**
         * @var UserInterface|null $userObject
         */
        $userObject = null;

        // Cycle through the configured identity sources and test each
        $fields = $this->options->getAuthIdentityFields();
        while (! is_object($userObject) && count($fields) > 0) {
            $mode = array_shift($fields);

            switch ($mode) {
                case 'username':
                    $userObject = $this->adapter->findByUsername($identity);
                    break;
                case 'email':
                    $userObject = $this->adapter->findByEmail($identity);
                    break;
            }
        }

        if (! $userObject) {
            $event->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)
                ->setMessages(['Invalid username or password']);
            $this->setSatisfied(false);
            return false;
        }

        if ($this->options->getEnableUserState()) {
            // Don't allow user to login if state is not in allowed list
            if (! in_array($userObject->getState(), $this->options->getAllowedLoginStates())) {
                $event->setCode(AuthenticationResult::FAILURE_UNCATEGORIZED)
                    ->setMessages(['The user is not allowed to login']);
                $this->setSatisfied(false);
                return false;
            }
        }

        if (! password_verify($credential, $userObject->getPassword())) {
            // Password does not match
            $event->setCode(AuthenticationResult::FAILURE_CREDENTIAL_INVALID)
                ->setMessages(['Invalid username or password']);
            $this->setSatisfied(false);
            return false;
        }

        // regen the session
        $request = $event->getRequest();
        $session = $request->getAttribute(SessionMiddleware::class);
        if ($session instanceof SessionInterface) {
            $session->regenerate();
        }

        // Success!
        $event->setIdentity($userObject->getIdentity());
        // Update user's password hash if the cost parameter has changed
        $this->updateUserPasswordHash($userObject, $credential);
        $this->setSatisfied(true);
        /** @var array $storage */
        $storage = $this->session->get(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE);
        $storage['identity'] = $event->getIdentity();
        $this->session->set(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage);
        $event->setCode(AuthenticationResult::SUCCESS)
            ->setMessages(['Authentication successful.']);
        return true;
    }

    protected function updateUserPasswordHash(UserInterface $userObject, string $password): void
    {
        $hash = explode('$', $userObject->getPassword());
        if ($hash[2] === (string) $this->options->getPasswordCost()) {
            return;
        }
        $userObject->setPassword(password_hash(
            $password,
            PASSWORD_BCRYPT,
            [
                'cost' => $this->options->getPasswordCost(),
            ]
        ));
        $this->adapter->update($userObject);
    }

    public function preProcessCredential($credential): mixed
    {
        if (null !== $this->credentialPreprocessor) {
            return ($this->credentialPreprocessor)($credential);
        }
        return $credential;
    }

    public function setCredentialPreprocessor(callable $credentialPreprocessor): self
    {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $listeners[] = $events->attach('authenticate', [$this, 'authenticate'], $priority);
        $listeners[] = $events->attach('logout', [$this, 'logout'], $priority);
        $listeners[] = $events->attach('reset', [$this, 'reset'], $priority);
    }

    public function detach(EventManagerInterface $events)
    {
    }

    protected function clearStorage(SessionInterface $session): void
    {
        $session->unset(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE);
    }
}
