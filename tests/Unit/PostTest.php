<?php

namespace Tests\Unit;

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreatePostWithMiddleware()
    {
        $data = [
            'title' => 'Post title for unit',
	        'body'  => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();

        $this
            ->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function  testCreatePostWithoutRoles() {
        $data = [
            'title' => 'Post title for unit',
            'body'  => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function  testCreatePostWithRoles() {
        $data = [
            'title' => 'Post title for unit',
            'body'  => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 4);

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'id',
                        'user_id',
                        'title',
                        'body',
                        'created_at',
                        'updated_at',
                    ]
                ]
            );
    }

    public function  testCreatePostAsAdmin() {
        $data = [
            'title' => 'Post title for unit',
            'body'  => '# Post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'id',
                        'user_id',
                        'title',
                        'body',
                        'created_at',
                        'updated_at',
                    ]
                ]
            );
    }

    public function testGetAllPosts() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->assertJson([
            'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'data' => [
                        'posts' => [
                            [
                                'id',
                                'user' => [
                                    'id',
                                    'username',
                                    'avatar_url',
                                    'created_at'
                                ],
                                'title',
                                'body',
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

    public function testUpdatePostAsOwner() {
        $data = [
            'title' => 'Updated post title for unit',
            'body'  => '# Updated post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $post = (new Post)
            ->fill(['title' => 'hello', 'body' => 'hello again'])
            ->user()
            ->associate($user);
        $forum->posts()->save($post);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $post = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['posts'][1];

        $this
            ->actingAs($user, 'api')
            ->json('PATCH', '/api/forum/' . $forum['id'] . '/post/' . $post['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'id',
                        'user_id',
                        'title',
                        'body',
                        'created_at',
                        'updated_at',
                    ]
                ]
            );
    }

    public function testDeletePostAsOwner() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $post = (new Post)
            ->fill(['title' => 'hello', 'body' => 'hello again'])
            ->user()
            ->associate($user);
        $forum->posts()->save($post);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $post = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['posts'][1];

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testDeletePostAsModerator() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $post = (new Post)
            ->fill(['title' => 'hello', 'body' => 'hello again'])
            ->user()
            ->associate($user);
        $forum->posts()->save($post);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 5);

        $post = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['posts'][1];

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
