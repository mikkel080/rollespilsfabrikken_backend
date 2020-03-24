<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Forum;
use \Tests\Helpers\TestHelper;

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
            ->actingAs($user, 'api')->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
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
            ->actingAs($user, 'api')->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'forum_id' => $forum['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'title',
                        'body',
                        'forum_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
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
            ->actingAs($user, 'api')->json('POST', '/api/forum/' . $forum['id'] . '/post', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'forum_id' => $forum['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'title',
                        'body',
                        'forum_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
                    ]
                ]
            );
    }

    public function testGetAllPosts() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $this
            ->actingAs($user, 'api')->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->assertJson([
            'message' => 'success',
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'posts' => [
                        'data' => [
                            [
                                'id',
                                'forum_id',
                                'user_id',
                                'title',
                                'body',
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

    public function testUpdatePostAsOwner() {
        $data = [
            'title' => 'Updated post title for unit',
            'body'  => '# Updated post body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $forum->posts()->create(['title' => 'hello', 'body' => 'hello again', 'user_id' => $user['id']]);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $post = $this
            ->actingAs($user, 'api')->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['posts']['data'][1];

        $this
            ->actingAs($user, 'api')->json('PATCH', '/api/forum/' . $forum['id'] . '/post/' . $post['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'post' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'forum_id' => $forum['id'],
                    'user_id' => $user['id'],
                ]
            ])
            ->assertJsonStructure(
                [
                    'message',
                    'post' => [
                        'title',
                        'body',
                        'forum_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                        'id'
                    ]
                ]
            );
    }

    public function testDeletePostAsOwner() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $forum
            ->posts()
            ->create(['title' => 'hello', 'body' => 'hello again', 'user_id' => $user['id']]);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $post = $this
            ->actingAs($user, 'api')->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['posts']['data'][1];

        $this
            ->actingAs($user, 'api')->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testDeletePostAsModerator() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();

        $forum
            ->posts()
            ->create(['title' => 'hello', 'body' => 'hello again', 'user_id' => $user['id']]);

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 5);

        $post = $this
            ->actingAs($user, 'api')->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['posts']['data'][1];

        $this
            ->actingAs($user, 'api')->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
