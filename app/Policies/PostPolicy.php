<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class PostPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperUser()) {
            return true;
        }
    }

    /**
     * Determine whether the user can pin the post.
     *
     * @param User $user
     * @param Comment $comment
     * @return mixed
     */
    public function pin(User $user, Comment $comment)
    {
        if ($comment['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    } // TODO: Figure out wth is going on here

    /**
     * Determine whether the user can lock the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function lock(User $user, Post $post)
    {
        if ($post['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $comment->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can view newest posts.
     *
     * @param User $user
     * @return mixed
     */
    public function viewNewest(User $user)
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can view any posts.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function viewAny(User $user, Forum $forum)
    {
        return (new PolicyHelper())->checkLevel($user,  $forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can view the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function view(User $user, Post $post)
    {
        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can view the file attached to a post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function downloadFile(User $user, Post $post)
    {
        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 2);
    }

    /**
     * Determine whether the user can add files the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function addFile(User $user, Post $post)
    {
        if ($post['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param User $user
     * @param Forum $forum
     * @return mixed
     */
    public function create(User $user, Forum $forum)
    {
        return (new PolicyHelper())->checkLevel($user,  $forum['obj_id'], 4);
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        if ($post['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        if ($post['user_id'] == $user['id']) return true;

        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can restore the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function restore(User $user, Post $post)
    {
        return (new PolicyHelper())->checkLevel($user,  $post->forum['obj_id'], 5);
    }

    /**
     * Determine whether the user can permanently delete the post.
     *
     * @param User $user
     * @param Post $post
     * @return mixed
     */
    public function forceDelete(User $user, Post $post)
    {
        return (new PolicyHelper())->checkLevel($user,  $post->forum()['obj_id'], 5);
    }
}
