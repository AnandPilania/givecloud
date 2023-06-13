<?php

namespace Ds\Eloquent;

use Ds\Domain\Settings\GivecloudExpressConfigRepository;
use Ds\Domain\Shared\Exceptions\PermissionException;

/** @mixin \Ds\Illuminate\Database\Eloquent\Model */
trait Permissions
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootPermissions()
    {
        // do nothing
    }

    /**
     * Determine whether the current user CAN edit the current eloquent model/record.
     *
     * @param string|array $permissions
     * @param bool $all_must_be_true
     * @return bool
     */
    public function userCan($permissions, $all_must_be_true = false)
    {
        // force $permissions to be an array of permissions to check
        if (! is_array($permissions)) {
            $permissions = [$permissions];
        }

        $fullyQualifiedPermissions = array_map(function ($permission) {
            $klass = explode('\\', strtolower(get_class($this)));

            return count(explode('.', $permission)) !== 2 ? array_pop($klass) . '.' . $permission : $permission;
        }, $permissions);

        if (isGivecloudExpress() && ! $this->permissionsAvailableInGivecloudExpress($fullyQualifiedPermissions, (bool) $all_must_be_true)) {
            return false;
        }

        // if the current user is a super user, always return TRUE
        if (is_super_user()) {
            return true;
        }

        // if the current user is a account admin, always return TRUE
        if (user('is_account_admin')) {
            return true;
        }

        $failed_count = 0;

        // loop over each permission and check
        foreach ($permissions as $permission) {
            // check the permission
            $can_do = $this->_checkPermission($permission);

            // if we only need ONE permission to be true, and this permission IS true, return TRUE!
            if ($can_do && ! $all_must_be_true) {
                return true;
            }

            // otherwise, continue counting tracking counts

            $failed_count += (int) ! $can_do;
        }

        // if there were no failures, return true
        return $failed_count === 0;
    }

    /**
     * Check permission and immediately redirect.
     *
     * @param string|array $permissions
     * @param string $url
     * @return bool
     */
    public function userCanOrRedirect($permissions, $url = '/jpanel')
    {
        // if they don't have permission
        if (! $this->userCan($permissions)) {
            throw new PermissionException($permissions, $url);
        }

        // otherwise, return TRUE
        return true;
    }

    private function permissionsAvailableInGivecloudExpress(array $permissions, bool $allMustBeTrue = false): bool
    {
        $availablePermissions = app(GivecloudExpressConfigRepository::class)->getAvailablePermissions();

        $permissionsGranted = 0;

        foreach ($permissions as $permission) {
            $permissionIsAvailable = in_array($permission, $availablePermissions, true);

            if ($permissionIsAvailable) {
                $permissionsGranted++;
            }
        }

        if ($allMustBeTrue) {
            return count($permissions) === $permissionsGranted;
        }

        return $permissionsGranted > 0;
    }

    /**
     * Check permission on this model.
     * model.level OR just level
     * Example:             _checkPermission('order.view')
     *           or simply: _checkPermission('view') (because you already know the model)
     *
     * @param string $permission
     * @return bool
     */
    private function _checkPermission($permission)
    {
        // break $permission into module and level
        // if there is a model/level defined
        if (count(explode('.', $permission)) == 2) {
            // break out model and level
            [$model, $level] = explode('.', $permission);
        }

        // if no model provided
        else {
            // assume this model
            $klass = explode('\\', strtolower(get_called_class()));
            $model = array_pop($klass);

            // grab level
            $level = $permission;
        }

        // return permission
        if ($level === 'edit' || $level === 'add') {
            return (user()->can($model . '.edit')) // if they can edit any records
                || (
                          // if they can ADD records
                          user()->can($model . '.add')

                          // AND the record is NEW OR they created this record within the last 36hrs and want to modify it
                       && (! $this->exists || ($this->createdBy->id == user('id') && $this->{$this->getCreatedAtColumn()}->diffInHours() < 36))
                );
        }

        // all other level checks
        return user()->can($model . '.' . $level);
    }

    /**
     * Find an eloquent record ONLY when permission exists to retreive it.
     *
     * @param string $identity
     * @param string|array $permissions
     * @param string $url
     * @return self
     */
    public static function findWithPermission($identity, $permissions = 'view', $url = '/jpanel')
    {
        return tap(static::findOrFail($identity))->userCanOrRedirect($permissions, $url);
    }

    /**
     * Create a new model instance IF we have permission to
     *
     * @param string $url
     * @return self
     */
    public static function newWithPermission($url = '/jpanel')
    {
        $permission = substr(static::class, strrpos(static::class, '\\') + 1);
        $permission = strtolower($permission) . '.add';

        user()->canOrRedirect($permission, $url);

        return new static;
    }
}
