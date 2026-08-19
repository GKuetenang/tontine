import Heading from '@/components/heading';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangleIcon,
    CheckIcon,
    RefreshCcwIcon,
    RotateCcwIcon,
    TrashIcon,
} from 'lucide-react';
import { toast } from 'sonner';

type Props = {
    tontine: ResultTontine;
    session: Session;
    draw: Draw | null;
};

export default withAppLayout<Props>(
    ({ tontine, session }) => [
        {
            title: 'Tontines',
            href: tontines.index().url,
        },
        {
            title: 'Sessions',
            href: sessions.index({
                tontine: tontine.slug,
            }).url,
        },
        {
            title: session.name,
            href: sessionParticipants.index({
                tontine: tontine.slug,
                session: session.slug,
            }).url,
        },
        {
            title: 'Tirage',
            href: '#',
        },
    ],
    ({ tontine, session, draw }: Props) => {
        const { can } = useAuthorization();

        const isDraft = session.status === 'draft';
        const isActive = session.status === 'active';

        const isConfirmed = !!draw?.confirmed_at;

        const entries = draw?.entries ?? [];

        const handleError = (
            errors: Record<string, string>,
        ) => {
            const firstError =
                Object.values(errors)[0];

            if (firstError) {
                toast.error(firstError);
            }
        };

        return (
            <>
                <Head title={`Tirage - ${session.name}`} />

                <Heading
                    title={`Tirage - ${session.name}`}
                    description='  Gérez et consultez l’ordre des tours de cette session.'
                />

                <div className="space-y-4">
                    <Card>
                        <CardHeader>
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>

                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {can(
                                        'session-participants.view',
                                    ) && (
                                            <Button
                                                variant="outline"
                                                asChild
                                            >
                                                <Link
                                                    href={sessionParticipants.index(
                                                        {
                                                            tontine:
                                                                tontine.slug,
                                                            session:
                                                                session.slug,
                                                        },
                                                    )}
                                                >
                                                    Participants
                                                </Link>
                                            </Button>
                                        )}

                                    {isActive &&
                                        can(
                                            'draws.generate',
                                        ) && (
                                            <Button
                                                asChild
                                            >
                                                <Link
                                                    href={drawRoutes.generate(
                                                        {
                                                            tontine:
                                                                tontine.slug,
                                                            session:
                                                                session.slug,
                                                        },
                                                    )}
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
                                        La session est encore en
                                        préparation. Activez-la avant
                                        de générer le tirage.
                                    </AlertDescription>
                                </Alert>
                            )}

                            {!draw && !isDraft && (
                                <Alert className="border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50">
                                    <AlertTriangleIcon />
                                    <AlertTitle>
                                        Aucun tirage généré
                                    </AlertTitle>

                                    <AlertDescription>
                                        Générez le tirage pour
                                        déterminer l’ordre des
                                        participants.
                                    </AlertDescription>
                                </Alert>
                            )}

                            {draw && (
                                <div className="space-y-4">
                                    <div className="flex flex-col gap-3 rounded-md border p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p className="font-medium">
                                                {isConfirmed
                                                    ? 'Tirage confirmé'
                                                    : 'Tirage non confirmé'}
                                            </p>

                                            <p className="text-sm text-muted-foreground">
                                                {
                                                    entries.length
                                                }{' '}
                                                tour
                                                {entries.length >
                                                    1
                                                    ? 's'
                                                    : ''}
                                            </p>
                                        </div>

                                        {!isConfirmed && (
                                            <div className="flex flex-wrap gap-2">
                                                {can(
                                                    'draws.reset',
                                                ) && (
                                                        <Button
                                                            variant="outline"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={drawRoutes.reset(
                                                                    {
                                                                        tontine:
                                                                            tontine.slug,
                                                                        session:
                                                                            session.slug,
                                                                    },
                                                                )}
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
                                                                href={drawRoutes.destroy(
                                                                    {
                                                                        tontine:
                                                                            tontine.slug,
                                                                        session:
                                                                            session.slug,
                                                                    },
                                                                )}
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
                                                        <Button
                                                            asChild
                                                        >
                                                            <Link
                                                                href={drawRoutes.confirm(
                                                                    {
                                                                        tontine:
                                                                            tontine.slug,
                                                                        session:
                                                                            session.slug,
                                                                    },
                                                                )}
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
                                    </div>

                                    {entries.length > 0 ? (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead className="w-24">
                                                        Position
                                                    </TableHead>

                                                    <TableHead>
                                                        Participant
                                                    </TableHead>

                                                    <TableHead>
                                                        Numéro du
                                                        tour
                                                    </TableHead>
                                                </TableRow>
                                            </TableHeader>

                                            <TableBody>
                                                {entries.map(
                                                    (entry) => (
                                                        <TableRow
                                                            key={
                                                                entry.id
                                                            }
                                                        >
                                                            <TableCell className="font-medium">
                                                                {
                                                                    entry.position
                                                                }
                                                            </TableCell>

                                                            <TableCell>
                                                                {entry
                                                                    .session_participant
                                                                    ?.membership
                                                                    ?.user
                                                                    ?.name ??
                                                                    '—'}
                                                            </TableCell>

                                                            <TableCell>
                                                                {
                                                                    entry.entry_number
                                                                }
                                                            </TableCell>
                                                        </TableRow>
                                                    ),
                                                )}
                                            </TableBody>
                                        </Table>
                                    ) : (
                                        <div className="rounded-md border border-dashed p-6 text-sm text-muted-foreground">
                                            Ce tirage ne contient
                                            actuellement aucune
                                            entrée.
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