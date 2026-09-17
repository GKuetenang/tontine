import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';

import { useTranslation } from '@/hooks/use-translation';
import groups from '@/routes/groups';
import type { Group } from '@/types';

import { Button } from '../ui/button';

export function EmptySessions({ group }: { group: Group }) {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col items-center justify-center gap-3 py-12 text-center">
            <CalendarDays className="size-8 text-muted-foreground" />

            <div>
                <p className="font-medium">{t('Aucune session')}</p>

                <p className="text-sm text-muted-foreground">
                    {t('Cette réunion ne possède encore aucune session.')}
                </p>
            </div>

            <Button asChild variant="outline">
                <Link href={groups.sessions.index(group.slug!)}>
                    {t('Gérer les sessions')}
                </Link>
            </Button>
        </div>
    );
}
