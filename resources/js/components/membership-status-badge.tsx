import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { MembershipStatus } from '@/types';


type MembershipStatusBadgeProps = {
    status: MembershipStatus;
    label?: string;
    className?: string;
};

const statusStyles: Record<MembershipStatus, string> = {
    active:
        'rounded-full border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300',

    inactive:
        'rounded-full border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',

    suspended:
        'rounded-full border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300',

    left:
        'rounded-full border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300',
};

const statusLabels: Record<MembershipStatus, string> = {
    active: 'Actif',
    inactive: 'Inactif',
    suspended: 'Suspendu',
    left: 'A quitté',
};

export function MembershipStatusBadge({
    status,
    label,
    className,
}: MembershipStatusBadgeProps) {
    return (
        <Badge
            variant="outline"
            className={cn(
                'whitespace-nowrap font-medium',
                statusStyles[status],
                className,
            )}
        >
            {label ?? statusLabels[status]}
        </Badge>
    );
}