import { Link } from '@inertiajs/react';
import { formatCurrency } from '@/lib/utils';
import groups from '@/routes/groups';
import type { Session, Group } from '@/types';
import { SessionStatusBadge } from '../session-status-badge';

export function SessionRow({
    group,
    session,
}: {
    group: Group;
    session: Session;
}) {
    return (
        <div className="flex items-center justify-between gap-4 py-4">
            <div className="min-w-0">
                <Link
                    href={groups.sessions.show({
                        group: group.slug!,
                        session: session.slug,
                    })}
                    className="font-medium hover:underline"
                >
                    {session.name}
                </Link>

                <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                    <span>
                        {formatCurrency(
                            session.default_contribution_amount,
                            group.currency,
                        )}
                    </span>

                    <span>•</span>

                    <span>
                        {session.participants_count ?? 0} participant
                        {(session.participants_count ?? 0) > 1 ? 's' : ''}
                    </span>
                </div>
            </div>

            <SessionStatusBadge session={session} />
        </div>
    );
}
