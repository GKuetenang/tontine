import type { InertiaLinkProps } from '@inertiajs/react';
import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import {
    CalendarDaysIcon,
    ChartNoAxesColumnIcon,
    CircleDollarSignIcon,
    HandHeartIcon,
    LandmarkIcon,
    LayoutDashboardIcon,
    PiggyBankIcon,
    ReceiptTextIcon,
    ShieldCheckIcon,
    ShuffleIcon,
    UserRoundCogIcon,
    UsersIcon,
} from 'lucide-react';
import { twMerge } from 'tailwind-merge';
import groups from '@/routes/groups';
import sessions from '@/routes/groups/sessions';
import type { Meeting, NavItem, Session, Group } from '@/types';

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
        },
        {
            title: 'Membres',
            href: groups.memberships.index(group.slug!),
            icon: UsersIcon,
        },
        {
            title: 'Sessions',
            href: groups.sessions.index(group.slug!),
            icon: CalendarDaysIcon,
        },
        {
            title: 'Règles de pénalité',
            href: groups.penaltyRules.index({ group: group.slug! }),
            icon: ShieldCheckIcon,
        },
        {
            title: 'Rôles et permissions',
            href: groups.roles.index({ group: group.slug! }),
            icon: UserRoundCogIcon,
        },
        {
            title: 'Finances',
            href: groups.finances.index({ group: group.slug! }),
            icon: CircleDollarSignIcon,
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
        },
        {
            title: 'Participants',
            href: groups.sessions.participants.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: UsersIcon,
        },
        {
            title: 'Assises',
            href: sessions.meetings.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: CalendarDaysIcon,
            activeWithParentUrl: true,
        },
        {
            title: 'Tirage',
            href: sessions.draw.show({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ShuffleIcon,
        },
        {
            title: 'Dons',
            href: sessions.donations.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: HandHeartIcon,
        },
        {
            title: 'Transactions',
            href: sessions.transactions.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ChartNoAxesColumnIcon,
        },
        {
            title: 'Prêts',
            href: sessions.loans.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: LandmarkIcon,
        },
        {
            title: 'Remboursements',
            href: sessions.repayments.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: ReceiptTextIcon,
        },
        {
            title: 'Assurance',
            href: sessions.insurance.index({
                group: group.slug!,
                session: session.slug,
            }),
            icon: PiggyBankIcon,
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
