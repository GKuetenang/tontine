import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { useAuthorization } from '@/hooks/use-authorization';

import meetings from '@/routes/tontines/sessions/meetings';

import type {
    Meeting,
    Session,
    Tontine,
} from '@/types';

import { Link } from '@inertiajs/react';

import {
    BanIcon,
    DoorOpenIcon,
    EllipsisIcon,
    LockKeyholeIcon,
    PencilIcon,
    TrashIcon,
} from 'lucide-react';

import { toast } from 'sonner';

import { EditMeetingForm } from './form';

type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export function Actions({
    tontine,
    session,
    meeting,
}: Props) {
    const { can, canAny } = useAuthorization();

    const hasActions = canAny(
        'meetings.view',
        'meetings.update',
        'meetings.open',
        'meetings.close',
        'meetings.cancel',
        'meetings.delete',
    );

    if (!hasActions) {
        return (
            <span
                className="text-muted-foreground"
                aria-label="Aucune action disponible"
            >
                —
            </span>
        );
    }

    const routeParams = {
        tontine: tontine.slug!,
        session: session.slug,
        meeting: meeting.slug,
    };

    return (
        <div className="flex items-end">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        className="ml-auto"
                        variant="ghost"
                        size="icon"
                        aria-label="Actions de la réunion"
                    >
                        <EllipsisIcon className="size-4" />
                    </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end">
                    {can('meetings.update') &&
                        meeting.status === 'scheduled' && (
                            <EditMeetingForm
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                                trigger={
                                    <DropdownMenuItem
                                        onSelect={(event) =>
                                            event.preventDefault()
                                        }
                                    >
                                        <PencilIcon className="size-4" />

                                        Modifier
                                    </DropdownMenuItem>
                                }
                            />
                        )}

                    {can('meetings.open') &&
                        meeting.status === 'scheduled' && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={meetings.open(
                                        routeParams,
                                    )}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment ouvrir cette réunion ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(
                                                errors,
                                            )[0];

                                        toast.error(
                                            firstError,
                                        );
                                    }}
                                >
                                    <DoorOpenIcon className="size-4" />

                                    Ouvrir
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('meetings.close') &&
                        meeting.status === 'in_progress' && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={meetings.close(
                                        routeParams,
                                    )}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment clôturer cette réunion ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(
                                                errors,
                                            )[0];

                                        toast.error(
                                            firstError,
                                        );
                                    }}
                                >
                                    <LockKeyholeIcon className="size-4" />

                                    Clôturer
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('meetings.cancel') &&
                        meeting.status === 'scheduled' && (
                            <DropdownMenuItem asChild>
                                <Link
                                    className="w-full"
                                    href={meetings.cancel(
                                        routeParams,
                                    )}
                                    onBefore={() =>
                                        confirm(
                                            'Voulez-vous vraiment annuler cette réunion ?',
                                        )
                                    }
                                    onError={(errors) => {
                                        const firstError =
                                            Object.values(
                                                errors,
                                            )[0];

                                        toast.error(
                                            firstError,
                                        );
                                    }}
                                >
                                    <BanIcon className="size-4" />

                                    Annuler
                                </Link>
                            </DropdownMenuItem>
                        )}

                    {can('meetings.delete') &&
                        meeting.status === 'scheduled' && (
                            <>
                                <DropdownMenuSeparator />

                                <DropdownMenuItem
                                    asChild
                                    variant="destructive"
                                >
                                    <Link
                                        className="w-full"
                                        href={meetings.close(
                                            routeParams,
                                        )}
                                        onBefore={() =>
                                            confirm(
                                                'Voulez-vous vraiment supprimer cette réunion ?',
                                            )
                                        }
                                        onError={(errors) => {
                                            const firstError =
                                                Object.values(
                                                    errors,
                                                )[0];

                                            toast.error(
                                                firstError,
                                            );
                                        }}
                                    >
                                        <TrashIcon className="size-4" />

                                        Supprimer
                                    </Link>
                                </DropdownMenuItem>
                            </>
                        )}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}