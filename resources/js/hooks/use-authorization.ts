import { usePage } from '@inertiajs/react';
import type { TontinePermission, TontineRole } from '@/types';

export function useAuthorization() {
    const page = usePage();
    const { auth } = page.props;

    const can = (permission: TontinePermission): boolean => {
        return auth.authorization.permissions.includes(permission);
    };

    const canAny = (...permissions: TontinePermission[]): boolean => {
        return permissions.some((permission) => can(permission));
    };

    const canAll = (...permissions: TontinePermission[]): boolean => {
        return permissions.every((permission) => can(permission));
    };

    const hasRole = (role: TontineRole): boolean => {
        return auth.authorization.roles.includes(role);
    };

    const hasAnyRole = (...roles: TontineRole[]): boolean => {
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