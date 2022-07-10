<?php

namespace Tests\Feature;

use App\Interfaces\Services\SessionsInterface;
use App\Models\Session;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionsTest extends TestCase
{
    use RefreshDatabase;

    protected SessionsInterface $sessions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessions = new \App\Services\Sessions();
    }

    /**
     * @throws Exception
     */
    public function testSingle()
    {
        $users = User::factory()->count(3)->create();
        $this->createSession($users[0], now()->startOfDay()->subHour(), now()->setHour(10)->startOfHour());
        $this->createSession($users[1], now()->setHour(11)->startOfHour(), now()->setHour(15)->startOfHour());
        $this->createSession($users[2], now()->setHour(16)->startOfHour(), Carbon::tomorrow()->setHour(1)
            ->startOfHour());
        $result = $this->sessions->getMaxOnline(now());
        $this->assertCount(3, $result);
        $this->assertEquals(now()->startOfDay(), $result[0]->getFrom());
        $this->assertEquals(now()->setHour(10)->startOfHour(), $result[0]->getTo());
        $this->assertEquals(1, $result[0]->getTotal());

        $this->assertEquals(now()->setHour(11)->startOfHour(), $result[1]->getFrom());
        $this->assertEquals(now()->setHour(15)->startOfHour(), $result[1]->getTo());
        $this->assertEquals(1, $result[1]->getTotal());

        $this->assertEquals(now()->setHour(16)->startOfHour(), $result[2]->getFrom());
        $this->assertEquals(now()->endOfDay()->microsecond(0), $result[2]->getTo());
        $this->assertEquals(1, $result[2]->getTotal());
    }

    /**
     * @throws Exception
     */
    public function testDoubleOverlap()
    {
        $users = User::factory()->count(3)->create();
        $this->createSession($users[0], now()->setHour(1), now()->setHour(11)->startOfHour());
        $this->createSession($users[1], now()->setHour(10)->startOfHour(), now()->setHour(16)->startOfHour());
        $this->createSession($users[2], now()->setHour(15)->startOfHour(), now()->setHour(23)->startOfHour());
        $result = $this->sessions->getMaxOnline(now());
        $this->assertCount(2, $result);
        $this->assertEquals(now()->setHour(10)->startOfHour(), $result[0]->getFrom());
        $this->assertEquals(now()->setHour(11)->startOfHour(), $result[0]->getTo());
        $this->assertEquals(2, $result[0]->getTotal());

        $this->assertEquals(now()->setHour(15)->startOfHour(), $result[1]->getFrom());
        $this->assertEquals(now()->setHour(16)->startOfHour(), $result[1]->getTo());
        $this->assertEquals(2, $result[1]->getTotal());
    }

    /**
     * @throws Exception
     */
    public function test3OverlapAndInfinite()
    {
        $users = User::factory()->count(3)->create();
        $this->createSession($users[0], now()->setHour(1), now()->setHour(3)->startOfHour());
        $this->createSession($users[1], now()->subDay()->setHour(23)->startOfHour());
        $this->createSession($users[2], now()->setHour(2)->startOfHour(), now()->setHour(20)->startOfHour());
        $result = $this->sessions->getMaxOnline(now());
        $this->assertCount(1, $result);
        $this->assertEquals(now()->setHour(2)->startOfHour(), $result[0]->getFrom());
        $this->assertEquals(now()->setHour(3)->startOfHour(), $result[0]->getTo());
        $this->assertEquals(3, $result[0]->getTotal());
    }

    /**
     * @throws Exception
     */
    public function test2OverlapAnd2Ranges()
    {
        $users = User::factory()->count(3)->create();
        $this->createSession($users[0], now()->subDay()->setHour(23));
        $this->createSession($users[1], now()->setHour(1)->startOfHour(), now()->setHour(10)->startOfHour());
        $this->createSession($users[1], now()->setHour(11)->startOfHour(), now()->setHour(23)->startOfHour());
        $this->createSession($users[2], now()->setHour(5)->startOfHour(), now()->setHour(15)->startOfHour());
        $result = $this->sessions->getMaxOnline(now());
        $this->assertCount(2, $result);
        $this->assertEquals(now()->setHour(5)->startOfHour(), $result[0]->getFrom());
        $this->assertEquals(now()->setHour(10)->startOfHour(), $result[0]->getTo());
        $this->assertEquals(3, $result[0]->getTotal());

        $this->assertEquals(now()->setHour(11)->startOfHour(), $result[1]->getFrom());
        $this->assertEquals(now()->setHour(15)->startOfHour(), $result[1]->getTo());
        $this->assertEquals(3, $result[0]->getTotal());
    }

    /**
     * @throws Exception
     */
    public function test3OverlapsAllInfinite()
    {
        $users = User::factory()->count(3)->create();
        $this->createSession($users[0], now()->setHour(12));
        $this->createSession($users[1], now()->subDay()->setHour(15)->startOfHour());
        $this->createSession($users[2], now()->setHour(20)->startOfHour());
        $result = $this->sessions->getMaxOnline(now());
        $this->assertCount(1, $result);
        $this->assertEquals(now()->setHour(20)->startOfHour(), $result[0]->getFrom());
        $this->assertEquals(now()->endOfDay()->microsecond(0), $result[0]->getTo());
        $this->assertEquals(3, $result[0]->getTotal());
    }

    protected function createSession(User $user, Carbon $loginTime, Carbon $logoutTime = null)
    {
        $session = new Session();
        $session->user_id = $user->id;
        $session->login_time = $loginTime->toDateTimeString();
        if ($logoutTime) {
            $session->logout_time = $logoutTime->toDateTimeString();
        }
        $session->save();
    }

}
