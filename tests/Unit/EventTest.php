<?php

namespace Tests\Unit;

use App\Models\Calendar;
use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
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
            'start' => '01-01-2022 23:02:01',
            'end' => '01-01-2022 23:30:01'
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
            'start' => '01-01-2022 23:02:01',
            'end' => '01-01-2022 23:30:01'
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
            'start' => '01-01-2022 23:02:01',
            'end' => '01-01-2022 23:30:01'
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
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'id',
                        'title',
                        'description',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ]
                ]
            );
    }

    public function  testCreateEventAsAdmin() {
        $data = [
            'title' => 'Event title for unit',
            'description'  => '# Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '01-01-2022 23:02:01',
            'end' => '01-01-2022 23:30:01'
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
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'title',
                        'description',
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
                    'data' => [
                        'events' => [
                            [
                                'id',
                                'user' => [
                                    'id',
                                    'username',
                                    'avatar_url',
                                    'created_at'
                                ],
                                'title',
                                'description',
                                'created_at',
                                'updated_at',
                            ]
                        ],
                        'links' => [
                            'first_page',
                            'last_page',
                            'prev_page',
                            'next_page'
                        ],
                        'meta' => [
                            'current_page',
                            'first_item',
                            'last_item',
                            'per_page',
                            'total',
                        ]
                    ]
                ]
            );
    }

    public function testUpdateEventAsOwner() {
        $data = [
            'title' => 'Updated event title for unit',
            'description'  => '# Update Event description\n## This\n### Is\nFor\n- Unit\n- Testing',
            'start' => '01-01-2022 23:02:01',
            'end' => '01-01-2022 23:03:01'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $event = (new Event)
            ->fill([
                'title' => 'hello',
                'description' => 'hello again',
                'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
                'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
            ])
            ->user()
            ->associate($user);
        $calendar->events()->save($event);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['events'][1];

        $this
            ->actingAs($user, 'api')
            ->json('PATCH', '/api/calendar/' . $calendar['id'] . '/event/' . $event['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'event' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'event' => [
                        'title',
                        'description',
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

        $event = (new Event)
            ->fill([
                'title' => 'hello',
                'description' => 'hello again',
                'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
                'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
            ])
            ->user()
            ->associate($user);
        $calendar->events()->save($event);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['events'][1];

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

        $event = (new Event)
            ->fill([
                'title' => 'hello',
                'description' => 'hello again',
                'start' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:02:01')->toDateTimeString(),
                'end' => Carbon::createFromFormat('d-m-Y H:i:s', '01-01-2022 23:03:01')->toDateTimeString(),
            ])
            ->user()
            ->associate($user);
        $calendar->events()->save($event);

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 5);

        $event = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/calendar/' . $calendar['id'] . '/event')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['events'][1];

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/calendar/' . $calendar['id'] . '/event/' . $event['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
