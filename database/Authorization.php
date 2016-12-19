<?php

namespace Sagi\Database;

use Models\Auth;

/**
 * Class Authorization
 * @package Sagi\Database
 */
trait Authorization
{

    /**
     * @return mixed
     */
    public function getAuth()
    {
        return $this->hasOne(Auth::className(), ['user_id', 'id']);
    }

    /**
     *
     */
    public function bootAuthorization()
    {
        $this->eventManager->listen('after_create', function (Model $model){
           $model->createUserAuth($model->getPrimaryValue());
        });

        $this->eventManager->listen('before_delete', function (Model $model){
            $model->deleteAuthRow();
        });
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->is('superadmin');
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is('admin');
    }

    /**
     * @return bool
     */
    public function isUser()
    {
        return $this->is('user');
    }

    /**
     * @return bool
     */
    public function isEditor()
    {
        return $this->is('editor');
    }

    /**
     * @param string $role
     * @return bool
     */
    public function is($role)
    {

        return RoleBag::hasPermission($this->getAuth()->role, $role);
    }

    /**
     * @param $id
     * @param $role
     */
    public function createUserAuth($id, $role = null)
    {
        $auth = Auth::set([
            'role' => $role,
            'user_id' => $id
        ]);

        $auth->save();
    }

    /**
     * @return Model
     */
    protected function deleteAuthRow()
    {
        return Auth::find($this->{$this->getPrimaryKey()})->delete();
    }


}