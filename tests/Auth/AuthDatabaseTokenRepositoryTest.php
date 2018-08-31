<?php

namespace Exolnet\Tests\Auth;

use Mockery as m;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;
use Exolnet\Auth\Emails\DatabaseTokenRepository;

class AuthDatabaseTokenRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
        Carbon::setTestNow(null);
    }

    public function testCreateInsertsNewRecordIntoTable()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('make')->andReturn('hashed-token');
        $repo->getConnection()->shouldReceive('table')->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('delete')->once();
        $query->shouldReceive('insert')->once();
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $results = $repo->create($user, 'email');

        $this->assertInternalType('string', $results);
        $this->assertGreaterThan(1, strlen($results));
    }

    public function testExistReturnsFalseIfNoRowFoundForUser()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('first')->andReturn(null);
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'token'));
    }

    public function testExistReturnsFalseIfRecordIsExpired()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('token', 'hashed-token')->andReturn(true);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subSeconds(300000)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'token'));
    }

    public function testExistReturnsTrueIfValidRecordExists()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('token', 'hashed-token')->andReturn(true);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subMinutes(10)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertTrue($repo->exists($user, 'token'));
    }

    public function testExistReturnsFalseIfInvalidToken()
    {
        $repo = $this->getRepo();
        $repo->getHasher()->shouldReceive('check')->with('wrong-token', 'hashed-token')->andReturn(false);
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $date = Carbon::now()->subMinutes(10)->toDateTimeString();
        $query->shouldReceive('first')->andReturn((object) ['created_at' => $date, 'token' => 'hashed-token']);
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $this->assertFalse($repo->exists($user, 'wrong-token'));
    }

    public function testDeleteMethodDeletesByToken()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('user_id', 'id')->andReturn($query);
        $query->shouldReceive('delete')->once();
        $user = m::mock('Exolnet\Contracts\Auth\CanConfirmEmail');
        $user->shouldReceive('getIdentifierForEmailConfirmation')->andReturn('id');

        $repo->delete($user);
    }

    public function testDeleteExpiredMethodDeletesExpiredTokens()
    {
        $repo = $this->getRepo();
        $repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('stdClass'));
        $query->shouldReceive('where')->once()->with('created_at', '<', m::any())->andReturn($query);
        $query->shouldReceive('delete')->once();

        $repo->deleteExpired();
    }

    protected function getRepo()
    {
        return new DatabaseTokenRepository(
            m::mock('Illuminate\Database\Connection'),
            m::mock('Illuminate\Contracts\Hashing\Hasher'),
            'table',
            'key'
        );
    }
}
