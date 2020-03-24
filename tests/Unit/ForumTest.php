<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use App\Models\Forum;
use \Tests\Helpers\TestHelper;

class ForumTest extends TestCase
{
    public function testCreateForumWithMiddleware()
    {
        $data = [
            'title' => 'Forum title for unit',
            'description'  => 'Forum description for unit'
        ];

        $this
            ->json('POST', '/api/forum/', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function  testCreateForumWithoutRoles() {
        $data = [
            'title' => 'Forum title for unit',
            'description'  => 'Forum description for unit'
        ];

        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'api')->json('POST', '/api/forum/', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreateForumWithRoles() {
        $data = [
            'title' => 'Forum title for unit',
            'description'  => 'Forum description for unit'
        ];

        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'api')->json('POST', '/api/forum/', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreateForumAsAdmin() {
        $data = [
            'title' => 'Forum title for unit',
            'description'  => 'Forum description for unit'
        ];

        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')->json('POST', '/api/forum/', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'forum' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
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

    public function testGetAllForumsAsAdmin() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')->json('GET', '/api/forum')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forums' => [
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

    public function testGetAllForumsAsUser() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $this
            ->actingAs($user, 'api')->json('GET', '/api/forum')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forums' => [
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

    public function testUpdateForumAsUser() {
        $data = [
            'title' => 'Updated forum title for unit',
            'description'  => 'Updated forum description for unit'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 6);

        $this
            ->actingAs($user, 'api')->json('PATCH', '/api/forum/' . $forum['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'forum' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $forum['id'],
                    'obj_id' => $forum['obj_id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
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

    public function testUpdateForumAsAdmin() {
        $data = [
            'title' => 'Updated forum title for unit',
            'description'  => 'Updated forum description for unit'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')->json('PATCH', '/api/forum/' . $forum['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'forum' => [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'id' => $forum['id'],
                    'obj_id' => $forum['obj_id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
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

    public function testDeleteForum() {
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $forum = $this
            ->actingAs($user, 'api')->json('GET', '/api/forum')
            ->assertStatus(200)
            ->decodeResponseJson()['forums']['data'][1];

        $this
            ->actingAs($user, 'api')->json('DELETE', '/api/forum/' . $forum['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
