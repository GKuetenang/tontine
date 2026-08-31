import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';
import tontines from '@/routes/tontines';
import type { Tontine } from '@/types';
import { Button } from '../ui/button';

export function EmptySessions({ tontine }: { tontine: Tontine }) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-12 text-center">
            <CalendarDays className="size-8 text-muted-foreground" />

            <div>
                <p className="font-medium">Aucune session</p>

                <p className="text-sm text-muted-foreground">
                    Cette tontine ne possède encore aucune session.
                </p>
            </div>

            <Button asChild variant="outline">
                <Link href={tontines.sessions.index(tontine.slug!)}>
                    Gérer les sessions
                </Link>
            </Button>
        </div>
    );
}
