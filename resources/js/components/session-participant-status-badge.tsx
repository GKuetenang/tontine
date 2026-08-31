import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type Props = {
    isActive: boolean;
    label?: string;
    className?: string;
};

const statusStyles: Record<'active' | 'inactive', string> = {
    active: 'rounded-full border-green-200 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950 dark:text-green-300',

    inactive:
        'rounded-full border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
};

const statusLabels: Record<'active' | 'inactive', string> = {
    active: 'Actif',
    inactive: 'Inactif',
};

export function SessionParticipantStatusBadge({
    isActive,
    label,
    className,
}: Props) {
    const status = isActive ? 'active' : 'inactive';

    return (
        <Badge
            variant="outline"
            className={cn(
                'font-medium whitespace-nowrap',
                statusStyles[status],
                className,
            )}
        >
            {label ?? statusLabels[status]}
        </Badge>
    );
}
