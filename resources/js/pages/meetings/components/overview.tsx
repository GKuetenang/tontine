import {
    CalendarDaysIcon,
    CoinsIcon,
    MapPinIcon,
    UsersIcon,
} from 'lucide-react';
import { InformationRow } from '@/components/groups/information-row';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import { formatDate } from '@/lib';
import { getMeetingStatusLabel } from '@/lib/utils';
import type { Meeting } from '@/types';
import { SummaryCard } from './summary-card';

export function MeetingOverview({ meeting }: { meeting: Meeting }) {
    const { t } = useTranslation();

    return (
        <>
            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <SummaryCard
                    title={t('Date prévue')}
                    value={formatDate(meeting.scheduled_at)}
                    icon={CalendarDaysIcon}
                />

                <SummaryCard
                    title={t('Lieu')}
                    value={meeting.location ?? 'Non défini'}
                    icon={MapPinIcon}
                />

                <SummaryCard
                    title={t('Présences')}
                    value={meeting.attendances_count ?? 0}
                    icon={UsersIcon}
                />

                <SummaryCard
                    title={t('Cotisations')}
                    value={meeting.contributions_count ?? 0}
                    icon={CoinsIcon}
                />
            </section>

            <section className="grid gap-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <CardTitle>{t('Description')}</CardTitle>
                    </CardHeader>

                    <CardContent>
                        {meeting.description ? (
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                {meeting.description}
                            </p>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                {t('Aucune description.')}
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Informations')}</CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <InformationRow
                            label={t('Numéro')}
                            value={`#${meeting.number}`}
                        />

                        <InformationRow
                            label={t('Statut')}
                            value={getMeetingStatusLabel(meeting.status)}
                        />

                        <InformationRow
                            label={t('Date prévue')}
                            value={formatDate(meeting.scheduled_at)}
                        />

                        <InformationRow
                            label={t('Ouverture')}
                            value={formatDate(meeting.opened_at)}
                        />

                        <InformationRow
                            label={t('Clôture')}
                            value={formatDate(meeting.closed_at)}
                        />

                        <InformationRow
                            label={t('Lieu')}
                            value={meeting.location ?? '—'}
                        />
                    </CardContent>
                </Card>
            </section>
        </>
    );
}
