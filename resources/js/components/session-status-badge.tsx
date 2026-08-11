import { Badge } from '@/components/ui/badge';
import { Session } from '@/types';

type SessionStatusProps = {
    session: Session;
    className?: string;
};

export function SessionStatusBadge({
    session,
    className,
}: SessionStatusProps) {
    switch (session.status) {
        case 'draft':
            return <Badge variant="secondary" className='rounded-full'>Préparation</Badge>;

        case 'active':
            return <Badge className="bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300">Active</Badge>;

        case 'closed':
            return <Badge variant="secondary">Fermée</Badge>;
    }
}