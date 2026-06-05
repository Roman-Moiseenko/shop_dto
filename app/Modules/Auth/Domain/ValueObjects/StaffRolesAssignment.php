<?php

namespace App\Modules\Auth\Domain\ValueObjects;

use App\Modules\Auth\Domain\Exceptions\RoleInvalidArgumentException;

/**
 * Вспомогательный класс для проверки ролей сотрудников:
 * - не пустые
 * - не содержат client
 * - если не содержат staff, то добавить
 */
final class StaffRolesAssignment
{
    /** @var RoleName[] */
    private array $roles;

    /**
     * @param string[] $roleNames
     * @throws RoleInvalidArgumentException
     */
    public function __construct(array $roleNames)
    {
        if (empty($roleNames)) {
            throw new RoleInvalidArgumentException('Роли пользователя не определены');
        }

        // Запрещаем назначать клиентскую роль сотруднику
        if (in_array(RoleName::CLIENT, $roleNames, true)) {
            throw new RoleInvalidArgumentException('Нельзя назначить роль client');
        }

        //Если нет Роли Сотрудника, то добавляем ее
        if (!in_array(RoleName::STAFF, $roleNames)) $roleNames[] = RoleName::STAFF;
        $this->roles = array_map(fn(string $name) => new RoleName($name), $roleNames);
    }

    /**
     * @return RoleName[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Для передачи в UserEntity (используется массив строк)
     * @return string[]
     */
    public function toArrayOfStrings(): array
    {
        return array_map(fn(RoleName $role) => $role->getValue(), $this->roles);
    }
}
