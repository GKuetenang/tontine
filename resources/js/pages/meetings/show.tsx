import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { InformationRow } from '@/components/tontines/information-row';
import { withAppLayout } from '@/layouts/app-layout';

import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import meetings from '@/routes/tontines/sessions/meetings';

import type {
    BreadcrumbItem,
    Meeting,
    Session,
    Tontine,
} from '@/types';

import { Head } from '@inertiajs/react';

import { EmptySection } from '@/components/meetings/empty-section';
import { MeetingHeader } from '@/components/meetings/meeting-header';
import { MeetingModuleCard } from '@/components/meetings/meeting-module-card';
import { SummaryCard } from '@/components/meetings/summary-card';
import { formatDate } from '@/lib';
import { getMeetingStatusLabel } from '@/lib/utils';
import {
    CalendarDays,
    CheckCircle2Icon,
    ClipboardListIcon,
    CoinsIcon,
    MapPinIcon,
    NotebookPenIcon,
    UsersIcon
} from 'lucide-react';
import { MeetingAgenda } from '../meeting-agendas/show';
import { MeetingAttendances } from '../meeting-attendances/show';



type Props = {
    tontine: Tontine;
    session: Session;
    meeting: Meeting;
};

export default withAppLayout<Props>(
    ({ tontine, session, meeting }) =>
        [
            {
                title: 'Tontines',
                href: tontines.index(),
            },
            {
                title: tontine.name,
                href: tontines.show({
                    tontine: tontine.slug!,
                }),
            },
            {
                title: 'Sessions',
                href: sessions.index({
                    tontine: tontine.slug!,
                }),
            },
            {
                title: session.name,
                href: sessions.show({
                    tontine: tontine.slug!,
                    session: session.slug,
                }),
            },
            {
                title: 'Réunions',
                href: meetings.index({
                    tontine: tontine.slug!,
                    session: session.slug,
                }),
            },
            {
                title: `Réunion #${meeting.number}`,
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({ tontine, session, meeting }: Props) => {
        return (
            <>
                <Head title={meeting.title} />

                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl">
                    <MeetingHeader
                        meeting={meeting}
                    />

                    <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <SummaryCard
                            title="Date prévue"
                            value={formatDate(
                                meeting.scheduled_at,
                            )}
                            icon={CalendarDays}
                        />

                        <SummaryCard
                            title="Lieu"
                            value={
                                meeting.location ??
                                'Non défini'
                            }
                            icon={MapPinIcon}
                        />

                        <SummaryCard
                            title="Présences"
                            value={
                                meeting.attendances_count ??
                                0
                            }
                            icon={UsersIcon}
                        />

                        <SummaryCard
                            title="Cotisations"
                            value={
                                meeting.contributions_count ??
                                0
                            }
                            icon={CoinsIcon}
                        />
                    </section>

                    <section className="grid gap-6 xl:grid-cols-3">
                        <Card className="xl:col-span-2">
                            <CardHeader>
                                <CardTitle>
                                    Gestion de la réunion
                                </CardTitle>
                            </CardHeader>

                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <MeetingModuleCard
                                    title="Ordre du jour"
                                    description="Préparer et organiser les points à traiter pendant la réunion."
                                    icon={ClipboardListIcon}
                                    href="#agenda"
                                />

                                <MeetingModuleCard
                                    title="Présences"
                                    description="Enregistrer les présences, absences et retards."
                                    icon={UsersIcon}
                                    href="#attendances"
                                />

                                <MeetingModuleCard
                                    title="Cotisations"
                                    description="Consulter les cotisations dues et enregistrer les paiements."
                                    icon={CoinsIcon}
                                    href="#contributions"
                                />

                                <MeetingModuleCard
                                    title="Notes"
                                    description="Consigner les informations importantes discutées pendant la réunion."
                                    icon={NotebookPenIcon}
                                    href="#notes"
                                />

                                <MeetingModuleCard
                                    title="Décisions"
                                    description="Enregistrer les décisions prises pendant la réunion."
                                    icon={CheckCircle2Icon}
                                    href="#decisions"
                                />
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Informations
                                </CardTitle>
                            </CardHeader>

                            <CardContent className="space-y-4">
                                <InformationRow
                                    label="Numéro"
                                    value={`#${meeting.number}`}
                                />

                                <InformationRow
                                    label="Statut"
                                    value={getMeetingStatusLabel(
                                        meeting.status,
                                    )}
                                />

                                <InformationRow
                                    label="Date prévue"
                                    value={formatDate(
                                        meeting.scheduled_at,
                                    )}
                                />

                                <InformationRow
                                    label="Ouverture"
                                    value={formatDate(
                                        meeting.opened_at,
                                    )}
                                />

                                <InformationRow
                                    label="Clôture"
                                    value={formatDate(
                                        meeting.closed_at,
                                    )}
                                />

                                <InformationRow
                                    label="Lieu"
                                    value={
                                        meeting.location ??
                                        '—'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </section>

                    {meeting.description && (
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Description
                                </CardTitle>
                            </CardHeader>

                            <CardContent>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {meeting.description}
                                </p>
                            </CardContent>
                        </Card>
                    )}

                    <section
                        id="agenda"
                        className="scroll-mt-6"
                    >
                        <MeetingAgenda
                            tontine={tontine}
                            session={session}
                            meeting={meeting}
                        />
                    </section>

                    <section
                        id="attendances"
                        className="scroll-mt-6"
                    >
                        <MeetingAttendances
                            tontine={tontine}
                            session={session}
                            meeting={meeting}
                        />
                    </section>

                    <section
                        id="contributions"
                        className="scroll-mt-6"
                    >
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Cotisations
                                </CardTitle>
                            </CardHeader>

                            <CardContent>
                                {meeting.status ===
                                    'scheduled' ? (
                                    <EmptySection
                                        icon={CoinsIcon}
                                        title="Aucune cotisation générée"
                                        description="Les obligations de cotisation seront créées automatiquement à l’ouverture de la réunion."
                                    />
                                ) : (
                                    <EmptySection
                                        icon={CoinsIcon}
                                        title="Cotisations de la réunion"
                                        description="Les montants dus, payés et restants seront affichés ici."
                                    />
                                )}
                            </CardContent>
                        </Card>
                    </section>

                    <section
                        id="notes"
                        className="scroll-mt-6"
                    >
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between">
                                <CardTitle>
                                    Notes
                                </CardTitle>

                                {meeting.status ===
                                    'in_progress' && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                        >
                                            Ajouter une note
                                        </Button>
                                    )}
                            </CardHeader>

                            <CardContent>
                                <EmptySection
                                    icon={NotebookPenIcon}
                                    title="Aucune note"
                                    description="Les notes prises pendant la réunion seront affichées ici."
                                />
                            </CardContent>
                        </Card>
                    </section>

                    <section
                        id="decisions"
                        className="scroll-mt-6"
                    >
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between">
                                <CardTitle>
                                    Décisions
                                </CardTitle>

                                {meeting.status ===
                                    'in_progress' && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                        >
                                            Ajouter une décision
                                        </Button>
                                    )}
                            </CardHeader>

                            <CardContent>
                                <EmptySection
                                    icon={
                                        CheckCircle2Icon
                                    }
                                    title="Aucune décision"
                                    description="Les décisions prises pendant la réunion seront affichées ici."
                                />
                            </CardContent>
                        </Card>
                    </section>
                </div>
            </>
        );
    },
);