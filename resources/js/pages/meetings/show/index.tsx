
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';

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

import { MeetingHeader } from '@/pages/meetings/show/meeting-header';



import { MeetingAgenda } from '@/pages/meeting-agendas/show';
import { MeetingAttendances } from '@/pages/meeting-attendances/show';
import { MeetingContributions } from '@/pages/meeting-contributions/show';
import { MeetingDecisions } from '@/pages/meeting-decisions/show';
import { MeetingNotes } from '@/pages/meeting-notes/show';
import {
    CheckCircle2Icon,
    ClipboardListIcon,
    CoinsIcon,
    InfoIcon,
    NotebookPenIcon,
    UsersIcon
} from 'lucide-react';
import { MeetingOverview } from './overview';

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
                        session={session}
                        tontine={tontine}
                    />

                    <Tabs
                        defaultValue="overview"
                        className="w-full"
                    >
                        <TabsList className="h-auto w-full justify-start gap-2 overflow-x-auto ">
                            <TabsTrigger
                                value="overview"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <InfoIcon className="size-4" />
                                Aperçu
                            </TabsTrigger>

                            <TabsTrigger
                                value="agenda"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <ClipboardListIcon className="size-4" />
                                Ordre du jour
                            </TabsTrigger>

                            <TabsTrigger
                                value="attendances"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <UsersIcon className="size-4" />
                                Présences
                            </TabsTrigger>

                            <TabsTrigger
                                value="contributions"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <CoinsIcon className="size-4" />
                                Cotisations
                            </TabsTrigger>

                            <TabsTrigger
                                value="notes"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <NotebookPenIcon className="size-4" />
                                Notes
                            </TabsTrigger>

                            <TabsTrigger
                                value="decisions"
                                className="data-[state=active]:shadow-none! data-[state=active]:bg-sidebar-accent hover:bg-sidebar-accent"
                            >
                                <CheckCircle2Icon className="size-4" />
                                Décisions
                            </TabsTrigger>
                            <TabsTrigger value="payout">
                                Versement
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent
                            value="overview"
                            className="mt-6 space-y-6"
                        >
                            <MeetingOverview
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent
                            value="agenda"
                            className="mt-6"
                        >
                            <MeetingAgenda
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent
                            value="attendances"
                            className="mt-6"
                        >
                            <MeetingAttendances
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent
                            value="contributions"
                            className="mt-6"
                        >
                            <MeetingContributions
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent
                            value="notes"
                            className="mt-6"
                        >
                            <MeetingNotes
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent
                            value="decisions"
                            className="mt-6"
                        >
                            <MeetingDecisions
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>
                        <TabsContent value="payout">
                            {/* <MeetingPayouts
                                tontine={tontine}
                                session={session}
                                meeting={meeting}
                            /> */}
                        </TabsContent>
                    </Tabs>
                </div>
            </>
        );
    },
);