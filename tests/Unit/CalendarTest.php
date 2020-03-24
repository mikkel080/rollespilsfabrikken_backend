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
            ->actingAs($user, 'api')->json('POST', '/api/calendar/', $data)
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
            ->actingAs($user, 'api')->json('POST', '/api/calendar/', $data)
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
            ->actingAs($user, 'api')->json('POST', '/api/calendar/', $data)
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
                        'obj_id',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
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
            ->actingAs($user, 'api')->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendars' => [
                        'data' => [
                            [
                                'id',
                                'obj_id',
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

    public function testGetAllCalendarsAsUser() {
        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 2);

        $this
            ->actingAs($user, 'api')->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendars' => [
                        'data' => [
                            [
                                'id',
                                'obj_id',
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

    public function testUpdateCalendarAsUser() {
        $data = [
            'title' => 'Updated calendar title for unit',
            'description'  => 'Updated calendar description for unit'
        ];

        $calendar = factory(Calendar::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $calendar['obj_id'], 6);

        $this
            ->actingAs($user, 'api')->json('PATCH', '/api/calendar/' . $calendar['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'calendar' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $calendar['id'],
                    'obj_id' => $calendar['obj_id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendar' => [
                        'id',
                        'obj_id',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
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
            ->actingAs($user, 'api')->json('PATCH', '/api/calendar/' . $calendar['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'calendar' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $calendar['id'],
                    'obj_id' => $calendar['obj_id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'calendar' => [
                        'id',
                        'obj_id',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
                    ]
                ]
            );
    }

    public function testDeleteCalendar() {
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $calendar = $this
            ->actingAs($user, 'api')->json('GET', '/api/calendar')
            ->assertStatus(200)
            ->decodeResponseJson()['calendars']['data'][1];

        $this
            ->actingAs($user, 'api')->json('DELETE', '/api/calendar/' . $calendar['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
