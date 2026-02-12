use PHPUnit\Framework\TestCase;
use DBA;

final class AbstractModelFactoryTest extends TestCase {
    public function testGetDBWithTest(): void {
        $db = Factory::getAgentFactory()->getDB(true);

        $this->assertSame($db, null);
    }

    public function testSimpleFilter(): void {
        $qF = new QueryFilter(User::USER_ID, 99999, "=");
        $user = Factory::getUserFactory()->filter([Factory::FILTER => $qF]);

        $this->assertSame($user, null);
    }
}
