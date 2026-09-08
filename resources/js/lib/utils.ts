import type { InertiaLinkProps } from '@inertiajs/react';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import {
    BriefcaseBusinessIcon,
    CalendarDaysIcon,
    ChartNoAxesColumnIcon,
    CircleDollarSignIcon,
    HandHeartIcon,
    LandmarkIcon,
    LayoutDashboardIcon,
    PiggyBankIcon,
    ReceiptTextIcon,
    ShieldCheckIcon,
    ShieldAlertIcon,
    ShuffleIcon,
    UserRoundCogIcon,
    UsersIcon,
} from 'lucide-react';
import { twMerge } from 'tailwind-merge';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type { Group, Meeting, NavItem, Session } from '@/types';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function formatCurrency(
    amount?: number | string | null,
    currency = 'XAF',
    locale = 'fr-FR',
): string {
    if (amount == null) {
        return '—';
    }

    const value = Number(amount);

    if (!Number.isFinite(value)) {
        return '—';
    }

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}

export function getGroupNavItems(group: Group): NavItem[] {
    return [
        {
            title: 'Vue d’ensemble',
            href: groups.show(group.slug!),
            icon: LayoutDashboardIcon,
            permission: 'groups.view',
        },
        {
            title: 'Mandats',
            href: groups.mandates.index({ group: group.slug! }),
            icon: BriefcaseBusinessIcon,
            activeWithParentUrl: true,
            permission: 'mandates.view',
        },
        {
            title: 'Membres',
            href: groups.memberships.index(group.slug!),
            icon: UsersIcon,
            permission: 'memberships.view',
        },
        {
            title: 'Sessions',
            href: groups.sessions.index(group.slug!),
            icon: CalendarDaysIcon,
            permission: 'sessions.view',
        },
        {
            title: 'Règles de pénalité',
            href: groups.penaltyRules.index({ group: group.slug! }),
            icon: ShieldCheckIcon,
            permission: 'penalties.view',
        },
        {
            title: 'Rôles et permissions',
            href: groups.roles.index({ group: group.slug! }),
            icon: UserRoundCogIcon,
            permission: 'roles.view',
        },
        {
            title: 'Finances',
            href: groups.finances.index({ group: group.slug! }),
            icon: CircleDollarSignIcon,
            permission: 'accounting.view',
        },
        // {
        //     title: 'Rapports',
        //     href: '#',
        //     icon: ChartNoAxesColumnIcon,
        // },
        // {
        //     title: 'Paramètres',
        //     href: '#',
        //     icon: SettingsIcon,
        // },
    ];
}

export function getSessionNavItems(group: Group, session: Session): NavItem[] {
    return [
        {
            title: 'Vue d’ensemble',
            href: sessions.show({
                group: group.slug!,
                session: session.slug,
            }),
            icon: LayoutDashboardIcon,
            permission: 'sessions.view',
        },
        {
            title: 'Participants',
            href: groups.sessions.participants.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: UsersIcon,
            permission: 'session-participants.view',
        },
        {
            title: 'Assises',
            href: sessions.meetings.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: CalendarDaysIcon,
            activeWithParentUrl: true,
            permission: 'meetings.view',
        },
        {
            title: 'Tirage',
            href: sessions.draw.show({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ShuffleIcon,
            permission: 'draws.view',
        },
        {
            title: 'Dons',
            href: sessions.donations.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: HandHeartIcon,
            permission: 'donations.view',
        },
        {
            title: 'Transactions',
            href: sessions.transactions.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ChartNoAxesColumnIcon,
            permission: 'accounting.view',
        },
        {
            title: 'Prêts',
            href: sessions.loans.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: LandmarkIcon,
            permission: 'loans.view',
        },
        {
            title: 'Remboursements',
            href: sessions.repayments.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ReceiptTextIcon,
            permission: 'repayments.view',
        },
        {
            title: 'Pénalités',
            href: sessions.penalties.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ShieldAlertIcon,
            permission: 'penalties.view',
        },
        {
            title: 'Assurance',
            href: sessions.insurance.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: PiggyBankIcon,
            permission: 'insurance.view',
        },
    ];
}

export function getMeetingStatusLabel(status: Meeting['status']): string {
    switch (status) {
        case 'scheduled':
            return 'Prévue';

        case 'in_progress':
            return 'En cours';

        case 'completed':
            return 'Terminée';

        case 'cancelled':
            return 'Annulée';

        default:
            return '—';
    }
}
