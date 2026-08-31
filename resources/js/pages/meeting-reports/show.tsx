import { RichTextContent } from '@/components/rich-text-content';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { withAppLayout } from '@/layouts/app-layout';
import { formatDate } from '@/lib';
import { formatCurrency } from '@/lib/utils';
import tontines from '@/routes/tontines';
import sessions from '@/routes/tontines/sessions';
import meetings from '@/routes/tontines/sessions/meetings';
import type { BreadcrumbItem, MeetingReport, Session, Tontine } from '@/types';
import { Head } from '@inertiajs/react';
import { PrinterIcon } from 'lucide-react';

type Props = {
    tontine: Tontine;
    session: Session;
    report: MeetingReport;
    canExport: boolean;
};

const attendanceLabels = {
    pending: 'En attente',
    present: 'Présent',
    absent: 'Absent',
    excused: 'Absent justifié',
    late: 'En retard',
} as const;

const contributionLabels = {
    unpaid: 'Non payée',
    partial: 'Partiellement payée',
    paid: 'Payée',
} as const;

const payoutLabels = {
    pending: 'En attente',
    paid: 'Payé',
    cancelled: 'Annulé',
} as const;

function memberName(participant?: {
    membership?: {
        user?: { name: string };
    };
}): string {
    return participant?.membership?.user?.name ?? '—';
}

function ReportSection({
    title,
    count,
    children,
}: {
    title: string;
    count: number;
    children: React.ReactNode;
}) {
    return (
        <Card className="break-inside-avoid shadow-none">
            <CardHeader className="border-b">
                <CardTitle>
                    {title}{' '}
                    <span className="font-normal text-muted-foreground">
                        ({count})
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

export default withAppLayout<Props>(
    ({ tontine, session, report }) =>
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
                title: session.name,
                href: sessions.show({
                    tontine: tontine.slug!,
                    session: session.slug,
                }),
            },
            {
                title: `Réunion #${report.meeting.number}`,
                href: meetings.show({
                    tontine: tontine.slug!,
                    session: session.slug,
                    meeting: report.meeting.slug,
                }),
            },
            {
                title: 'Rapport',
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({ tontine, session, report, canExport }: Props) => {
        const { meeting, summary } = report;
        const agendaItems = meeting.agenda_items ?? [];
        const attendances = meeting.attendances ?? [];
        const contributions = meeting.contributions ?? [];
        const payouts = meeting.payouts ?? [];
        const notes = meeting.notes ?? [];
        const decisions = meeting.decisions ?? [];

        return (
            <>
                <Head title={`Rapport — ${meeting.title}`} />

                <div
                    id="meeting-report"
                    className="mx-auto flex w-full flex-col gap-6 print:max-w-none"
                >
                    <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
                        <div></div>

                        {canExport && (
                            <Button
                                type="button"
                                onClick={() => window.print()}
                            >
                                <PrinterIcon className="size-4" />
                                Exporter en PDF
                            </Button>
                        )}
                    </div>

                    <header className="space-y-3 border-b pb-6">
                        <p className="text-sm font-medium tracking-wide text-primary uppercase">
                            {tontine.name} · {session.name}
                        </p>
                        <div>
                            <h1 className="text-3xl font-bold">
                                Rapport de la réunion #{meeting.number}
                            </h1>
                            <p className="mt-1 text-xl text-muted-foreground">
                                {meeting.title}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground">
                            <span>{formatDate(meeting.scheduled_at)}</span>
                            {meeting.location && (
                                <span>{meeting.location}</span>
                            )}
                            <span>Statut : {meeting.status}</span>
                        </div>
                        {meeting.description && (
                            <p className="max-w-3xl text-sm">
                                {meeting.description}
                            </p>
                        )}
                    </header>

                    <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Card className="shadow-none">
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Présents / retards
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {summary.present_total + summary.late_total}
                                    <span className="text-base font-normal text-muted-foreground">
                                        {' '}
                                        / {summary.attendances_total}
                                    </span>
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="shadow-none">
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Cotisations encaissées
                                </p>
                                <p className="mt-1 text-xl font-semibold">
                                    {formatCurrency(summary.contributions_paid)}
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="shadow-none">
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Cotisations restantes
                                </p>
                                <p className="mt-1 text-xl font-semibold">
                                    {formatCurrency(
                                        summary.contributions_remaining,
                                    )}
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="shadow-none">
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Versements payés
                                </p>
                                <p className="mt-1 text-xl font-semibold">
                                    {formatCurrency(summary.payouts_paid)}
                                </p>
                            </CardContent>
                        </Card>
                    </section>

                    <ReportSection
                        title="Ordre du jour"
                        count={agendaItems.length}
                    >
                        {agendaItems.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Aucun point à l’ordre du jour.
                            </p>
                        ) : (
                            <div className="space-y-4">
                                {agendaItems.map((item) => (
                                    <div
                                        key={item.id}
                                        className="border-b pb-4 last:border-0 last:pb-0"
                                    >
                                        <h3 className="font-medium">
                                            {item.position}. {item.title}
                                        </h3>
                                        {item.description && (
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {item.description}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </ReportSection>

                    <ReportSection title="Présences" count={attendances.length}>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Membre</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead>Arrivée</TableHead>
                                    <TableHead>Note</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {attendances.map((attendance) => (
                                    <TableRow key={attendance.id}>
                                        <TableCell>
                                            {memberName(
                                                attendance.session_participant,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {attendanceLabels[
                                                attendance.status as keyof typeof attendanceLabels
                                            ] ?? attendance.status}
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(
                                                attendance.checked_in_at,
                                            )}
                                        </TableCell>
                                        <TableCell className="whitespace-normal">
                                            {attendance.note ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </ReportSection>

                    <ReportSection
                        title="Cotisations"
                        count={contributions.length}
                    >
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Membre</TableHead>
                                    <TableHead>Dû</TableHead>
                                    <TableHead>Payé</TableHead>
                                    <TableHead>Restant</TableHead>
                                    <TableHead>Statut</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {contributions.map((contribution) => (
                                    <TableRow key={contribution.id}>
                                        <TableCell>
                                            {memberName(
                                                contribution.session_participant,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(
                                                contribution.amount_due,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(
                                                contribution.amount_paid,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(
                                                contribution.remaining_amount,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {contributionLabels[
                                                contribution.status as keyof typeof contributionLabels
                                            ] ?? contribution.status}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </ReportSection>

                    <ReportSection title="Versements" count={payouts.length}>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Bénéficiaire</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Montant</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead>Payé le</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {payouts.map((payout) => (
                                    <TableRow key={payout.id}>
                                        <TableCell>
                                            {memberName(
                                                payout.draw_entry
                                                    ?.session_participant,
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {payout.draw_entry?.position ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(payout.amount)}
                                        </TableCell>
                                        <TableCell>
                                            {payoutLabels[
                                                payout.status as keyof typeof payoutLabels
                                            ] ?? payout.status}
                                        </TableCell>
                                        <TableCell>
                                            {formatDate(payout.paid_at)}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </ReportSection>

                    <ReportSection title="Notes" count={notes.length}>
                        {notes.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Aucune note.
                            </p>
                        ) : (
                            <div className="space-y-5">
                                {notes.map((note) => (
                                    <article
                                        key={note.id}
                                        className="break-inside-avoid border-b pb-5 last:border-0 last:pb-0"
                                    >
                                        <p className="mb-2 text-xs font-medium text-primary">
                                            {note.agenda_item
                                                ? `${note.agenda_item.position}. ${note.agenda_item.title}`
                                                : 'Note générale'}
                                        </p>
                                        <RichTextContent
                                            content={note.content}
                                        />
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {note.creator?.name ??
                                                'Auteur inconnu'}{' '}
                                            · {formatDate(note.created_at)}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        )}
                    </ReportSection>

                    <ReportSection title="Décisions" count={decisions.length}>
                        {decisions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Aucune décision.
                            </p>
                        ) : (
                            <div className="space-y-5">
                                {decisions.map((decision) => (
                                    <article
                                        key={decision.id}
                                        className="break-inside-avoid border-b pb-5 last:border-0 last:pb-0"
                                    >
                                        <p className="mb-1 text-xs font-medium text-primary">
                                            {decision.agenda_item
                                                ? `${decision.agenda_item.position}. ${decision.agenda_item.title}`
                                                : 'Décision générale'}
                                        </p>
                                        <h3 className="font-semibold">
                                            {decision.title}
                                        </h3>
                                        {decision.description && (
                                            <div className="mt-2">
                                                <RichTextContent
                                                    content={
                                                        decision.description
                                                    }
                                                />
                                            </div>
                                        )}
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {decision.creator?.name ??
                                                'Auteur inconnu'}{' '}
                                            · {formatDate(decision.created_at)}
                                        </p>
                                    </article>
                                ))}
                            </div>
                        )}
                    </ReportSection>

                    <footer className="border-t pt-4 text-xs text-muted-foreground">
                        Rapport généré le {formatDate(new Date().toISOString())}
                    </footer>
                </div>
            </>
        );
    },
);
