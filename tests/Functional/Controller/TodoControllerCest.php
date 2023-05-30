<?php


namespace Tests\Functional\Controller;

use Tests\Support\FunctionalTester;
use Tests\Support\Page\Functional\Room;
use Tests\Support\Step\Functional\Root;
use Tests\Support\Step\Functional\User;

class TodoControllerCest
{
    private int $roomId;

    public function _before(Root $R, User $U, Room $roomPage)
    {
        $R->loginAndCreatePortalAsRoot();
        $R->goToLogoutPath();

        $U->registerAndLoginAsUser(1);
        $roomPage->create(1, 'Testraum');
        $this->roomId = $U->grabFromCurrentUrl('~^/portal/\d+/room/(\d+)~');
    }

    public function create(FunctionalTester $I)
    {
        $I->amOnRoute('app_todo_create', [
            'roomId' => $this->roomId,
        ]);
        $I->seeResponseCodeIsSuccessful();
    }

    public function detail(FunctionalTester $I)
    {
        $I->amOnRoute('app_todo_create', [
            'roomId' => $this->roomId,
        ]);
        $I->seeResponseCodeIsSuccessful();

        $itemId = $I->grabFromCurrentUrl('~^/room/\d+/todo/(\d+)~');
        $I->amOnRoute('app_todo_detail', [
            'roomId' => $this->roomId,
            'itemId' => $itemId,
        ]);
        $I->seeResponseCodeIsSuccessful();

        // Forbidden
        $I->goToLogoutPath();
        $I->stopFollowingRedirects();
        $I->amOnRoute('app_todo_detail', [
            'roomId' => $this->roomId,
            'itemId' => $itemId,
        ]);
        $I->seeResponseCodeIsRedirection();
    }

    public function edit(FunctionalTester $I)
    {
        $I->amOnRoute('app_todo_create', [
            'roomId' => $this->roomId,
        ]);
        $I->seeResponseCodeIsSuccessful();

        $itemId = $I->grabFromCurrentUrl('~^/room/\d+/todo/(\d+)~');
        $I->amOnRoute('app_todo_edit', [
            'roomId' => $this->roomId,
            'itemId' => $itemId,
        ]);
        $I->seeResponseCodeIsSuccessful();
    }

    public function feed(FunctionalTester $I)
    {
        $I->amOnRoute('app_todo_feed', [
            'roomId' => $this->roomId,
        ]);
        $I->seeResponseCodeIsSuccessful();
    }

    public function list(FunctionalTester $I)
    {
        $I->amOnRoute('app_todo_list', [
            'roomId' => $this->roomId,
        ]);
        $I->seeResponseCodeIsSuccessful();
    }
}
