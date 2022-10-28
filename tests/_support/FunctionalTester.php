<?php

namespace App\Tests;

use App\Entity\Account;
use App\Entity\AuthSource;
use App\Entity\AuthSourceLocal;
use App\Entity\Portal;
use App\Entity\Room;
use App\Facade\AccountCreatorFacade;
use Codeception\Actor;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

    public function havePortal(string $title): Portal
    {
        $authSource = new AuthSourceLocal();
        $this->haveInRepository($authSource, [
            'title' => 'Lokal',
            'enabled' => true,
            'default' => true,
            'createRoom' => true,
        ]);

        $portal = new Portal();
        $portal->addAuthSource($authSource);
        $this->haveInRepository($portal, [
            'title' => $title,
            'status' => 1,
        ]);

        return $portal;
    }

    public function haveAuthSource(Portal $portal, AuthSource $authSource, string $title): void
    {
        $this->haveInRepository($authSource, [
            'title' => $title,
            'enabled' => true,
            'default' => false,
            'createRoom' => true,
        ]);

        $portal->addAuthSource($authSource);
        $this->haveInRepository($portal);
    }

    public function haveAccount(Portal $portal, string $username): Account
    {
        /** @var AuthSourceLocal $localAuthSource */
        $localAuthSource = $portal->getAuthSources()->filter(function (AuthSource $authSource) {
            return $authSource->getType() === 'local';
        })->first();

        /** @var Account $account */
        $account = $this->make(Account::class, [
            'authSource' => $localAuthSource,
            'contextId' => $portal->getId(),
            'username' => $username,
        ]);

        /** @var AccountCreatorFacade $accountFacade */
        $accountFacade = $this->grabService(AccountCreatorFacade::class);
        $accountFacade->persistNewAccount($account);

        $this->grabEntityFromRepository(Account::class, [
            'username' => $username,
        ]);

        return $account;
    }

    public function haveRoom(string $title, Portal $portal, $additionalParams = []): Room
    {
        $params = [
            'contextId' => $portal->getId(),
            'creator_id' => 99,
            'modifier_id' => 99,
            'title' => $title,
            'status' => 1,
        ];

        if (!empty($additionalParams)) {
            $params = array_merge($params, $additionalParams);
        }

        $room = new Room();
        $this->haveInRepository($room, $params);

        return $room;
    }
}
