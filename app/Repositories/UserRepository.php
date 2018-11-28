<?php
namespace App\Repositories;
use App\User;

class UserRepository
{
    /** @var User 注入的User model */
    protected $user;
    /**
    * UserRepository constructor.
    * @param User $user
    */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function getIdLargerThan($id)
    {
        return $this->user
            ->where('id', '>', $id)
            ->orderBy('id')
            ->get();
    }
    public function getNameByToken($token)
    {
        return $this->user
            ->where('api_token',$token)
            ->value('name');
    }
};