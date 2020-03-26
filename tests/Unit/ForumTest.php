<?php

namespace Tests\Unit;

use App\Models\Forum;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

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
                        'title',
                        'description'
                    ]
                ]
            );
    }

    public function testGetForumAsAdmin() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')->json('GET', '/api/forum/' . $forum['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
                        'id',
                        'title',
                        'description',
                        'posts'
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
                    'data' => [
                        'forums' => [
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
                    'data' => [
                        'forums' => [
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
                    'id' => $forum['id']
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
                        'id',
                        'title',
                        'description',
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
                    'id' => $forum['id']
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'forum' => [
                        'id',
                        'title',
                        'description'
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
            ->decodeResponseJson()['data']['forums'][1];

        $this
            ->actingAs($user, 'api')->json('DELETE', '/api/forum/' . $forum['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
