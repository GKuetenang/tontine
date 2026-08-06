import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { Session } from '@/types';
import { CheckCircle2, CirclePause, LockKeyhole } from 'lucide-react';

type SessionStatusProps = {
    session: Session;
    className?: string;
};

export function SessionStatusBadge({
    session,
    className,
}: SessionStatusProps) {
    if (session.is_closed) {
        return (
            <Badge
                variant="secondary"
                className={cn(
                    'gap-1.5 bg-muted text-muted-foreground rounded-full',
                    className,
                )}
            >
                <LockKeyhole className="size-3.5" />
                Fermée
            </Badge>
        );
    }

    if (session.is_active) {
        return (
            <Badge
                className={cn(
                    'gap-1.5 bg-green-100 text-green-700 hover:bg-green-100 rounded-full',
                    'dark:bg-green-950 dark:text-green-300 rounded-full',
                    className,
                )}
            >
                <CheckCircle2 className="size-3.5" />
                Active
            </Badge>
        );
    }

    return (
        <Badge
            variant="outline"
            className={cn(
                'gap-1.5 text-amber-700 dark:text-amber-300 rounded-full',
                className,
            )}
        >
            <CirclePause className="size-3.5" />
            Inactive
        </Badge>
    );
}