<?php

namespace Tests\Unit;

use App\Models\Calendar;
use App\Models\User;
use Tests\TestCase;
use Tests\Helpers\TestHelper;

class CalendarTest extends TestCase
{
    public function testCreateCalendarWithMiddleware()
    {
        $data = [
            'title' => 'Calendar title for unit',
            'description'  => 'Calendar description for unit'
        ];

        $this
            ->json('POST', '/api/calendar/', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function  testCreateCalendarWithoutRoles() {
        $data = [
            'title' => 'Calendar title for unit',
            'description'  => 'Calendar description for unit'
        ];

        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'sanctum')
            ->json('POST', '/api/calendar/', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreateCalendarWithRoles() {
        $data = [
            'title' => 'Calendar title for unit',
            'description'  => 'Calendar description for unit'
        ];

        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'sanctum')
            ->json('POST', '/api/calendar/', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreateCalendarAsAdmin() {
        $data = [
            'title' => 'Calendar title for unit',
            'description'  => 'Calendar description for unit'
        ];

        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'sanctum')
            ->json('POST', '/api/calendar/', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'calendar' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendar' => [
                        'id',
                        'title',
                        'description'
                    ]
                ]
            );
    }

    public function testGetAllCalendarsAsAdmin() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'sanctum')
            ->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'data' => [
                        'calendars' => [
                            [
                                'id',
                                'title',
                                'description'
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

    public function testGetAllCalendarsAsUser() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $this
            ->actingAs($user, 'sanctum')
            ->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'data' => [
                        'calendars' => [
                            [
                                'id',
                                'title',
                                'description'
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

    public function testUpdateCalendarAsUser() {
        $data = [
            'title' => 'Updated calendar title for unit',
            'description'  => 'Updated calendar description for unit'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 6);

        $this
            ->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/calendar/' . $calendar['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'calendar' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $calendar['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendar' => [
                        'id',
                        'title',
                        'description'
                    ]
                ]
            );
    }

    public function testUpdateCalendarAsAdmin() {
        $data = [
            'title' => 'Updated calendar title for unit',
            'description'  => 'Updated calendar description for unit'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/calendar/' . $calendar['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'calendar' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $calendar['id']
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendar' => [
                        'id',
                        'title',
                        'description'
                    ]
                ]
            );
    }

    public function testDeleteCalendar() {
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $calendar = $this
            ->actingAs($user, 'sanctum')->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['calendars'][1];

        $this
            ->actingAs($user, 'sanctum')->json('DELETE', '/api/calendar/' . $calendar['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
