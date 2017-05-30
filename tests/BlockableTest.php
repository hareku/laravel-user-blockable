<?php

namespace Hareku\LaravelBlockable\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BlockableTest extends TestCase
{
    use DatabaseTransactions;

    // /**
    //  * Create users.
    //  *
    //  * @param  int  $amount
    //  * @return Collection
    //  */
    // protected function createUsers(int $amount = 3)
    // {
    //     return factory(User::class, $amount)->create();
    // }
    //
    // /**
    //  * Create a user.
    //  *
    //  * @param  array  $override
    //  * @return User
    //  */
    // protected function createUser(array $override = []): User
    // {
    //     return factory(User::class)->create($override);
    // }

    /** @test */
    public function it_blocks_user()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();

        $blocker->block($blockedByUser->id);

        $this->assertDatabaseHas(config('blockable.table_name'), [
            'blocker_id' => $blocker->id,
            'blocked_by_id' => $blockedByUser->id,
        ]);
    }

    /** @test */
    public function it_add_blocker()
    {
        $blockedByUser = $this->createUser();
        $blocker = $this->createUser();

        $blockedByUser->addBlockers($blocker->id);

        $this->assertDatabaseHas(config('blockable.table_name'), [
            'blocker_id' => $blocker->id,
            'blocked_by_id' => $blockedByUser->id,
        ]);
    }

    /** @test */
    public function it_add_many_blockerUsers()
    {
        $blockedByUser = $this->createUser();
        $blockers = $this->createUsers(3);

        $blockedByUser->addBlockers($blockers->pluck('id')->toArray());

        foreach ($blockers as $blocker) {
            $this->assertDatabaseHas(config('blockable.table_name'), [
                'blocker_id' => $blocker->id,
                'blocked_by_id' => $blockedByUser->id,
            ]);
        }
    }

    /** @test */
    public function it_blocks_many_users()
    {
        $blocker = $this->createUser();
        $blockedByUsers = $this->createUsers(3);

        $blocker->block($blockedByUsers->pluck('id')->toArray());

        foreach ($blockedByUsers as $blockedByUser) {
            $this->assertDatabaseHas(config('blockable.table_name'), [
                'blocker_id' => $blocker->id,
                'blocked_by_id' => $blockedByUser->id,
            ]);
        }
    }

    /** @test */
    public function it_blocks_same_user()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();

        $blocker->block($blockedByUser->id);
        $blocker->block($blockedByUser->id);

        $this->assertSame(1, $blocker->blockingUsers()->count());
    }

    /** @test */
    public function it_gets_blockers_and_blocking_users()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();

        $blocker->block($blockedByUser->id);

        $this->assertSame(1, $blocker->blockingUsers()->count());
        $this->assertSame(1, $blocker->blockingRelationships()->count());
        $this->assertSame(0, $blocker->blockerUsers()->count());
        $this->assertSame(0, $blocker->blockerRelationships()->count());
    }

    /** @test */
    public function it_unblocks_user()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();

        $blocker->block($blockedByUser->id);
        $blocker->unblock($blockedByUser->id);

        $this->assertDatabaseMissing(config('blockable.table_name'), [
            'blocker_id' => $blocker->id,
            'blocked_by_id' => $blockedByUser->id,
        ]);
    }

    /** @test */
    public function it_unblocks_many_users()
    {
        $blocker = $this->createUser();
        $blockedByUsers = $this->createUsers(3);

        $blockedByUserIds = $blockedByUsers->pluck('id')->toArray();
        $blocker->block($blockedByUserIds);
        $blocker->unblock($blockedByUserIds);

        foreach ($blockedByUsers as $blockedByUser) {
            $this->assertDatabaseMissing(config('blockable.table_name'), [
                'blocker_id' => $blocker->id,
                'blocked_by_id' => $blockedByUser->id,
            ]);
        }
    }

    /** @test */
    public function it_checks_if_user_is_blocking()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();
        $notBlockedBy = $this->createUser();

        $blocker->block($blockedByUser->id);

        $this->assertTrue($blocker->isBlocking($blockedByUser->id));
        $this->assertFalse($blocker->isBlocking($notBlockedBy->id));
    }

    /** @test */
    public function it_checks_if_user_is_blocking_for_array()
    {
        $blocker = $this->createUser();
        $blockedByUsers = $this->createUsers(3);
        $notBlockedBy = $this->createUser();
        $blockedByUserIds = $blockedByUsers->pluck('id')->toArray();

        $blocker->block($blockedByUserIds);

        $this->assertTrue($blocker->isBlocking($blockedByUserIds));

        $blockedByUserIds[] = $notBlockedBy->id;
        $this->assertFalse($blocker->isBlocking($blockedByUserIds));
    }

    /** @test */
    public function it_checks_if_user_is_being_blocked()
    {
        $blockedByUser = $this->createUser();
        $blocker = $this->createUser();
        $notBlocker = $this->createUser();

        $blocker->block($blockedByUser->id);

        $this->assertTrue($blockedByUser->isBlockedBy($blocker->id));
        $this->assertFalse($blockedByUser->isBlockedBy($notBlocker->id));
    }

    /** @test */
    public function it_checks_if_user_is_being_blocked_for_array()
    {
        $blockedByUser = $this->createUser();
        $blockers = $this->createUsers(3);
        $notBlocker = $this->createUser();
        $blockerIds = $blockers->pluck('id')->toArray();

        foreach ($blockers as $blocker) {
            $blocker->block($blockedByUser->id);
        }

        $this->assertTrue($blockedByUser->isBlockedBy($blockerIds));

        $blockerIds[] = $notBlocker->id;
        $this->assertFalse($blockedByUser->isBlockedBy($blockerIds));
    }

    /** @test */
    public function it_checks_if_user_is_mutual_block()
    {
        $blocker = $this->createUser();
        $blockedByUser = $this->createUser();

        $blocker->block($blockedByUser->id);
        $blockedByUser->block($blocker->id);

        $this->assertTrue($blocker->isMutualBlock($blockedByUser->id));
        $this->assertTrue($blockedByUser->isMutualBlock($blocker->id));
    }

    /** @test */
    public function it_gets_blocker_ids()
    {
        $blockedByUser = $this->createUser();
        $blockers = $this->createUsers(3);

        foreach ($blockers as $blocker) {
            $blocker->block($blockedByUser->id);
        }

        $this->assertEquals(
            $blockedByUser->blockerIds(),
            $blockers->pluck('id')->toArray()
        );

        $this->assertEquals(
            $blockedByUser->blockerIds(true),
            $blockers->pluck('id')
        );
    }

    /** @test */
    public function it_gets_blocking_ids()
    {
        $blocker = $this->createUser();
        $blockedByUsers = $this->createUsers(3);
        $blockedByUserIds = $blockedByUsers->pluck('id');

        $blocker->block($blockedByUserIds->toArray());

        $this->assertEquals(
            $blocker->blockingIds(),
            $blockedByUserIds->toArray()
        );

        $this->assertEquals(
            $blocker->blockingIds(true),
            $blockedByUserIds
        );
    }

    /** @test */
    public function it_rejects_not_blocker_ids()
    {
        $blockedByUser = $this->createUser();
        $blockers = $this->createUsers(3);
        $blockerIds = $blockers->pluck('id')->toArray();
        $notBlockerIds = $this->createUsers(3)->pluck('id')->toArray();

        foreach ($blockers as $blocker) {
            $blocker->block($blockedByUser->id);
        }

        $this->assertSame(
            $blockerIds,
            $blockedByUser->rejectNotBlocker(array_merge($blockerIds, $notBlockerIds))
        );
    }

    /** @test */
    public function it_rejects_not_blocking_ids()
    {
        $blocker = $this->createUser();
        $blockedByUserIds = $this->createUsers(3)->pluck('id')->toArray();
        $notBlockedByIds = $this->createUsers(3)->pluck('id')->toArray();

        $blocker->block($blockedByUserIds);

        $this->assertSame(
            $blockedByUserIds,
            $blocker->rejectNotBlocking(array_merge($blockedByUserIds, $notBlockedByIds))
        );
    }
}
