<?php

namespace Exolnet\Tests\Auth;

use Exolnet\Auth\Emails\DatabaseTokenRepository;
use Exolnet\Contracts\Auth\CanConfirmEmail;
use Exolnet\Tests\UnitTestCase;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;
use Mockery as m;
use stdClass;

/**
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class AuthDatabaseTokenRepositoryTest extends UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function testCreateInsertsNewRecordIntoTable()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('make')->andReturn('hashed-token');
        $repo->getConnection()->shouldReceive('table')->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('delete')->once();
        $query->shouldReceive('insert')->once();
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $results = $repo->create($user, 'email');

        $this->assertIsString($results);
        $this->assertGreaterThan(1, strlen($results));
    }

    public function testExistReturnsFalseIfNoRowFoundForUser()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('first')->andReturn(null);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'token'));
    }

    public function testExistReturnsFalseIfRecordIsExpired()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('token', 'hashed-token')->andReturn(true);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subSeconds(300000)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'token'));
    }

    public function testExistReturnsTrueIfValidRecordExists()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('token', 'hashed-token')->andReturn(true);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subMinutes(10)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertTrue($repo->exists($user, 'token'));
    }

    public function testExistReturnsFalseIfInvalidToken()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('wrong-token', 'hashed-token')->andReturn(false);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subMinutes(10)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'wrong-token'));
    }

    public function testRecentlyCreatedReturnsFalseIfNoRowFoundForUser()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('first')->once()->andReturn(null);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->recentlyCreatedToken($user));
    }

    public function testRecentlyCreatedReturnsTrueIfRecordIsRecentlyCreated()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subSeconds(59)->toDateTimeString();
        $query->shouldReceive('first')->once()->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertTrue($repo->recentlyCreatedToken($user));
    }

    public function testRecentlyCreatedReturnsFalseIfValidRecordExists()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subSeconds(61)->toDateTimeString();
        $query->shouldReceive('first')->once()->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->recentlyCreatedToken($user));
    }

    public function testDeleteMethodDeletesByToken()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('delete')->once();
        $user = m::mock(CanConfirmEmail::class);
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $repo->delete($user);
    }

    public function testDeleteExpiredMethodDeletesExpiredTokens()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock(stdClass::class));
        $query->shouldReceive('where')->once()->with('created_at', '<', m::any())->andReturn($query);
        $query->shouldReceive('delete')->once();

        $repo->deleteExpired();
    }

    protected function getRepo()
    {
        return new DatabaseTokenRepository(
            m::mock(Connection::class),
            m::mock(Hasher::class),
            'table',
            'key'
        );
    }
}
