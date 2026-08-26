
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';

import { Button } from '@/components/ui/button';

import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';

import { useAuthorization } from '@/hooks/use-authorization';

import { withAppLayout } from '@/layouts/app-layout';

import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import drawRoutes from '@/routes/tontines/sessions/draw';
import sessionParticipants from '@/routes/tontines/sessions/participants';

import type {
    Draw,
    ResultTontine,
    Session,
} from '@/types';

import {
    Head,
    Link,
} from '@inertiajs/react';

import {
    AlertTriangleIcon,
    CheckIcon,
    RefreshCcwIcon,
    RotateCcwIcon,
    TrashIcon,
} from 'lucide-react';

import { toast } from 'sonner';

import {
    DrawEntriesTable,
} from './draw-entries-table';

type Props = {
    tontine: ResultTontine;
    session: Session;
    draw: Draw | null;
};

export default withAppLayout<Props>(
    ({ tontine, session }) => [
        {
            title: 'Tontines',

            href:
                tontines
                    .index()
                    .url,
        },

        {
            title: 'Sessions',

            href:
                sessions
                    .index({
                        tontine:
                            tontine.slug,
                    })
                    .url,
        },

        {
            title:
                session.name,

            href:
                sessionParticipants
                    .index({
                        tontine:
                            tontine.slug,

                        session:
                            session.slug,
                    })
                    .url,
        },

        {
            title: 'Tirage',
            href: '#',
        },
    ],

    ({
        tontine,
        session,
        draw,
    }: Props) => {
        const { can } =
            useAuthorization();

        const isDraft =
            session.status ===
            'draft';

        const isActive =
            session.status ===
            'active';

        const isConfirmed =
            Boolean(
                draw?.confirmed_at,
            );

        const entries =
            draw?.entries ?? [];

        /*
         * Le drag-and-drop n'est disponible
         * que lorsque :
         *
         * - la session est active ;
         * - un tirage existe ;
         * - le tirage n'est pas confirmé ;
         * - l'utilisateur possède la permission.
         */
        const canSwap =
            isActive
            && Boolean(draw)
            && !isConfirmed
            && can(
                'draws.update',
            );

        /*
         * Permet de recréer DrawEntriesTable
         * lorsque Inertia retourne les nouvelles
         * positions après un swap.
         */
        const entriesKey =
            entries.length > 0
                ? entries
                    .map(
                        (
                            entry,
                        ) =>
                            [
                                entry.id,
                                entry.position,
                                entry.updated_at,
                            ].join(
                                ':',
                            ),
                    )
                    .join('|')
                : 'empty';

        const handleError = (
            errors: Record<
                string,
                string
            >,
        ) => {
            const firstError =
                Object.values(
                    errors,
                )[0];

            if (firstError) {
                toast.error(
                    firstError,
                );
            }
        };

        return (
            <>
                <Head
                    title={`Tirage - ${session.name}`}
                />
                <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Tirage — {session.name}
                        </h1>

                        {draw && (
                            <span
                                className={[
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    isConfirmed
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-primary/10 text-primary',
                                ].join(' ')}
                            >
                                {isConfirmed
                                    ? 'Confirmé'
                                    : 'Non confirmé'}
                            </span>
                        )}
                    </div>

                    <p className="text-sm text-muted-foreground">
                        Gérez et consultez l’ordre des tours de cette session.
                    </p>

                    {/* {draw && (
                        <p className="text-sm text-muted-foreground">
                            {entries.length}{' '}
                            tour
                            {entries.length > 1 ? 's' : ''}
                        </p>
                    )} */}
                </div>

                <div className="space-y-4 mt-6">
                    <Card>
                        <CardHeader>
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p className="font-semibold">
                                        Ordre du tirage
                                    </p>

                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {canSwap
                                            ? 'Glissez une entrée sur une autre pour permuter leurs positions.'
                                            : 'Consultez l’ordre des participants pour cette session.'}
                                    </p>
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {!draw &&
                                        can('session-participants.view') && (
                                            <Button
                                                variant="outline"
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        sessionParticipants.index({
                                                            tontine:
                                                                tontine.slug,
                                                            session:
                                                                session.slug,
                                                        }).url
                                                    }
                                                >
                                                    Participants
                                                </Link>
                                            </Button>
                                        )}

                                    {isActive
                                        && can(
                                            'draws.generate',
                                        ) && (
                                            <Button asChild>
                                                <Link
                                                    href={
                                                        drawRoutes.generate({
                                                            tontine:
                                                                tontine.slug,
                                                            session:
                                                                session.slug,
                                                        })
                                                    }
                                                    method="post"
                                                    as="button"
                                                    onBefore={() =>
                                                        draw
                                                            ? confirm(
                                                                'Voulez-vous régénérer le tirage ? Le résultat actuel sera remplacé.',
                                                            )
                                                            : confirm(
                                                                'Voulez-vous générer le tirage ?',
                                                            )
                                                    }
                                                    onError={
                                                        handleError
                                                    }
                                                >
                                                    <RefreshCcwIcon className="size-4" />

                                                    {draw
                                                        ? 'Régénérer'
                                                        : 'Générer le tirage'}
                                                </Link>
                                            </Button>
                                        )}
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent>
                            {isDraft && (
                                <Alert className="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50">
                                    <AlertTriangleIcon />

                                    <AlertDescription>
                                        La
                                        session
                                        est
                                        encore
                                        en
                                        préparation.
                                        Activez-la
                                        avant
                                        de
                                        générer
                                        le
                                        tirage.
                                    </AlertDescription>
                                </Alert>
                            )}

                            {!draw
                                && !isDraft && (
                                    <Alert className="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50">
                                        <AlertTriangleIcon />

                                        <AlertTitle>
                                            Aucun
                                            tirage
                                            généré
                                        </AlertTitle>

                                        <AlertDescription>
                                            Générez
                                            le
                                            tirage
                                            pour
                                            déterminer
                                            l’ordre
                                            des
                                            participants.
                                        </AlertDescription>
                                    </Alert>
                                )}

                            {draw && (
                                <div className="space-y-4">
                                    {!isConfirmed && (
                                        <div className="flex flex-wrap justify-end gap-2">
                                            {can(
                                                'draws.reset',
                                            ) && (
                                                    <Button
                                                        variant="outline"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={
                                                                drawRoutes.reset({
                                                                    tontine:
                                                                        tontine.slug,
                                                                    session:
                                                                        session.slug,
                                                                })
                                                            }
                                                            method="patch"
                                                            as="button"
                                                            onBefore={() =>
                                                                confirm(
                                                                    'Voulez-vous réinitialiser ce tirage ?',
                                                                )
                                                            }
                                                            onError={
                                                                handleError
                                                            }
                                                        >
                                                            <RotateCcwIcon className="size-4" />
                                                            Réinitialiser
                                                        </Link>
                                                    </Button>
                                                )}

                                            {can(
                                                'draws.delete',
                                            ) && (
                                                    <Button
                                                        variant="destructive-outline"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={
                                                                drawRoutes.destroy({
                                                                    tontine:
                                                                        tontine.slug,
                                                                    session:
                                                                        session.slug,
                                                                })
                                                            }
                                                            method="delete"
                                                            as="button"
                                                            onBefore={() =>
                                                                confirm(
                                                                    'Voulez-vous vraiment supprimer ce tirage ?',
                                                                )
                                                            }
                                                            onError={
                                                                handleError
                                                            }
                                                        >
                                                            <TrashIcon className="size-4" />
                                                            Supprimer
                                                        </Link>
                                                    </Button>
                                                )}

                                            {can(
                                                'draws.confirm',
                                            ) && (
                                                    <Button asChild>
                                                        <Link
                                                            href={
                                                                drawRoutes.confirm({
                                                                    tontine:
                                                                        tontine.slug,
                                                                    session:
                                                                        session.slug,
                                                                })
                                                            }
                                                            method="patch"
                                                            as="button"
                                                            onBefore={() =>
                                                                confirm(
                                                                    'Confirmer définitivement ce tirage ?',
                                                                )
                                                            }
                                                            onError={
                                                                handleError
                                                            }
                                                        >
                                                            <CheckIcon className="size-4" />
                                                            Confirmer
                                                        </Link>
                                                    </Button>
                                                )}
                                        </div>
                                    )}

                                    {entries.length > 0 ? (
                                        <DrawEntriesTable
                                            key={entriesKey}
                                            tontine={tontine}
                                            session={session}
                                            draw={draw}
                                            canSwap={canSwap}
                                        />
                                    ) : (
                                        <div className="rounded-md border border-dashed p-6 text-sm text-muted-foreground">
                                            Ce tirage ne contient actuellement aucune entrée.
                                        </div>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </>
        );
    },
);