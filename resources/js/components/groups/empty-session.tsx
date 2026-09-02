import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';
import groups from '@/routes/groups';
import type { Group } from '@/types';
import { Button } from '../ui/button';

export function EmptySessions({ group }: { group: Group }) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-12 text-center">
            <CalendarDays className="size-8 text-muted-foreground" />

            <div>
                <p className="font-medium">Aucune session</p>

                <p className="text-sm text-muted-foreground">
                    Cette réunion ne possède encore aucune session.
                </p>
            </div>

            <Button asChild variant="outline">
                <Link href={groups.sessions.index(group.slug!)}>
                    Gérer les sessions
                </Link>
            </Button>
        </div>
    );
}
