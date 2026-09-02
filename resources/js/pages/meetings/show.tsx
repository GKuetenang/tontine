import { Head } from '@inertiajs/react';
import {
    CheckCircle2Icon,
    ClipboardListIcon,
    CoinsIcon,
    HandCoinsIcon,
    InfoIcon,
    NotebookPenIcon,
    UsersIcon,
} from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

import { withAppLayout } from '@/layouts/app-layout';

import { MeetingAgenda } from '@/pages/meeting-agendas/show';
import { MeetingAttendances } from '@/pages/meeting-attendances/show';
import { MeetingContributions } from '@/pages/meeting-contributions/show';
import { MeetingDecisions } from '@/pages/meeting-decisions/show';
import { MeetingNotes } from '@/pages/meeting-notes/show';
import { MeetingPayouts } from '@/pages/meeting-payouts/show';
import { MeetingHeader } from '@/pages/meetings/components/meeting-header';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import meetings from '@/routes/groups/sessions/meetings';
import type {
    BreadcrumbItem,
    Meeting,
    MeetingPayoutContext,
    Session,
    Group,
} from '@/types';
import { MeetingOverview } from './components/overview';

type Props = {
    group: Group;
    session: Session;
    meeting: Meeting;
    payoutContext: MeetingPayoutContext;
};

export default withAppLayout<Props>(
    ({ group, session, meeting }) =>
        [
            {
                title: 'Réunions',
                href: groups.index(),
            },
            {
                title: group.name,
                href: groups.show({
                    group: group.slug!,
                }),
            },
            {
                title: 'Sessions',
                href: sessions.index({
                    group: group.slug!,
                }),
            },
            {
                title: session.name,
                href: sessions.show({
                    group: group.slug!,
                    session: session.slug,
                }),
            },
            {
                title: 'Assises',
                href: meetings.index({
                    group: group.slug!,
                    session: session.slug,
                }),
            },
            {
                title: `Assise #${meeting.number}`,
                href: '#',
            },
        ] as BreadcrumbItem[],

    ({ group, session, meeting, payoutContext }: Props) => {
        return (
            <>
                <Head title={meeting.title} />

                <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl">
                    <MeetingHeader
                        meeting={meeting}
                        session={session}
                        group={group}
                    />

                    <Tabs defaultValue="overview" className="w-full">
                        <TabsList className="h-auto w-full justify-start gap-2 overflow-x-auto">
                            <TabsTrigger
                                value="overview"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <InfoIcon className="size-4" />
                                Aperçu
                            </TabsTrigger>

                            <TabsTrigger
                                value="agenda"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <ClipboardListIcon className="size-4" />
                                Ordre du jour
                            </TabsTrigger>

                            <TabsTrigger
                                value="attendances"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <UsersIcon className="size-4" />
                                Présences
                            </TabsTrigger>

                            <TabsTrigger
                                value="contributions"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <CoinsIcon className="size-4" />
                                Cotisations
                            </TabsTrigger>

                            <TabsTrigger value="payouts">
                                <HandCoinsIcon className="size-4" />
                                Versements
                            </TabsTrigger>

                            <TabsTrigger
                                value="notes"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <NotebookPenIcon className="size-4" />
                                Notes
                            </TabsTrigger>

                            <TabsTrigger
                                value="decisions"
                                className="hover:bg-sidebar-accent data-[state=active]:bg-sidebar-accent data-[state=active]:shadow-none!"
                            >
                                <CheckCircle2Icon className="size-4" />
                                Décisions
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent
                            value="overview"
                            className="mt-6 space-y-6"
                        >
                            <MeetingOverview meeting={meeting} />
                        </TabsContent>

                        <TabsContent value="agenda" className="mt-6">
                            <MeetingAgenda
                                group={group}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent value="attendances" className="mt-6">
                            <MeetingAttendances
                                group={group}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent value="contributions" className="mt-6">
                            <MeetingContributions
                                group={group}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent value="payouts" className="mt-6">
                            <MeetingPayouts
                                group={group}
                                session={session}
                                meeting={meeting}
                                context={payoutContext}
                            />
                        </TabsContent>

                        <TabsContent value="notes" className="mt-6">
                            <MeetingNotes
                                group={group}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>

                        <TabsContent value="decisions" className="mt-6">
                            <MeetingDecisions
                                group={group}
                                session={session}
                                meeting={meeting}
                            />
                        </TabsContent>
                    </Tabs>
                </div>
            </>
        );
    },
);
