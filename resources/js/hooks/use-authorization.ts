import { usePage } from '@inertiajs/react';
import type { GroupPermission, GroupRole } from '@/types';

export function useAuthorization() {
    const page = usePage();
    const { auth } = page.props;

    const can = (permission: GroupPermission): boolean => {
        return auth.authorization.permissions.includes(permission);
    };

    const canAny = (...permissions: GroupPermission[]): boolean => {
        return permissions.some((permission) => can(permission));
    };

    const canAll = (...permissions: GroupPermission[]): boolean => {
        return permissions.every((permission) => can(permission));
    };

    const hasRole = (role: GroupRole): boolean => {
        return auth.authorization.roles.includes(role);
    };

    const hasAnyRole = (...roles: GroupRole[]): boolean => {
        return roles.some((role) => hasRole(role));
    };

    return {
        roles: auth.authorization.roles,
        permissions: auth.authorization.permissions,
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
    };
}
