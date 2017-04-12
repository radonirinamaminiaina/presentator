<?php
namespace api\tests\FunctionalTester;

use Yii;
use api\tests\FunctionalTester;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;

/**
 * VersionsController API functional test.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionsCest
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);

        // Authenticate user
        $this->user = User::findOne(1003);
        $I->haveHttpHeader('X-Access-Token', $this->user->generateJwtToken());
    }

    /* Index action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function indexUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to index action');
        $I->seeUnauthorizedAccess('/versions');
    }

    /**
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('List all user versions');
        $I->sendGET('/versions');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'projectId' => 'integer',
            'order'     => 'integer',
        ]);
    }

    /* Create action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function createUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to create action');
        $I->seeUnauthorizedAccess('/versions', 'POST');
    }

    /**
     * @param FunctionalTester $I
     */
    public function createError(FunctionalTester $I)
    {
        $I->wantTo('Wrong version create attempt');
        $I->sendPOST('/versions', ['projectId' => 1001]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'projectId' => 'string',
            ],
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Correct version create attempt');
        $I->sendPOST('/versions', ['projectId' => 1002]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'projectId' => 'integer:=1002',
            'order'     => 'integer',
        ]);
    }

    /* View action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function viewUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to view action');
        $I->seeUnauthorizedAccess('/versions/1004');
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to view unaccessible or other project version');
        $I->sendGET('/versions/1001');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Get project version');
        $I->sendGET('/versions/1004');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id'        => 1004,
            'projectId' => 1003,
            'order'     => 1,
        ]);
    }

    /* Delete action
    --------------------------------------------------------------- */
    /**
     * @param FunctionalTester $I
     */
    public function deleteUnauthorized(FunctionalTester $I)
    {
        $I->wantTo('Check unauthorized access to view action');
        $I->seeUnauthorizedAccess('/versions/1004', 'DELETE');
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteMissing(FunctionalTester $I)
    {
        $I->wantTo('Try to delete unaccessible or other project version');
        $I->sendDELETE('/versions/1001');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteTheOnlyOne(FunctionalTester $I)
    {
        $I->wantTo('Delete the only one project version');
        $I->sendDELETE('/versions/1003');
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Delete project version');
        $I->sendDELETE('/versions/1004');
        $I->seeResponseCodeIs(204);
    }
}
