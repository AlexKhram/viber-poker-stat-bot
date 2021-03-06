<?php

namespace ViberPokerBot\Lib\Storage;

require_once 'Storage.php';

class UserStorage extends Storage
{

    //User fields
    //{
    //"id": data,
    //"name": data,
    //"avatar": data,
    //"role": data,
    //"isSubscribed": data
    //}

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected function getFilePath(): string
    {
        return static::STORAGE_PATH . 'users.json';
    }

    protected function storeDataToFile(array $users): void
    {
        file_put_contents($this->getFilePath(), json_encode($users, JSON_THROW_ON_ERROR));
    }

    public function getUser($id)
    {
        foreach ($this->getAll() as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }

        return null;
    }

    public function getSubscribedUsers(): array
    {
        $users = [];
        foreach ($this->getAll() as $user) {
            if ($user->isSubscribed) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function getUserIds()
    {
        $ids = [];
        foreach ($this->getAll() as $user) {
            $ids[] = $user->id;
        }

        return $ids;
    }

    public function getSubscribedUserIds()
    {
        $ids = [];
        foreach ($this->getAll() as $user) {
            if ($user->isSubscribed) {
                $ids[] = $user->id;
            }
        }

        return $ids;
    }

    public function updateUser(object $newUser): bool
    {
        if (empty($newUser->id)) {
            return false;
        }
        $users = $this->getAll();
        foreach ($users as $key => $user) {
            if ($newUser->id === $user->id) {
                //user already exist and isSubscribed is equal
                if (($user->isSubscribed ?? null) === $newUser->isSubscribed || ($user->isSubscribed ?? null)) {
                    return false;
                }
                $newUser->role = $user->role;
                unset($users[$key]);
                break;
            }
        }
        $users[] = $newUser;
        $this->storeDataToFile($users);

        return true;
    }


    public function updateUsers(array $newUsers, bool $isSubscribed = true): bool
    {
        $users = $this->getAll();
        foreach ($users as $user) {
            foreach ($newUsers as $key => $newUser) {
                $newUser->isSubscribed = $isSubscribed;
                if ($newUser->id === $user->id) {
                    unset($users[$key]);
                    if (($user->isSubscribed ?? null) === $newUser->isSubscribed) {
                        unset($newUsers[$key]);
                    } else {
                        unset($users[$key]);
                    }
                }
            }
            if (!$newUsers) {
                break;
            }
        }
        if (!$newUsers) {
            return false;
        }

        $users = array_merge($users, $newUsers);
        $this->storeDataToFile($users);

        return true;
    }

    public function isUserAdmin(string $id = null): bool
    {
        $users = $this->getAll();
        foreach ($users as $user) {
            if ($user->id === $id) {
                return $user->role === static::ROLE_ADMIN;
            }
        }

        return false;
    }

    public function setSubscribe(string $id = null, bool $isSubscribed = true): bool
    {
        $userExist = false;
        $users = $this->getAll();
        foreach ($users as $key => $user) {
            if ($id === $user->id) {
                $userExist = true;
                $users[$key]->isSubscribed = $isSubscribed;
                break;
            }
        }
        if (!$userExist) {
            return false;
        }

        $this->storeDataToFile($users);

        return true;
    }

    public function setRole(string $id = null, string $role = self::ROLE_USER): bool
    {
        $userExist = false;
        $users = $this->getAll();
        foreach ($users as $key => $user) {
            if ($id === $user->id) {
                $userExist = true;
                $users[$key]->role = $role;
                break;
            }
        }
        if (!$userExist) {
            return false;
        }

        $this->storeDataToFile($users);

        return true;
    }
}