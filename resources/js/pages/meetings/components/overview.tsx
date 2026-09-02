import {
    CalendarDaysIcon,
    CoinsIcon,
    MapPinIcon,
    UsersIcon,
} from 'lucide-react';
import { InformationRow } from '@/components/groups/information-row';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib';
import { getMeetingStatusLabel } from '@/lib/utils';
import type { Meeting } from '@/types';
import { SummaryCard } from './summary-card';

export function MeetingOverview({ meeting }: { meeting: Meeting }) {
    return (
        <>
            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <SummaryCard
                    title="Date prévue"
                    value={formatDate(meeting.scheduled_at)}
                    icon={CalendarDaysIcon}
                />

                <SummaryCard
                    title="Lieu"
                    value={meeting.location ?? 'Non défini'}
                    icon={MapPinIcon}
                />

                <SummaryCard
                    title="Présences"
                    value={meeting.attendances_count ?? 0}
                    icon={UsersIcon}
                />

                <SummaryCard
                    title="Cotisations"
                    value={meeting.contributions_count ?? 0}
                    icon={CoinsIcon}
                />
            </section>

            <section className="grid gap-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <CardTitle>Description</CardTitle>
                    </CardHeader>

                    <CardContent>
                        {meeting.description ? (
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                {meeting.description}
                            </p>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Aucune description.
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Informations</CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <InformationRow
                            label="Numéro"
                            value={`#${meeting.number}`}
                        />

                        <InformationRow
                            label="Statut"
                            value={getMeetingStatusLabel(meeting.status)}
                        />

                        <InformationRow
                            label="Date prévue"
                            value={formatDate(meeting.scheduled_at)}
                        />

                        <InformationRow
                            label="Ouverture"
                            value={formatDate(meeting.opened_at)}
                        />

                        <InformationRow
                            label="Clôture"
                            value={formatDate(meeting.closed_at)}
                        />

                        <InformationRow
                            label="Lieu"
                            value={meeting.location ?? '—'}
                        />
                    </CardContent>
                </Card>
            </section>
        </>
    );
}
