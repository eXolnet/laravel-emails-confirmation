<?php

namespace Illuminate\Tests\Auth;

use Exolnet\Auth\Emails\EmailBroker;
use Exolnet\Auth\Emails\TokenRepositoryInterface;
use Exolnet\Contracts\Auth\CanConfirmEmail;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Carbon;
use Mockery as m;
use Illuminate\Support\Arr;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class AuthEmailBrokerTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testIfUserIsNotFoundErrorRedirectIsReturnedWhenSending()
    {
        $mocks = $this->getMocks();
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods(['getUser', 'makeErrorRedirect'])->setConstructorArgs(array_values($mocks))->getMock();

        $this->assertEquals(EmailBroker::INVALID_USER, $broker->sendConfirmationLink(null, 'email'));
    }

    public function testIfUserIsNotFoundErrorRedirectIsReturnedWhenResending()
    {
        $mocks = $this->getMocks();
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods(['getUser', 'makeErrorRedirect'])->setConstructorArgs(array_values($mocks))->getMock();
        $broker->expects($this->once())->method('getUser')->will($this->returnValue(null));

        $this->assertEquals(EmailBroker::INVALID_USER, $broker->resendConfirmationLink(['credentials']));
    }

    public function testIfUserIsNotFoundErrorRedirectIsReturnedWhenUserIsAlreadyConfirmed()
    {
        $mocks = $this->getMocks();
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods(['getUser', 'makeErrorRedirect'])->setConstructorArgs(array_values($mocks))->getMock();
        $user = m::mock(CanConfirmEmail::class);
        $broker->expects($this->once())->method('getUser')->will($this->returnValue($user));
        $user->shouldReceive('getConfirmedAtForEmailConfirmation')->once()->andReturn(Carbon::now());

        $this->assertEquals(EmailBroker::INVALID_USER, $broker->resendConfirmationLink(['credentials']));
    }

    public function testGetUserThrowsExceptionIfUserDoesntImplementCanConfirmEmail()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('User must implement CanConfirmEmail interface.');

        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn('bar');

        $broker->getUser(['foo']);
    }

    public function testUserIsRetrievedByCredentials()
    {
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user = m::mock(CanConfirmEmail::class));

        $this->assertEquals($user, $broker->getUser(['foo']));
    }

    public function testBrokerCreatesTokenAndRedirectsWithoutErrorWhenSending()
    {
        $mocks = $this->getMocks();
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods()->setConstructorArgs(array_values($mocks))->getMock();
        $user = m::mock(CanConfirmEmail::class);
        $mocks['tokens']->shouldReceive('create')->once()->with($user, 'email')->andReturn('token');
        $user->shouldReceive('sendEmailConfirmationNotification')->with('email', 'token');

        $this->assertEquals(EmailBroker::CONFIRM_LINK_SENT, $broker->sendConfirmationLink($user, 'email'));
    }

    public function testBrokerCreatesTokenAndRedirectsWithoutErrorWhenResending()
    {
        $mocks = $this->getMocks();
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods()->setConstructorArgs(array_values($mocks))->getMock();
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['foo'])->andReturn($user = m::mock(CanConfirmEmail::class));
        $mocks['tokens']->shouldReceive('create')->once()->with($user, 'email')->andReturn('token');
        $user->shouldReceive('getConfirmedAtForEmailConfirmation')->once()->andReturn(null);
        $user->shouldReceive('getEmailForEmailConfirmation')->once()->andReturn('email');
        $user->shouldReceive('sendEmailConfirmationNotification')->with('email', 'token');

        $this->assertEquals(EmailBroker::CONFIRM_LINK_SENT, $broker->resendConfirmationLink(['foo']));
    }

    public function testRedirectIsReturnedByConfirmWhenUserCredentialsInvalid()
    {
        $broker = $this->getBroker($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['creds'])->andReturn(null);

        $this->assertEquals(EmailBroker::INVALID_USER, $broker->confirm(['creds'], function () {
        }));
    }

    public function testRedirectReturnedByRemindWhenRecordDoesntExistInTable()
    {
        $creds = ['token' => 'token'];
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods()->setConstructorArgs(array_values($mocks = $this->getMocks()))->getMock();
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(Arr::except($creds, ['token']))->andReturn($user = m::mock(CanConfirmEmail::class));
        $mocks['tokens']->shouldReceive('exists')->with($user, 'token')->andReturn(false);

        $this->assertEquals(EmailBroker::INVALID_TOKEN, $broker->confirm($creds, function () {
        }));
    }

    public function testIfEmailConfirmedRedirectIsReturned()
    {
        $creds = ['token' => 'token'];
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods()->setConstructorArgs(array_values($mocks = $this->getMocks()))->getMock();
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(Arr::except($creds, ['token']))->andReturn($user = m::mock(CanConfirmEmail::class));
        $mocks['tokens']->shouldReceive('exists')->with($user, 'token')->andReturn(true);
        $mocks['tokens']->shouldReceive('find')->once()->with($user)->andReturn(['email' => 'email']);
        $mocks['tokens']->shouldReceive('delete')->once()->with($user);

        $this->assertEquals(EmailBroker::EMAIL_CONFIRMED, $broker->confirm($creds, function () {
        }));
    }

    public function testConfirmRemovesRecordOnReminderTableAndCallsCallback()
    {
        unset($_SERVER['__email.confirm.test']);
        $broker = $this->getMockBuilder(EmailBroker::class)->setMethods(['validateConfirm'])->setConstructorArgs(array_values($mocks = $this->getMocks()))->getMock();
        $broker->expects($this->once())->method('validateConfirm')->will($this->returnValue($user = m::mock(CanConfirmEmail::class)));
        $mocks['tokens']->shouldReceive('find')->once()->with($user)->andReturn(['email' => 'email']);
        $mocks['tokens']->shouldReceive('delete')->once()->with($user);
        $callback = function ($user, $email) {
            $_SERVER['__email.confirm.test'] = compact('user', 'email');

            return 'foo';
        };

        $this->assertEquals(EmailBroker::EMAIL_CONFIRMED, $broker->confirm(['email' => 'email', 'token' => 'token'], $callback));
        $this->assertEquals(['user' => $user, 'email' => 'email'], $_SERVER['__email.confirm.test']);
    }

    protected function getBroker($mocks)
    {
        return new EmailBroker($mocks['tokens'], $mocks['users']);
    }

    protected function getMocks()
    {
        $mocks = [
            'tokens' => m::mock(TokenRepositoryInterface::class),
            'users'  => m::mock(UserProvider::class),
        ];

        return $mocks;
    }
}
