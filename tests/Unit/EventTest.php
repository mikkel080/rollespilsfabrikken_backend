<?php

namespace Tests\Unit;

use App\Models\Calendar;
use App\Models\User;
use App\Models\Event;
use Tests\TestCase;
use Tests\Helpers\TestHelper;

class EventTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreateEventWithMiddleware()
    {
        $data = [
            'title' => 'Event title for unit',
            'description'  => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '2020-03-24 12:00:00',
            'end' => '2020-03-24 12:30:00'
        ];

        $calendar = factory(Calendar::class)->create();

        $this
            ->json('POST', '/api/calendar/' . $calendar['id'] . '/event', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function  testCreateEventWithoutRoles() {
        $data = [
            'title' => 'Event title for unit',
            'description'  => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '2020-03-24 12:00:00',
            'end' => '2020-03-24 12:30:00'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/calendar/' . $calendar['id'] . '/event', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreateEventWithRoles() {
        $data = [
            'title' => 'Event title for unit',
            'description'  => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '2020-03-24 12:00:00',
            'end' => '2020-03-24 12:30:00'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 4);

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/calendar/' . $calendar['id'] . '/event', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'event' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'calendar_id' => $calendar['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'title',
                        'description',
                        'calendar_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
                    ]
                ]
            );
    }

    public function  testCreateEventAsAdmin() {
        $data = [
            'title' => 'Event title for unit',
            'description'  => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '2020-03-24 12:00:00',
            'end' => '2020-03-24 12:30:00'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/calendar/' . $calendar['id'] . '/event', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'event' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'calendar_id' => $calendar['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'title',
                        'description',
                        'calendar_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
                    ]
                ]
            );
    }

    public function testGetAllEvents() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'events' => [
                        'data' => [
                            [
                                'id',
                                'calendar_id',
                                'user_id',
                                'title',
                                'description',
                                'created_at',
                                'updated_at',
                            ]
                        ],
                        'current_page',
                        'first_page_url',
                        'from',
                        'last_page',
                        'last_page_url',
                        'next_page_url',
                        'path',
                        'per_page',
                        'prev_page_url',
                        'to',
                        'total',
                    ]
                ]
            );
    }

    public function testUpdateEventAsOwner() {
        $data = [
            'title' => 'Updated event title for unit',
            'description'  => '# Update Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '2020-03-24 12:00:00',
            'end' => '2020-03-24 12:30:00'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $calendar
            ->events()
            ->create([
                'title' => 'hello',
                'description' => 'hello again',
                'user_id' => $user['id'],
                'start' => '2020-03-24 12:00:00',
                'end' => '2020-03-24 12:30:00'
            ]);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['events']['data'][1];

        $this
            ->actingAs($user, 'api')
            ->json('PATCH', '/api/calendar/' . $calendar['id'] . '/event/' . $event['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'event' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'calendar_id' => $calendar['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'title',
                        'description',
                        'calendar_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
                    ]
                ]
            );
    }

    public function testDeleteEventAsOwner() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $calendar
            ->events()
            ->create([
                'title' => 'hello',
                'description' => 'hello again',
                'user_id' => $user['id'],
                'start' => '2020-03-24 12:00:00',
                'end' => '2020-03-24 12:30:00'
            ]);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['events']['data'][1];

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/calendar/' . $calendar['id'] . '/event/' . $event['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testDeleteEventAsModerator() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $calendar
            ->events()
            ->create([
                'title' => 'hello',
                'description' => 'hello again',
                'user_id' => 1,
                'start' => '2020-03-24 12:00:00',
                'end' => '2020-03-24 12:30:00'
            ]);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 5);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['events']['data'][1];

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/calendar/' . $calendar['id'] . '/event/' . $event['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
