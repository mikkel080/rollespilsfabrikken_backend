<?php

namespace Tests\Unit;

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Tests\TestCase;

class CommentTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreateCommentWithMiddleware()
    {
        $data = [
            'body'  => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);

        $this
            ->json('POST', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment', $data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function  testCreateCommentWithoutRoles() {
        $data = [
            'body'  => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $user = factory(User::class)->create();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment', $data)
            ->assertStatus(403)
            ->assertJson(['message' => 'You do not have the rights to perform this action']);
    }

    public function testCreateCommentWithRoles() {
        $data = [
            'body'  => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 3);

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'comment' => [
                    'body' => $data['body'],
                    'user_id' => $user['id'],
                ]
            ]);
    }

    public function  testCreateSubCommentWithRoles() {
        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $comment = $post->comments()->first();

        $data = [
            'body'  => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing',
            'parent_id' => $comment['id'],
        ];

        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 3);

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'comment' => [
                    'body' => $data['body'],
                    'parent_id' => $data['parent_id'],
                    'user_id' => $user['id'],
                ]
            ]);
    }

    public function  testCreateCommentAsAdmin() {
        $data = [
            'body'  => '# Comment body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $user = factory(User::class)->create();

        $user['super_user'] = 1;
        $user->save();

        $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment', $data)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'success',
                'comment' => [
                    'body' => $data['body'],
                    'user_id' => $user['id'],
                ]
            ]);
    }

    public function testGetAllComments() {
        $forum = factory(Forum::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $user = factory(User::class)->create();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment')
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testUpdateCommentAsOwner() {
        $data = [
            'body'  => '# Updated comment body\n## This\n### Is\nFor\n- Unit\n- Testing'
        ];

        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);

        $comment = $post->comments()->first();
        $comment->user_id = $user['id'];
        $comment->save();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $post = $this
            ->actingAs($user, 'api')
            ->json('GET', '/api/forum/' . $forum['id'] . '/post')
            ->assertStatus(200)
            ->decodeResponseJson()['data']['posts'][1];

        $this
            ->actingAs($user, 'api')
            ->json('PATCH', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment/' . $comment['id'], $data)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
                'comment' => [
                    'body' => $data['body'],
                    'parent_id' => $comment['parent_id'],
                    'user_id' => $user['id'],
                ]
            ]);
    }

    public function testDeleteCommentAsOwner() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $comment = $post->comments()->first();
        $comment->user_id = $user['id'];
        $comment->save();

        //(new TestHelper())->giveUserPermission($user, $forum['obj_id'], 2);

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment/' . $comment['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function testDeleteCommentAsModerator() {
        $forum = factory(Forum::class)->create();
        $user = factory(User::class)->create();
        $post  = factory(Post::class)->create(['forum_id' => $forum['id']]);
        $comment = $post->comments()->first();

        (new TestHelper())->giveUserPermission($user, $forum['obj_id'], 5);

        $this
            ->actingAs($user, 'api')
            ->json('DELETE', '/api/forum/' . $forum['id'] . '/post/' . $post['id'] . '/comment/' . $comment['id'])
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }
}
