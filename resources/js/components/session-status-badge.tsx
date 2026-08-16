import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { Session } from '@/types';

type SessionStatusProps = {
    session: Session;
    className?: string;
};

export function SessionStatusBadge({ session, className }: SessionStatusProps) {
    switch (session.status) {
        case 'draft':
            return (
                <Badge
                    variant="secondary"
                    className={cn('rounded-full', className)}
                >
                    Préparation
                </Badge>
            );

        case 'active':
            return (
                <Badge
                    className={cn(
                        'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300',
                        className,
                    )}
                >
                    Active
                </Badge>
            );

        case 'closed':
            return (
                <Badge className={cn(className)} variant="secondary">
                    Fermée
                </Badge>
            );
    }
}
